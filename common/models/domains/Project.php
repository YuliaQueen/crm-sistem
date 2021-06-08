<?php

namespace common\models\domains;

use common\models\queries\ProjectQuery;
use common\services\JiraService;
use common\models\domains\Issue;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $project_key
 * @property bool|null $autoload
 * @property string|null $load_start_date
 * @property int|null $reload_days
 * @property string|null $last_load_date
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_by_id
 * @property int $updated_by_id
 *
 * @property Issue[] $issues
 * @property User $createdBy
 * @property User $updatedBy
 */
class Project extends ActiveRecord
{
    /** @var string Сценарий создания нового проекта. */
    public const SCENARIO_CREATE_PROJECT = 'create_project';

    /** @var string Сценарий обновления проекта. */
    public const SCENARIO_RELOAD_PROJECT = 'reload_project';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'project';
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
            [['deleted_at'], 'default', 'value' => 0],
            [['project_key', 'load_start_date'], 'required'],
            [['autoload'], 'boolean'],
            [['last_load_date',], 'safe'],
            [
                ['reload_days'],
                'default',
                'value' => null,
            ],
            [['reload_days',], 'integer'],
            [['project_key'], 'string', 'max' => 255],
            [
                ['project_key', 'deleted_at'],
                'unique',
                'targetAttribute' => ['project_key', 'deleted_at'],
                'on' => self::SCENARIO_CREATE_PROJECT,
                'message' => 'Этот проект уже синхронизируется',
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
            'project_key' => 'Ключ проекта',
            'autoload' => 'Автозагрузка',
            'load_start_date' => 'Дата начальной загрузки',
            'reload_days' => 'дней',
            'last_load_date' => 'Last Load Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE_PROJECT] = [
            'project_key',
            'load_start_date',
            'autoload',
            'reload_days',
            '!deleted_at',
        ];
        $scenarios[self::SCENARIO_RELOAD_PROJECT] = [
            'load_start_date',
            '!deleted_at',
        ];

        return $scenarios;
    }

    /**
     * Gets query for [[Issues]].
     *
     * @return ActiveQuery
     */
    public function getIssues()
    {
        return $this->hasMany(Issue::class, ['project_id' => 'id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_id']);
    }

    /**
     * {@inheritdoc}
     * @return ProjectQuery the active query used by this AR class.
     */
    public static function find(): ProjectQuery
    {
        return new ProjectQuery(static::class);
    }

    /**
     * Сохраняет задачи и ворклоги проекта
     * @param Project $project
     * @param $projectStartDate
     * @param $projectId
     * @return bool
     * @throws Throwable
     */
    public function saveIssuesAndWorklogsData(Project $project, $projectStartDate, $projectId): bool
    {
        try {
            $jiraService = new JiraService();
            [$issuesArray, $worklogsArray] = ($jiraService->getJiraIssuesAndWorklogs(
                $project->project_key,
                $projectStartDate
            ));
            /** @var Issue[] $dbIssues */
            $dbIssues = Issue::find()
                ->whereProjectId($project->id)
                ->getByWorklogStartDate($projectStartDate)
                ->notDeleted()
                ->indexBy('issue_key')
                ->all();

            $oldIssues = [];
            $oldIssuesIds = [];
            foreach ($dbIssues as $issue) {
                $oldIssues[$issue->issue_key] = $issue;
                $oldIssuesIds[] = $issue->id;
            }
            $oldWorklogs = [];
            /** @var Worklog[] $dbWorklogs */
            $dbWorklogs = Worklog::find()->whereIssueId($oldIssuesIds)->whereDateStart($projectStartDate)->notDeleted(
            )->all();
            foreach ($dbWorklogs as $dbWorklog) {
                $oldWorklogs[$dbWorklog->worklog_id] = $dbWorklog;
            }

            $issuesTree = [];
            $idsArray = [];
            // сохранение задач
            if (!empty($issuesArray)) {
                $issuesTree = Issue::getInTreeFormat($issuesArray);

                /** @var int[] $idsArray */
                /** @var Issue[] $issuesToDelete */
                [$idsArray, $issuesToDelete] = Issue::saveIssuesTree($issuesTree, $projectId, $oldIssues);

                foreach ($issuesToDelete as $issueToDelete) {
                    $issueToDelete->delete();
                }
            }

            // возвращаем удаленные в jira ворклоги, если такие есть
            $deletedWorklogs = Worklog::saveWorklogs($idsArray, $worklogsArray, $oldWorklogs);
            /** @var Worklog $deletedWorklog */
            foreach ($deletedWorklogs as $deletedWorklog) {
                $deletedWorklog->delete();
            }
            return true;
        } catch (\Exception $exception) {
            Yii::error($exception, 'project');
            return false;
        }
    }

    /**
     * Удаляет все задачи и ворклоги, связанные с проектом
     * @param Project $project
     * @return bool
     * @throws Throwable
     */
    public function deleteIssuesAndWorklogsByProject(Project $project): bool
    {
        try {
            /** @var Issue[] $issues */
            $issues = $project->issues;

            $issuesIds = [];
            foreach ($issues as $issue) {
                $issuesIds[] = $issue->id;
                $issue->delete();
            }

            $worklogs = Worklog::find()->notDeleted()->whereIssueId($issuesIds)->all();

            foreach ($worklogs as $worklog) {
                $worklog->delete();
            }

            return true;
        } catch (Exception $exception) {
            Yii::error($exception, 'project');
            return false;
        }
    }
}
