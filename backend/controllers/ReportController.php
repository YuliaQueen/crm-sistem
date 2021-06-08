<?php

namespace backend\controllers;

use backend\models\forms\PerformanceForm;
use Yii;
use yii\web\Controller;

class ReportController extends Controller
{
    public function actionIndex()
    {
        $model = new PerformanceForm();
        $worklogsData = '';
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $project = $model->project;
            $startDate = $model->startDate;
            $endDate = $model->endDate;
            $worklogsData = $model->getWorklogsData($project, $startDate, $endDate);
        }

        return $this->render('index', compact('model', 'worklogsData'));
    }
}
