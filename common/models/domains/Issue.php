<?php

namespace common\models\domains;

use common\models\queries\IssueQuery;
use Yii;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "issue".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $level
 * @property int $project_id
 * @property string $issue_key
 * @property string $issue_summary
 * @property string|null $issue_type
 * @property int $deleted_at
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Issue $parent
 * @property Issue[] $issues
 * @property Project $project
 * @property User $createdBy
 * @property User $updatedBy
 */
class Issue extends ActiveRecord
{
    /** @var string Сценарий создания нового проекта. */
    public const SCENARIO_CREATE_ISSUE = 'create_issue';

    /** @var string Сценарий обновления проекта. */
    public const SCENARIO_RELOAD_ISSUE = 'reload_issue';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'issue';
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
    public function rules(): array
    {
        return [
            [['parent_id', 'level', 'project_id'],
                'default',
                'value' => null,
            ],
            [['parent_id', 'level', 'project_id'], 'integer'],
            [['issue_summary'], 'string'],
            [['project_id', 'issue_key'], 'required'],
            [['issue_key', 'issue_summary', 'issue_type'], 'string', 'max' => 255],
            [['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Issue::class,
                'targetAttribute' => ['parent_id' => 'id'],
            ],
            [['project_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Project::class,
                'targetAttribute' => ['project_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'level' => 'Level',
            'project_id' => 'Project ID',
            'issue_key' => 'Issue Key',
            'issue_summary' => 'Issue Summary',
            'issue_type' => 'Issue Type',
            'deleted_at' => 'Deleted At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE_ISSUE] = [
            'issue_key',
            '!deleted_at',
        ];
        $scenarios[self::SCENARIO_RELOAD_ISSUE] = [
            '!deleted_at',
        ];

        return $scenarios;
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(Issue::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Issues]].
     *
     * @return ActiveQuery
     */
    public function getIssues(): ActiveQuery
    {
        return $this->hasMany(Issue::class, ['parent_id' => 'id']);
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
     * @return IssueQuery
     */
    public static function find(): IssueQuery
    {
        return new IssueQuery(static::class);
    }

    /**
     * Формирует элементы многоуровневого массива задач в следующем формате:
     * [
     *     [
     *         'parent' => Родительская задача,
     *         'children' => [
     *             [
     *                 'parent' => Родительская задача,
     *                 'children' => [],
     *             ],
     *             ...,
     *         ]
     *     ],
     *     ...,
     * ]
     * В поле 'children' рекурсивно повторяется структура массива.
     * Туда добавляются все задачи у которых parent_key равен ключу массива.
     * Т.е. рекурсивно все дочерние задачи.
     * Если дочерних задач нет, то поле 'children' все равно создается и в него добавляется пустой массив.
     *
     * @param array $items
     * @return array
     */
    public static function getInTreeFormat(array $items): array
    {
        $itemsGroupedByParentId = ArrayHelper::index($items, null, [function ($item) {
            return $item['parent_key'] ?? 0;
        }]);

        return self::generateTree($itemsGroupedByParentId, 0);
    }

    /**
     * @param array $items
     * @param int|string $key
     * @return array
     */
    protected static function generateTree(array $items, $key): array
    {
        $result = [];
        if (isset($items[$key])) {
            /** @var Issue $item */
            foreach ($items[$key] as $item) {
                $result[] = [
                    'parent' => $item,
                    'children' => self::generateTree($items, $item['task_key']),
                ];
            }
        }
        return $result;
    }

    /**
     * @param $tree
     * @param $projectId
     * @param Issue[] $oldIssues
     * @param int|null $id
     * @param array $idsArray
     * @return array
     * @throws Exception
     */
    public static function saveIssuesTree($tree, $projectId, array $oldIssues, int $id = null, array $idsArray = []): array
    {
        foreach ($tree as $item) {
            if (isset($item['parent'])) {
                $issue = $item['parent'];
                if (isset($oldIssues[$issue['task_key']])) {
                    $model = $oldIssues[$issue['task_key']];
                    unset($oldIssues[$issue['task_key']]);
                } else {
                    $model = new Issue();
                }
                $model->issue_key = $issue['task_key'];
                $model->issue_summary = $issue['task_summary'];
                $model->issue_type = $issue['task_type'];
                $model->parent_id = $id;
                $model->level = $issue['level'];
                $model->project_id = $projectId;
                if ($model->save()) {
                    $modelId = $model->id;
                    $idsArray[$model->issue_key] = $modelId;
                } else {
                    Yii::error(['errors' => $model->errors, 'attributes' => $model->attributes], 'project');
                    throw new Exception('Не удалось сохранить задачу');
                }
            }
            if (!empty($item['children'])) {
                [$ids, $oldIssues] = self::saveIssuesTree($item['children'], $projectId, $oldIssues, $modelId, $idsArray);
                $idsArray = array_merge($idsArray, $ids);
            }
        }
        return [$idsArray, $oldIssues];
    }
}
