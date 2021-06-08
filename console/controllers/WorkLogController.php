<?php

namespace console\controllers;

use common\services\WorkLogService;
use Exception;
use yii\base\InvalidConfigException;
use yii\console\Controller;

class WorkLogController extends Controller
{
    /**
     * Запуск ежедневной проверки
     * @return bool
     * @throws InvalidConfigException
     */
    public function actionDailyCheck(): bool
    {
        $workLogService = new WorkLogService();
        return $workLogService->runDailyCheck();
    }

    /**
     * Запуск еженедельной проверки
     * @throws Exception
     */
    public function actionWeeklyCheck()
    {
        $workLogService = new WorkLogService();
        $workLogService->runWeeklyCheck();
    }
}
