<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii2tech\crontab\CronTab;

class CronController extends Controller
{
    public function actionInit()
    {
        $appPath = Yii::getAlias('@app');

        $pathToYii = substr($appPath, 0, strrpos($appPath, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'yii ';

        /**
         * Ежедневная проверка залогированного времени
         * Запуск ежедневно в 9:30 утра
         */
        $dailyCheck = $pathToYii . 'work-log/daily-check';

        /**
         * Еженедельная проверка залогированного времени
         * Запуск каждый понедельник в 9:30 утра
         */
        $weeklyCheck = $pathToYii . 'work-log/weekly-check';

        /**
         * Автоподгрузка задач и ворклогов проекта
         * Запуск ежедневно в 9:45 утра
         */
        $projectAutoload = $pathToYii . 'project/autoload-projects';

        /**
         * Обновление Google Sheets
         * Запуск ежедневно в 10 утра
         */
        $googleSheets = $pathToYii . 'google/update-sheet';

        $cronTab = new CronTab();

        $cronTab->setJobs([
            [
                'min' => '30',
                'hour' => '9',
                'command' => 'php' . ' ' . $dailyCheck,
            ],
            [
                'line' => '30 9 * * 1 php' . ' ' . $weeklyCheck,
            ],
            [
                'line' => '45 9 * * * php' . ' ' . $projectAutoload,
            ],
            [
                'line' => '0 10 * * * php' . ' ' . $googleSheets,
            ],
        ]);
        $cronTab->apply();
    }

    public function actionRemoveAll()
    {
        $cronTab = new CronTab();
        $cronTab->removeAll();
    }
}
