<?php

namespace common\services;

use common\models\domains\Calendar;
use common\models\domains\User;
use common\models\enums\DayType;
use common\models\enums\Employment;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use JsonMapper_Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

class WorkLogService
{
    public const CALENDAR_EVENTS_LOG = 'calendar_events_array';

    /**
     * Ежедневная проверка залогированного времени в Jira
     * @return bool
     * @throws JsonMapper_Exception
     * @throws Exception
     */
    public function runDailyCheck(): bool
    {
        $ifTodayIsMonday = $this->ifTheDayIsMonday('today');
        if (!$ifTodayIsMonday) {
            /** @var string $startOfWeek начало недели */
            $startOfWeek = 'Monday this Week';

            /** @var string $yesterdayDate вчерашняя дата */
            $yesterdayDate = date('Y-m-d', strtotime('yesterday'));

            /** @var string $todayDate текущая дата */
            $todayDate = date('Y-m-d', strtotime('today'));

            /** @var array $currentWeekDaysArr массив дней текущей недели с понедельника по вчерашний день */
            $currentWeekDaysArr = $this->getDateIntervalArray($startOfWeek, $yesterdayDate);

            /** @var int $currentWeeklyRate предполагаемая норма часов за текущую неделю */
            $currentWeeklyRate = count($currentWeekDaysArr) * User::DAILY_RATE;

            /** @var User[] $users */
            $users = User::find()->notDeleted()->notSystem()->isStaff()->whereNotDismissal()->with('lead')->all();

            /** @var Calendar[] $commonEvents */
            $commonEvents = Calendar::find()
                ->notDeleted()
                ->whereIntersectPeriod($currentWeekDaysArr[0], $todayDate)
                ->all();

            [$eventsWithUserId, $eventsWithoutUserId] = $this->getEvents($commonEvents);

            /** @var array $commonEventsDaysArr массив общих переопределений вида date => type или [] */
            $commonEventsDaysArr = $this->getDayTypeArray($eventsWithoutUserId, $currentWeekDaysArr);

            $logData = [];
            // логирование общих событий
            $logData['Общие события'] = $commonEventsDaysArr;

            $jiraService = new JiraService();
            $slackService = new SlackService();

            foreach ($users as $user) {
                /** @var Calendar[] $userEvents */
                $userEvents = $eventsWithUserId[$user->id] ?? [];

                /** @var array $userEventsDayArr массив пользовательских переопределений вида date => type или [] */
                $userEventsDayArr = $this->getDayTypeArray($userEvents, $currentWeekDaysArr);

                // логирование пользовательских событий
                $logData[$user->jira_user] = ['Пользовательские события' => $userEventsDayArr];

                $userEventDays = array_merge($commonEventsDaysArr, $userEventsDayArr);

                // получение нормы часов для пользователя с учетом инд. переопределений
                $currentWeeklyRate = $this->getUserWeeklyLoad($currentWeeklyRate, $userEventDays);

                // логирование индивидуальной недельной нагрузки
                $logData[$user->jira_user] = ['Инд. недельная нагрузка' => $currentWeeklyRate];

                $userTimeZone = $jiraService->getUserTimeZone($user->jira_user);

                $thisMonday = $this->getDateWithTimeZone($currentWeekDaysArr[0], $userTimeZone);
                $yesterday = $this->getDateWithTimeZone('yesterday', $userTimeZone);
                $today = $this->getDateWithTimeZone('today', $userTimeZone);

                /** @var float $yesterdayHours worklogs за вчерашний день */
                $yesterdayHours = $jiraService->getTimeSpent($yesterday, $today, $user->jira_user);

                // логирование вчерашних ворклогов
                $logData[$user->jira_user]['yesterdayHours'] = $yesterdayHours;

                /** @var float $weeklyHours worklogs с начала текущей недели */
                $weeklyHours = $jiraService->getTimeSpent($thisMonday, $today, $user->jira_user);

                // логирование ворклогов за неделю
                $logData[$user->jira_user]['weeklyHours'] = $weeklyHours;

                // сообщение отправится, если сегодня рабочий день по переопределению для пользователя или по календарю
                if ($this->ifTodayIsUserWorkDay($userEvents) || $this->isWorkingDay($today)) {
                    $this->sendDailyMessage($slackService, $weeklyHours, $currentWeeklyRate, $yesterdayHours, $user);
                }
            }
            // сохранение массива логов
            Yii::info(VarDumper::dumpAsString($logData), self::CALENDAR_EVENTS_LOG);
        } else {
            Yii::info('Сегодня понедельник, отправка сообщений не требуется!', SlackService::SLACK_SERVICE_LOG);
        }
        return true;
    }

