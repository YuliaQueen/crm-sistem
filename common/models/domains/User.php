<?php

namespace common\models\domains;

use common\models\queries\UserQuery;
use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int|null $type
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password_hash
 * @property string|null $auth_key
 * @property int|null $department_id
 * @property string|null $jira_user
 * @property string|null $slack_email
 * @property int|null $employee
 * @property int|null $weekly_load
 * @property int|null $leadership_id
 * @property string|null $date_of_employment
 * @property string|null $date_of_dismissal
 * @property int $deleted_at
 * @property int|null $created_by_id
 * @property int|null $updated_by_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $lead
 * @property User[] $team
 * @property User $createdBy
 * @property User $updatedBy
 * @property AuthAssignment[] $authAssignments
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @var int идентификатор пользователя "Система".
     */
    public const SYSTEM_ID = 1;

    /**
     * Норма ежедневных рабочих часов для штатного сотрудника
     */
    public const DAILY_RATE = 8;

    /**
     * Норма еженедельных рабочих часов для штатного сотрудника
     */
    public const WEEKLY_RATE = 40;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user';
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
            [['leadership_id'], 'default', 'value' => null],
            [['email', 'name', 'slack_email', 'jira_user', 'employee', 'department_id', 'date_of_employment'], 'required'],
            [['department_id', 'leadership_id', 'employee'], 'integer'],
            [['weekly_load'], 'integer', 'min' => 1],
            [['date_of_dismissal'], 'safe'],
            [['name', 'email', 'jira_user', 'slack_email'], 'string', 'max' => 255],
            [['email', 'slack_email'], 'filter', 'filter' => 'strtolower'],
            [['email', 'slack_email'], 'email'],
            [
                ['email'],
                'unique',
                'targetAttribute' => ['email', 'deleted_at'],
                'message' => 'Email используется другим пользователем.',
            ],
            [
                ['leadership_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['leadership_id' => 'id'],
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
            'type' => 'Тип пользователя',
            'name' => 'ФИО',
            'email' => 'Email',
            'password_hash' => 'Пароль',
            'department_id' => 'Направление',
            'jira_user' => 'Логин в Jira',
            'slack_email' => 'Email в Slack',
            'employee' => 'Вид сотрудничества',
            'weekly_load' => 'Нагрузка',
            'leadership_id' => 'ФИО руководителя',
            'date_of_employment' => 'Устроен на работу',
            'date_of_dismissal' => 'Дата увольнения',
            'deleted_at' => 'Deleted At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'created_at' => 'Создан',
            'updated_at' => 'Изменен',
        ];
    }

    /**
     * Gets query for [[Leadership]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getLead()
    {
        return $this->hasOne(User::class, ['id' => 'leadership_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getTeam()
    {
        return $this->hasMany(User::class, ['leadership_id' => 'id']);
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
     * Gets query for [[AuthAssignments]].
     *
     * @return ActiveQuery
     */
    public function getAuthAssignments(): ActiveQuery
    {
        return $this->hasMany(AuthAssignment::class, ['user_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find(): UserQuery
    {
        return new UserQuery(static::class);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws Exception
     */
    public function setPassword(string $password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * @param int|string $id
     * @return IdentityInterface|null
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @param mixed $token
     * @param mixed|null $type
     * @return IdentityInterface|null
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @param $email
     *
     * @return array|User|ActiveRecord
     */
    public static function findByEmail($email)
    {
        return static::find()->whereEmail($email)->notDeleted()->one();
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}
