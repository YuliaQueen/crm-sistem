<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\domains\Project]].
 *
 * @see \common\models\domains\Project
 */
class IssueQuery extends ActiveQuery
{

    /**
     * Добавляет условие на выборку неудаленных задач.
     * @return IssueQuery
     */
    public function notDeleted(): IssueQuery
    {
        return $this->andWhere(['issue.deleted_at' => 0]);
    }

    public function whereProjectId($projectId): IssueQuery
    {
        return $this->andWhere(['project_id' => $projectId]);
    }

    public function getByWorklogStartDate($date): IssueQuery
    {
        return $this->innerJoin('worklog', 'worklog.issue_id = issue.id')
            ->andWhere(['=', 'worklog.deleted_at', 0])
            ->andWhere(['>=', 'worklog.date', $date]);
    }
}
