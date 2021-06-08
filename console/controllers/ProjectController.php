<?php

namespace console\controllers;

use common\services\ProjectService;
use JiraRestApi\JiraException;
use JsonMapper_Exception;
use yii\base\Exception;
use yii\console\Controller;

class ProjectController extends Controller
{
    /**
     * Запуск автоподгрузки данных проекта из Jira за заданное количество дней
     * @throws JiraException
     * @throws JsonMapper_Exception
     * @throws \Throwable
     * @throws Exception
     */
    public function actionAutoloadProjects()
    {
        $projectService = new ProjectService();
        $projectService->autoloadProjects();
    }
}
