<?php

namespace common\models\domains;

use common\models\queries\GoogleSheetsQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "google_sheets".
 *
 * @property int $id
 * @property int $project_id
 * @property string $spreadsheet
 * @property string $range
 * @property string $sheet
 * @property int $reload_days
 * @property int|null $last_load_date
 * @property int $deleted_at
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Project $project
 */
class GoogleSheets extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'google_sheets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['project_id', 'spreadsheet', 'range', 'sheet', 'reload_days'], 'required'],
            [['project_id', 'reload_days', 'last_load_date'], 'default', 'value' => null],
            [['project_id', 'reload_days', 'last_load_date'], 'integer'],
            [['spreadsheet', 'range', 'sheet'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project Key',
            'spreadsheet' => 'Spreadsheet ID',
            'sheet' => 'Sheet ID',
            'range' => 'Range',
            'reload_days' => 'Reload Days',
            'last_load_date' => 'Last Load Date',
            'deleted_at' => 'Deleted At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return ActiveQuery
     */
    public function getProject(): ActiveQuery
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    /**
     * {@inheritdoc}
     * @return GoogleSheetsQuery the active query used by this AR class.
     */
    public static function find(): GoogleSheetsQuery
    {
        return new GoogleSheetsQuery(static::class);
    }
}
