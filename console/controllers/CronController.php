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
         * @var string $dailyCheck
         * Запуск ежедневно в 9:30 утра
         */
        $dailyCheck = $pathToYii . 'work-log/daily-check';

        /**
         * @var string $weeklyCheck
         * Запуск каждый понедельник в 9:30 утра
         */
        $weeklyCheck = $pathToYii . 'work-log/weekly-check';

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
        ]);
        $cronTab->apply();
    }

    public function actionRemoveAll()
    {
        $cronTab = new CronTab();
        $cronTab->removeAll();
    }
}
