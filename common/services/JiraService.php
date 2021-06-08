<?php

namespace common\services;

use common\models\SystemSettings;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\Issue;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\Worklog;
use JiraRestApi\JiraException;
use JiraRestApi\User\UserService;
use JsonMapper_Exception;
use Yii;
use yii\helpers\VarDumper;

class JiraService
{
    public const DEFAULT_TIMEZONE = 'Europe/Moscow';
    public const JIRA_SERVICE_LOG = 'jira_service';

    private object $settings;
    private object $issueService;
    private object $userService;
    /** @var IssueService */
    protected $iss;
    protected $epics = [];
    protected $parents = [];

    public function __construct()
    {
        /** @var SystemSettings $settingsModel */
        $settingsModel = new SystemSettings();
        $jiraUrl = $settingsModel->jiraUrl;
        $jiraLogin = $settingsModel->jiraLogin;
        $jiraPassword = $settingsModel->jiraPassword;

        $this->settings = new ArrayConfiguration(
            [
                'jiraHost' => $jiraUrl,
                'jiraUser' => $jiraLogin,
                'jiraPassword' => $jiraPassword,
            ]);

        $this->issueService = new IssueService($this->settings);
        $this->userService = new UserService($this->settings);
    }

    /**
     * Возвращает залогированное время пользователя Jira
     *
     * @param string $startDate - начальная дата выборки логов, включительно, передаем в формате 'Y-m-d'
     * @param string $endDate - конечная дата выборки логов, не включительно, передаем в формате 'Y-m-d'
     * @param string $userName - логин сотрудника в Jira
     * @return float - возвращает часы и четверти часа
     * @throws JsonMapper_Exception
     */
    public function getTimeSpent(string $startDate, string $endDate, string $userName): float
    {
        $timeSpent = 0;
        $worklogsArr = [];

        try {
            $iss = $this->issueService;

            $jql = "worklogAuthor = $userName AND worklogDate>=$startDate AND worklogDate<$endDate ORDER BY key, updated DESC";
            // логирование запроса к jira
            Yii::info($jql, self::JIRA_SERVICE_LOG);

            $response = $iss->search($jql, 0, 500);
            // логирование ответа от jira
            Yii::info(VarDumper::dumpAsString($response), self::JIRA_SERVICE_LOG);

            foreach ($response->issues as $issue) {
                foreach ($iss->getWorklog($issue->key) as $worklogs) {
                    if (is_array($worklogs)) {
                        foreach ($worklogs as $worklog) {
                            $authorName = $worklog->author['name'];
                            if ($authorName === $userName
                                && $worklog->timeSpentSeconds
                                && $worklog->started
                                && date('Y-m-d', strtotime($worklog->started)) >= $startDate
                                && date('Y-m-d', strtotime($worklog->started)) < $endDate) {
                                $timeSpent += $worklog->timeSpentSeconds;
                                $worklogsArr['worklogs'][] = [$issue->key => $worklog];
                            }
                        }
                    }
                }
            }
        } catch (JiraException $e) {
            Yii::error($e);
        }
        // логирование ворклогов
        Yii::info(VarDumper::dumpAsString($worklogsArr), self::JIRA_SERVICE_LOG);
        return $timeSpent / 60 / 60;
    }

