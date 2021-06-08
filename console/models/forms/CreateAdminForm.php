<?php


namespace console\models\forms;


use common\models\domains\User;

class CreateAdminForm extends User
{
    /** @var string Сценарий создания/редактирования администратора. */
    public const SCENARIO_CREATE_ADMIN = 'createAdmin';

    public function rules(): array
    {
        return [
            [['name', 'password_hash', 'auth_key'], 'string'],
            [['email'], 'email'],
            [['type'], 'integer'],
            [['name', 'email', 'password'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_CREATE_ADMIN] = [
            'name',
            'email',
            'password_hash',
            'auth_key',
            'type',
        ];
        return $scenarios;
    }
}
