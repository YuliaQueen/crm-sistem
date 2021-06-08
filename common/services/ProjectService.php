<?php

namespace common\services;

use common\models\domains\Issue;
use common\models\domains\Project;
use common\models\domains\Worklog;
use JiraRestApi\JiraException;
use JsonMapper_Exception;
use Throwable;
use Yii;
use yii\base\Exception;

class ProjectService
{
    /**
     * @return bool
     * @throws JiraException
     * @throws JsonMapper_Exception|Exception|Throwable
     */
    public function autoloadProjects(): bool
    {
        try {
            $autoloadProjects = Project::find()->notDeleted()->whereIsAutoload()->all();
            $jiraService = new JiraService();
            $autoloadDataArray = [];
            $oldIssues = [];
            $oldIssuesIds = [];
            $oldWorklogs = [];

            if ($autoloadProjects === null) {
                return false;
            }

            /** @var Project[] $autoloadProjects */
            foreach ($autoloadProjects as $project) {
                $startDate = date('Y-m-d', strtotime("today - {$project->reload_days} days"));

                /** @var Issue[] $dbIssues */
                $dbIssues = Issue::find()
                    ->getByWorklogStartDate($startDate)
                    ->whereProjectId($project->id)
                    ->indexBy('issue_key')
                    ->notDeleted()
                    ->all();

                foreach ($dbIssues as $issue) {
                    $oldIssues[$issue->issue_key] = $issue;
                    $oldIssuesIds[] = $issue->id;
                }

                /** @var Worklog[] $dbWorklogs */
                $dbWorklogs = Worklog::find()->whereIssueId($oldIssuesIds)->whereDateStart($startDate)->notDeleted()->all();
                foreach ($dbWorklogs as $dbWorklog) {
                    $oldWorklogs[$dbWorklog->worklog_id] = $dbWorklog;
                }

                $autoloadDataArray[$project->id] = $jiraService->getJiraIssuesAndWorklogs($project->project_key, $startDate);
            }

            if (!empty($autoloadDataArray)) {
                foreach ($autoloadDataArray as $projectId => $jiraProjectData) {
                    [$jiraIssuesArray, $jiraWorklogsArray] = $jiraProjectData;

                    $updatedIssue = [];
                    $issuesTree = [];
                    $idsArray = [];
                    // сохранение задач
                    if (!empty($jiraIssuesArray)) {
                        $issuesTree = Issue::getInTreeFormat($jiraIssuesArray);

                        /** @var Issue[] $idsArray */
                        [$idsArray, $issuesToDelete] = Issue::saveIssuesTree($issuesTree, $projectId, $oldIssues);
                        foreach ($issuesToDelete as $issueToDelete) {
                            $issueToDelete->delete();
                        }

                        // сохранение ворклогов
                        $deletedWorklogs = Worklog::saveWorklogs($idsArray, $jiraWorklogsArray, $oldWorklogs);

                        /** @var Worklog $deletedWorklog */
                        foreach ($deletedWorklogs as $deletedWorklog) {
                            $deletedWorklog->delete();
                        }
                        foreach ($autoloadProjects as $project) {
                            $project->last_load_date = time();
                            $project->update(false, ['last_load_date']);
                        }
                    }
                }
            }
            return true;
        } catch (Exception $exception) {
            Yii::error($exception, 'project');
            return false;
        }
    }
}
