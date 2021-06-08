<?php

namespace common\models\queries;

use common\models\domains\GoogleSheets;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the ActiveQuery class for [[\common\models\domains\GoogleSheets]].
 *
 * @see \common\models\domains\GoogleSheets
 */
class GoogleSheetsQuery extends ActiveQuery
{
    /**
     * Поиск задачи автозагрузки по id
     * @param $id
     * @return GoogleSheetsQuery
     */
    public function whereId($id): GoogleSheetsQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Добавляет условие на выборку неудаленных задач автозагрузки.
     * @return GoogleSheetsQuery
     */
    public function notDeleted(): GoogleSheetsQuery
    {
        return $this->andWhere(['deleted_at' => 0]);
    }

    /**
     * {@inheritdoc}
     * @return GoogleSheets[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return array|ActiveRecord|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