    /**
     * Еженедельная проверка залогированного времени в Jira
     * @throws Exception
     */
    public function runWeeklyCheck()
    {
        $jiraService = new JiraService();
        $slackService = new SlackService();

        /** @var array $lastWeekDaysArray массив дней прошедшей недели */
        $lastWeekDaysArray = $this->getDateIntervalArray('Monday last Week', 'Sunday last Week');

        /** @var User[] $users */
        $users = User::find()->notDeleted()->notSystem()->whereNotDismissal()->with('lead')->all();
        $startDate = $lastWeekDaysArray[0];
        $endDate = end($lastWeekDaysArray);

        /** @var Calendar[] $commonEvents */
        $commonEvents = Calendar::find()
            ->whereIntersectPeriod($startDate, $endDate)
            ->notDeleted()
            ->all();

        [$eventsWithUserId, $eventsWithoutUserId] = $this->getEvents($commonEvents);

        $logData = [];
        // логирование общих событий
        $logData['Общие события'] = $eventsWithoutUserId;

        /** @var array $commonEventsDaysArr массив общих переопределений вида date => type или [] */
        $commonEventsDaysArr = $this->getDayTypeArray($eventsWithoutUserId, $lastWeekDaysArray);

        foreach ($users as $user) {
            /** @var Calendar[] $userEvents */
            $userEvents = $eventsWithUserId[$user->id] ?? [];

            $userTimeZone = $jiraService->getUserTimeZone($user->jira_user);
            $startDate = $this->getDateWithTimeZone($startDate, $userTimeZone);
            $endDate = $this->getDateWithTimeZone($endDate, $userTimeZone);

            /** @var array $userEventsDayArr массив пользовательских переопределений вида date => type или [] */
            $userEventsDayArr = $this->getDayTypeArray($userEvents, $lastWeekDaysArray);

            // логирование пользовательских событий
            $logData[$user->jira_user] = ['Пользовательские события' => $userEventsDayArr];

            $userEventDays = array_merge($commonEventsDaysArr, $userEventsDayArr);

            $defaultLoad = $user->employee === Employment::STAFF ? User::WEEKLY_RATE : $user->weekly_load;
            $userWeeklyLoad = $this->getUserWeeklyLoad($defaultLoad, $userEventDays);

            // логирование инд. недельной нагрузки
            $logData[$user->jira_user] = ['Инд. недельная нагрузка' => $userWeeklyLoad];

            /** @var float $hours */
            $hours = $jiraService->getTimeSpent($startDate, date('Y-m-d', strtotime("+1 day", strtotime($endDate))), $user->jira_user);

            // логирование часов за неделю
            $logData[$user->jira_user]['залогировано за неделю'] = $hours;
            // отправка сообщения
            $this->sendWeeklyMessage($slackService, $hours, $userWeeklyLoad, $startDate, $endDate, $user);
        }
        // сохранение массива логов
        Yii::info(VarDumper::dumpAsString($logData), self::CALENDAR_EVENTS_LOG);
    }

    /**
     * Возвращает два массива событий - общий и индивидуальный
     *
     * @param Calendar[] $commonEvents
     * @return array
     */
    private function getEvents($commonEvents)
    {
        $eventsWithUserId = [];
        $eventsWithoutUserId = [];
        foreach ($commonEvents as $commonEvent) {
            if ($commonEvent->user_id !== null) {
                $eventsWithUserId[$commonEvent->user_id][] = $commonEvent;
            } else {
                $eventsWithoutUserId[] = $commonEvent;
            }
        }
        return [$eventsWithUserId, $eventsWithoutUserId];
    }

