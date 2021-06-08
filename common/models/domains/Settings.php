<?php

namespace common\models\domains;

use common\models\queries\UserQuery;
use common\models\queries\SettingsQuery;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $name
 * @property string|null $value
 * @property int $created_at
 * @property int $updated_at
 * @property bool $is_deleted
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Settings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'settings';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            SoftDeleteBehavior::class => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'is_deleted' => true,
                    'updated_at' => time(),
                ],
                'replaceRegularDelete' => true,
            ],
            BlameableBehavior::class => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by_id',
                'updatedByAttribute' => 'updated_by_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'value'], 'required'],
            [['name', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'value' => 'Значение',
            'created_at' => 'Создано',
            'updated_at' => 'Изменено',
            'is_deleted' => 'Is Deleted',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_id']);
    }

    /**
     * {@inheritdoc}
     * @return SettingsQuery the active query used by this AR class.
     */
    public static function find(): SettingsQuery
    {
        return new SettingsQuery(static::class);
    }
}
