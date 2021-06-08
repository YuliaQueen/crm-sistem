<?php


namespace backend\models\forms;

use common\models\domains\User;

/**
 * @property string|null $password
 * @property string|null $passwordRepeat
 */
class UserForm extends User
{
    /** @var string Сценарий создания пользователя. */
    public const SCENARIO_CREATE = 'create';

    /** @var string Сценарий редактирования пользователя. */
    public const SCENARIO_UPDATE = 'update';

    /** @var string Сценарий изменения пароля в профиле пользователя. */
    public const SCENARIO_CHANGE_PASS = 'change_pass';

    /** @var string пароль */
    public $password;

    /** @var string повтор пароля */
    public $passwordRepeat;

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['password', 'passwordRepeat'], 'required'],
            [['password'], 'string', 'min' => 6],
            [['passwordRepeat'], 'string'],
            [
                'passwordRepeat', 'compare', 'compareAttribute' => 'password',
                'message' => "Пароли не совпадают", 'skipOnEmpty' => false,
                'when' => function ($model) {
                    return $model->password !== null && $model->password !== '';
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'name',
            'email',
            'department_id',
            'jira_user',
            'slack_email',
            'employee',
            'weekly_load',
            'leadership_id',
            'date_of_employment',
            'date_of_dismissal',
            '!deleted_at',
            '!type',
            'password',
            'passwordRepeat',
        ];
        $scenarios[self::SCENARIO_UPDATE] = [
            'name',
            'email',
            'department_id',
            'jira_user',
            'slack_email',
            'employee',
            'weekly_load',
            'leadership_id',
            'date_of_employment',
            'date_of_dismissal',
            '!deleted_at',
            '!type',
        ];
        $scenarios[self::SCENARIO_CHANGE_PASS] = [
            'password',
            'passwordRepeat',
        ];
        return $scenarios;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), ['password' => 'пароль', 'passwordRepeat' => 'повтор пароля']);
    }
}
