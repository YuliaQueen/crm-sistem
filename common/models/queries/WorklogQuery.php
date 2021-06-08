<?php

namespace common\models\queries;

use common\models\domains\Issue;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\domains\Project]].
 *
 * @see \common\models\domains\Project
 */
class WorklogQuery extends ActiveQuery
{

    /**
     * Поиск ворклога по id
     * @param $id
     * @return WorklogQuery
     */
    public function whereId($id): WorklogQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Добавляет условие на выборку неудаленных дат.
     * @return WorklogQuery
     */
    public function notDeleted(): WorklogQuery
    {
        return $this->andWhere(['deleted_at' => 0]);
    }

    /**
     * @param $id
     * @return WorklogQuery
     */
    public function whereIssueId($id): WorklogQuery
    {
        return $this->andWhere(['issue_id' => $id]);
    }

    /**
     * Добавляет условие на выборку ворклогов в промежутке между двумя датами
     * @param $dateStart
     * @param $dateEnd
     * @return WorklogQuery
     */
    public function getBetweenDate($dateStart, $dateEnd): WorklogQuery
    {
        return $this->andWhere(['and', ['>=', 'date', $dateStart], ['<', 'date', $dateEnd]]);
    }

    /**
     * Добавляет условие на выборку ворклогов для определенного проекта
     * @param $projectId
     * @return WorklogQuery
     */
    public function whereProjectId($projectId): WorklogQuery
    {
        $issuesSubQuery = Issue::find()->select('id')->andWhere(['project_id' => $projectId]);

        return $this->andWhere(['issue_id' => $issuesSubQuery]);
    }

    public function whereDateStart($date): WorklogQuery
    {
        return $this->andWhere(['>=', 'date', $date]);
    }
}