    /**
     * Возвращает недельную нагрузку пользователя в часах с учетом переопределений,
     * если массив переопределений пуст, то возвращается стандартная недельная нагрузка
     *
     * @param int $defaultWeeklyLoad
     * @param array $userEventDays
     * @return int|null
     * @throws \yii\base\Exception
     */
    private function getUserWeeklyLoad(int $defaultWeeklyLoad, array $userEventDays = []): ?int
    {
        $userWeeklyLoad = $defaultWeeklyLoad;

        foreach ($userEventDays as $date => $type) {
            if (DayType::getDayType($type) === DayType::WEEKDAY && !$this->isWorkingDay($date)) { //
                $userWeeklyLoad += User::DAILY_RATE;
            } elseif (DayType::getDayType($type) === DayType::WEEKEND && $this->isWorkingDay($date)) {
                $userWeeklyLoad -= User::DAILY_RATE;
            }
        }

        return $userWeeklyLoad;
    }

    /**
     * Возвращает массив вида date => type
     *
     * @param Calendar[] $eventsObj
     * @param array $daysArr
     * @return array
     * @throws Exception
     */
    private function getDayTypeArray(array $eventsObj, array $daysArr): array
    {
        $daysTypeArray = [];
        // получение массива дат для всех записей календаря,
        /** @var Calendar $event */
        foreach ($eventsObj as $event) {
            $calendarEventDaysArr =
                $this->getDateIntervalArray($event->date_start, $event->date_end);
            foreach ($calendarEventDaysArr as $day) {
                foreach ($daysArr as $date) {
                    if (strtotime($date) === strtotime($day)) {
                        // выборка дат только текущей недели
                        $daysTypeArray[$day] = $event->type;
                    }
                }
            }
        }
        return $daysTypeArray;
    }

