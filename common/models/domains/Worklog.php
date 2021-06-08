<?php

namespace common\models\domains;

use common\models\queries\WorklogQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "worklog".
 *
 * @property int $id
 * @property string|null $worklog_id
 * @property int|null $issue_id
 * @property string|null $date
 * @property int|null $author_id
 * @property int|null $timespent
 * @property string|null $worklog_comment
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_by_id
 * @property int $updated_by_id
 *
 * @property Issue $issue
 * @property User $author
 * @property User $createdBy
 * @property User $updatedBy
 */
class Worklog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'worklog';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class => [
                'class' => TimestampBehavior::class,
            ],
            SoftDeleteBehavior::class => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'deleted_at' => time(),
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
    public function rules()
    {
        return [
            [['issue_id', 'author_id', 'timespent'], 'default', 'value' => null],
            [['issue_id', 'author_id', 'timespent', 'worklog_id'], 'integer'],
            [['date'], 'safe'],
            [['worklog_comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'worklog_id' => 'Worklog ID',
            'issue_id' => 'Issue ID',
            'date' => 'Date',
            'author_id' => 'Author ID',
            'timespent' => 'Timespent',
            'worklog_comment' => 'Worklog Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * Gets query for [[Issue]].
     *
     * @return ActiveQuery
     */
    public function getIssue(): ActiveQuery
    {
        return $this->hasOne(Issue::class, ['id' => 'issue_id']);
    }

    /**
     * Gets query for [[Author]].
     *
     * @return ActiveQuery
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery
     */
    public function getCreatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return ActiveQuery
     */
    public function getUpdatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_id']);
    }

    /**
     * {@inheritdoc}
     * @return WorklogQuery the active query used by this AR class.
     */
    public static function find(): WorklogQuery
    {
        return new WorklogQuery(static::class);
    }

    /**
     * @param array $idsArray - массив id задач
     * @param array $worklogsArray - массив ворклогов из Jira
     * @param array $oldWorklogs
     * @return array
     */
    public static function saveWorklogs(array $idsArray, array $worklogsArray, array $oldWorklogs): array
    {
        if (!empty($idsArray) && !empty($worklogsArray)) {
            $authors = [];
            /** @var User[] $authorModels */
            $authorModels = User::find()->select(['id', 'jira_user'])->notDeleted()->all();
            foreach ($authorModels as $authorModel) {
                $authors[$authorModel->jira_user] = $authorModel->id;
            }
            foreach ($worklogsArray as $key => $items) {
                foreach ($items as $item) {
                    if (isset($oldWorklogs[$item['worklog_id']])) {
                        $worklog = $oldWorklogs[$item['worklog_id']];
                        unset($oldWorklogs[$item['worklog_id']]);
                    } else {
                        $worklog = new Worklog();
                    }
                    $worklog->issue_id = $idsArray[$key];
                    $worklog->author_id = $authors[$item['author']] ?? null;
                    $worklog->date = $item['date'];
                    $worklog->worklog_id = $item['worklog_id'];
                    $worklog->worklog_comment = $item['worklog_comment'];
                    $worklog->timespent = $item['timespent'];
                    if (!$worklog->save()) {
                        Yii::error(['message' => 'Ворклог не сохранен', 'errors' => $worklog->getErrors()], 'project');
                    }
                }
            }
        }
        return $oldWorklogs;
    }
}
