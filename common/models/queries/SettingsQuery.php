<?php

namespace common\models\queries;

use common\models\domains\Settings;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the ActiveQuery class for [[\common\models\domains\Settings]].
 *
 * @see \common\models\domains\Settings
 */
class SettingsQuery extends ActiveQuery
{
    /**
     * @return SettingsQuery
     */
    public function notDeleted()
    {
        return $this->andWhere(['is_deleted' => false]);
    }

    /**
     * @return SettingsQuery
     */
    public function actual()
    {
        $subQuery = (new Query())->from('settings')
            ->select([
                'max_created_at' => 'MAX(created_at)',
                'name',
            ])
            ->andWhere([
                'is_deleted' => false,
            ])
            ->groupBy(['name']);

        return $this->innerJoin(['m' => $subQuery], 'm.name = ' . Settings::tableName() . '.name'
            . ' AND m.max_created_at = ' . Settings::tableName() . '.created_at');
    }
}
