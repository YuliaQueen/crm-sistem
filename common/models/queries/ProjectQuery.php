<?php

namespace common\models\queries;

use common\models\domains\Worklog;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\domains\Project]].
 *
 * @see \common\models\domains\Project
 */
class ProjectQuery extends ActiveQuery
{

    /**
     * Поиск проекта по id
     * @param $id
     * @return ProjectQuery
     */
    public function whereId($id): ProjectQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Добавляет условие на выборку неудаленных дат.
     * @return ProjectQuery
     */
    public function notDeleted(): ProjectQuery
    {
        return $this->andWhere(['deleted_at' => 0]);
    }

    /**
     * Добавляет условие на выборку проектов с автозагрузкой.
     * @return ProjectQuery
     */
    public function whereIsAutoload(): ProjectQuery
    {
        return $this->andWhere(['autoload' => true]);
    }

    /**
     * Получает первый ворклог проекта
     * @param $projectId
     * @return bool|mixed|string|null
     */
    public function getFirstLog($projectId)
    {
        return Worklog::find()->whereProjectId($projectId)->min('date');
    }

    /**
     * Получает последний ворклог проекта
     * @param $projectId
     * @return bool|mixed|string|null
     */
    public function getLastLog($projectId)
    {
        return Worklog::find()->whereProjectId($projectId)->max('date');
    }
}