    /**
     * @param $project
     * @param $startDate
     * @param $endDate
     * @return string
     * @throws JiraException
     * @throws JsonMapper_Exception
     */
    public function getWorklogsData($project, $startDate, $endDate): string
    {
        $iss = $this->issueService;

        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        $start = date('Y-m-d', $startTimestamp);
        $end = date('Y-m-d', $endTimestamp);

        $jql = "project = $project AND worklogDate>=$start AND worklogDate<$end ORDER BY key, updated DESC";
        $response = $iss->search($jql, 0, 5000);
        $result = "<pre>";
        $result .=
            "<b>Project Key</b>"
            . "\t" . "<b>Epic Key</b>"
            . "\t" . "<b>Epic Summary</b>"
            . "\t" . "<b>Task Key</b>"
            . "\t" . "<b>Task Summary</b>"
            . "\t" . "<b>Task Type</b>"
            . "\t" . "<b>SubTask Key</b>"
            . "\t" . "<b>SubTask Summary</b>"
            . "\t" . "<b>SubTask Type</b>"
            . "\t" . "<b>Дата</b>"
            . "\t" . "<b>Автор</b>"
            . "\t" . "<b>Затрачено</b>"
            . "\t" . "<b>Описание работы</b>"
            . "\t" . "<b>WorkLogId</b>"
            . "\n";

        /** @var Issue $issue */
        foreach ($response->issues as $issue) {
            $resultBase = '';
            $resultBase .= $project;
            // вывод названия epic'а (если подзадача, то epic берётся от родителя)
            if ($issue->fields->parent !== null) {
                if ($issue->fields->parent->fields->issuetype->name === 'Epic') {
                    [$epicKey, $epicName] = $this->getEpicIdAndSummaryByKey($issue->fields->parent->key);
                } else {
                    [$epicKey, $epicName] = $this->getEpicIdAndSummary($this->getParentByKey($issue->fields->parent->key));
                }
            } else {
                [$epicKey, $epicName] = $this->getEpicIdAndSummary($issue);
            }
            $resultBase .= "\t";
            // формирование столбцов: epic /  задача / подзадача
            $taskKey = $issue->key;
            $taskSummary = $issue->fields->summary;
            $taskType = $issue->fields->issuetype->name;
            if (!empty($issue->fields->parent)) { // если есть родительская задача
                $parent = $issue->fields->parent;
                $resultBase .= $epicKey;
                $resultBase .= "\t";
                $resultBase .= $epicName;
                $resultBase .= "\t";
                $resultBase .= $parent->key === $epicKey ? '' : $parent->key;
                $resultBase .= "\t";
                $resultBase .= $parent->key === $epicKey ? '' : $parent->fields->summary;
                $resultBase .= "\t";
                $resultBase .= $parent->key === $epicKey ? '' : $parent->fields->issuetype->name;
                $resultBase .= "\t";
                $subTaskKey = $issue->key;
                $resultBase .= $subTaskKey;
                $resultBase .= "\t";
                $subTaskSummary = $issue->fields->summary;
                $resultBase .= $subTaskSummary;
                $resultBase .= "\t";
                $subTaskType = $issue->fields->issuetype->name;
                $resultBase .= $subTaskType;
            } else { //если нет родительской задачи
                if ($issue->fields->getIssueType()->name !== 'Epic') { // если это не Epic-задача
                    $resultBase .= $epicKey;
                    $resultBase .= "\t";
                    $resultBase .= $epicName;
                    $resultBase .= "\t";
                    $resultBase .= $taskKey;
                    $resultBase .= "\t";
                    $resultBase .= $taskSummary;
                    $resultBase .= "\t";
                    $resultBase .= $taskType;
                    $resultBase .= "\t";
                    $resultBase .= "\t";
                    $resultBase .= "\t";
                } else { // если это Epic-задача
                    $resultBase .= $issue->key;
                    $resultBase .= "\t";
                    $resultBase .= $issue->fields->summary;
                    $resultBase .= "\t";
                    $resultBase .= '';
                    $resultBase .= "\t";
                    $resultBase .= '';
                    $resultBase .= "\t";
                    $resultBase .= '';
                    $resultBase .= "\t";
                    $resultBase .= "\t";
                    $resultBase .= "\t";
                }
            }
            $resultBase .= "\t";

            foreach ($iss->getWorklog($issue->key) as $worklogs) {
                if (is_array($worklogs)) {
                    /** @var Worklog $worklog */
                    foreach ($worklogs as $worklog) {
                        // получает логи точно попадающие в заданный промежуток
                        $worklogStarted = strtotime($worklog->started);
                        if ($startTimestamp <= $worklogStarted && $worklogStarted < $endTimestamp) {
                            $worklogDate = date('d.m.Y', strtotime($worklog->started));
                            $resultWorklog = $worklogDate;
                            $resultWorklog .= "\t";
                            $worklogAuthor = $worklog->author['displayName'];
                            $resultWorklog .= $worklogAuthor;
                            $resultWorklog .= "\t";
                            $worklogTimespent = $this->format($worklog->timeSpentSeconds / 60 / 60);
                            $resultWorklog .= $worklogTimespent;
                            $resultWorklog .= "\t";
                            $worklogComment = str_replace(["\n", "\r"], ['', ''], $worklog->comment);
                            $resultWorklog .= $worklogComment;
                            $resultWorklog .= "\t";
                            $worklogId = $worklog->id;
                            $resultWorklog .= $worklogId;
                            $resultWorklog .= "\t";
                            $result .= $resultBase . $resultWorklog . "\n";
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $number
     * @return string
     */
    protected function format($number): string
    {
        return number_format($number, 2, ',', '');
    }

    /**
     * @param string $userName - логин пользователя Jira
     * @return string
     * @throws JiraException
     * @throws JsonMapper_Exception Возвращает TimeZone пользователя Jira
     */
    public function getUserTimeZone(string $userName): string
    {
        $usr = $this->userService;
        $user = $usr->get(['username' => $userName]);
        return $user->timeZone ? $user->timeZone : JiraService::DEFAULT_TIMEZONE;
    }

    /**
     * @param string $key
     * @return Issue|mixed
     * @throws JiraException
     * @throws JsonMapper_Exception
     */
    protected function getEpicByKey(string $key)
    {
        $iss = $this->issueService;
        if (empty($this->epics[$key])) {
            $this->epics[$key] = $iss->get($key);
        }
        return $this->epics[$key];
    }

    /**
     * @param string $key
     * @return Issue|mixed
     * @throws JiraException
     * @throws JsonMapper_Exception
     */
    protected function getParentByKey(string $key)
    {
        $iss = $this->issueService;
        if (empty($this->parents[$key])) {
            $this->parents[$key] = $iss->get($key);
        }
        return $this->parents[$key];
    }

    /**
     * @param Issue $issue
     * @return array|string
     * @throws JiraException
     * @throws JsonMapper_Exception
     */
    protected function getEpicIdAndSummary(Issue $issue)
    {
        if (!empty($issue->fields->customFields['customfield_10102'])) {
            $epic = $this->getEpicByKey($issue->fields->customFields['customfield_10102']);

            return [$epic->key, $epic->fields->summary];
        }
        return '';
    }

    /**
     * @param string $key
     * @return array
     * @throws JiraException
     * @throws JsonMapper_Exception
     */
    protected function getEpicIdAndSummaryByKey(string $key): array
    {
        $epic = $this->getEpicByKey($key);
        return [$epic->key, $epic->fields->summary];
    }
}
