<?php

namespace backend\models\forms;

use common\models\domains\User;
use common\models\queries\UserQuery;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Login form
 */
class LoginForm extends Model
{
    /** @var string email */
    public $email;
    /** @var string пароль */
    public $password;
    /** @var bool запомнить пользователя */
    public $rememberMe = true;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['email', 'filter', 'filter' => 'strtolower'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            if ($this->getUser()) {
                Yii::$app->session->setFlash('success', 'Вы успешно зашли на сайт');
                return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            }
        }

        return false;
    }

    /**
     * Finds user by [[email]]
     *
     * @return ActiveRecord|array|User
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::find()->whereEmail($this->email)->notDeleted()->whereNotDismissal()->one();
        }

        return $this->_user;
    }
}
