<?php

namespace backend\models\forms;

use common\services\JiraService;
use yii\base\Model;

/**
 * @property string|null $project
 * @property string|null $startDate
 * @property string|null $endDate
 */
class PerformanceForm extends Model
{
    public $project;
    public $startDate;
    public $endDate;

    public function rules()
    {
        return [
            [['project', 'startDate', 'endDate'], 'required'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'project' => 'Код проекта',
            'startDate' => 'Начальная дата отчета',
            'endDate' => 'Конечная дата отчета',
        ];
    }

    public function getWorklogsData($project, $startDate, $endDate): string
    {
        $jiraService = new JiraService();

        return $jiraService->getWorklogsData($project, $startDate, $endDate);
    }
}