    /**
     * Метод проверяет является ли текущая дата рабочей по календарю пользовательских переопределений
     *
     * @param $userEvents
     */
    private function ifTodayIsUserWorkDay($userEvents)
    {
        /** @var Calendar[] $userEvents */
        foreach ($userEvents as $userEvent) {
            if ((strtotime($userEvent->date_start) <= strtotime('today'))
                && (strtotime($userEvent->date_end) >= strtotime('today'))) {
                if (DayType::getDayType($userEvent->type) === DayType::WEEKDAY) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Возвращает массив дат от $startDate до $endDate (включительно).
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws Exception
     */
    private function getDateIntervalArray(string $startDate, string $endDate): array
    {
        $startDate = new DateTime(date('Y-m-d', strtotime($startDate)));
        $endDate = new DateTime(date('Y-m-d', strtotime($endDate . '+1 day')));
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate);
        $arrayDays = [];
        foreach ($period as $date) {
            $arrayDays[] = $date->format('Y-m-d');
        }
        return $arrayDays;
    }

    /**
     * Метод проверяет каким днем по календарю является день. Если это суббота или воскресенье, то возвращается false.
     * N - порядковый номер дня недели, от 1 (понедельник) до 7 (воскресенье),
     *
     * @param $strToTime
     * @return bool
     */
    private function isWorkingDay($strToTime): bool
    {
        if (date('N', strtotime($strToTime)) >= 6) {
            return false;
        }
        return true;
    }

    /**
     * @param string $date
     * @param string $userTimeZone
     * @return string
     *
     * Возвращает дату с учетом таймзоны пользователя
     */
    private function getDateWithTimeZone(string $date, $userTimeZone = JiraService::DEFAULT_TIMEZONE): string
    {
        date_default_timezone_set($userTimeZone);

        return date('Y-m-d', strtotime($date));
    }

    /**
     * Отправка ежедневного сообщения
     *
     * @param $slackService
     * @param $weeklyHours
     * @param $currentWeeklyRate
     * @param $yesterdayHours
     * @param User $user
     */
    private function sendDailyMessage($slackService, $weeklyHours, $currentWeeklyRate, $yesterdayHours, User $user)
    {
        $messageToLead = null;
        $usersLead = null;

        $yesterdayHoursMessage = $this->convertToHoursMins($yesterdayHours);
        $weeklyHoursMessage = $this->convertToHoursMins($weeklyHours);

        /** @var int|float $behindSchedule отставание от графика */
        $behindSchedule = $currentWeeklyRate - $weeklyHours;
        $behindScheduleMessage = $this->convertToHoursMins($behindSchedule);

        if (($weeklyHours < $currentWeeklyRate && $yesterdayHours >= User::DAILY_RATE)
            || ($weeklyHours < $currentWeeklyRate && $yesterdayHours < User::DAILY_RATE)) {
            $messageToUser = "Вчера вы залогировали {$yesterdayHoursMessage}. " .
                "С начала недели залогировано {$weeklyHoursMessage}. " .
                "Вы отстаете от недельного графика на {$behindScheduleMessage}.";

            $usersLead = $user->lead;
            if ($usersLead !== null) {
                $messageToLead = "Сотрудник {$user->name} отстает от недельного графика на {$behindScheduleMessage}. " .
                    "С начала текущей недели он залогировал {$weeklyHoursMessage}.";
            }
        } elseif ($weeklyHours >= $currentWeeklyRate && $yesterdayHours < User::DAILY_RATE) {
            $messageToUser = "Вчера вы залогировали {$yesterdayHoursMessage}. " .
                "С начала недели залогировано {$weeklyHoursMessage}. Вы укладываетесь в график!";
        } elseif ($weeklyHours >= $currentWeeklyRate && $yesterdayHours >= User::DAILY_RATE) {
            $messageToUser = "Хорошая работа! Вчера вы залогировали {$yesterdayHoursMessage}. " .
                "С начала недели залогировано {$weeklyHoursMessage}. Вы укладываетесь в график!";
        }

        $slackService->sendMessage($user->slack_email, $messageToUser);

        if ($messageToLead !== null && $usersLead !== null) {
            $slackService->sendMessage($usersLead->slack_email, $messageToLead);
        }
    }

    /**
     * Отправка еженедельного сообщения
     *
     * @param $slackService
     * @param $hours
     * @param $userWeeklyLoad
     * @param $startDate
     * @param $endDate
     * @param User $user
     * @throws InvalidConfigException
     */
    private function sendWeeklyMessage($slackService, $hours, $userWeeklyLoad, $startDate, $endDate, User $user)
    {
        $messageToLead = null;
        $usersLead = null;
        $hoursMessage = $this->convertToHoursMins($hours);

        if ($hours < $userWeeklyLoad) {
            $behindSchedule = $userWeeklyLoad - $hours;
            $behindScheduleMessage = $this->convertToHoursMins($behindSchedule);
            $startDateMessage = Yii::$app->getFormatter()->asDate($startDate, 'long');
            $endDateMessage = Yii::$app->getFormatter()->asDate($endDate, 'long');

            $messageToUser = 'Пожалуйста, внесите время в Jira. За неделю ' .
                $startDateMessage . ' - ' . $endDateMessage . ' вы внесли ' . $hoursMessage .
                ' из ' . $userWeeklyLoad . ' часов. ' . 'Вы отстаете от графика на ' . $behindScheduleMessage . '!';

            $usersLead = $user->lead;
            if ($usersLead !== null) {
                $messageToLead = "Сотрудник {$user->name} отстает от графика на {$behindScheduleMessage}! " .
                    "За прошлую неделю он залогировал {$hoursMessage}.";
            }
        } else {
            $messageToUser = "Хорошая работа! За прошлую неделю вы залогировали {$hoursMessage}. Вы укладываетесь в график!";
        }
        $slackService->sendMessage($user->slack_email, $messageToUser);

        if ($messageToLead !== null && $usersLead !== null) {
            $slackService->sendMessage($usersLead->slack_email, $messageToLead);
        }
    }

    /**
     * @param $day
     * @return bool
     *
     * Если $day является понедельником, возвращается true
     */
    private function ifTheDayIsMonday($day)
    {
        if ((int)date('N', strtotime($day)) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param $time
     * @return string|void
     *
     * Конвертирует переданное время в часах и долях часа (float) в часы и минуты
     *
     */
    private function convertToHoursMins($time)
    {
        $hours = (floor($time));
        $minutes = ceil(($time - $hours) * 60);

        return $hours . 'h ' . $minutes . 'm';
    }
}
