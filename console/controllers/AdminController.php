<?php

namespace console\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\enums\Employment;
use common\models\enums\UserType;
use console\models\forms\CreateAdminForm;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\validators\EmailValidator;
use yii\validators\StringValidator;

class AdminController extends Controller
{
    public function actionCreate()
    {
        $model = new CreateAdminForm();
        $model->scenario = CreateAdminForm::SCENARIO_CREATE_ADMIN;

        $model->name = $this->prompt(
            'Введите имя пользователя: ',
            [
                'required' => true,
                'error' => $this->ansiFormat('Логин не должен быть пустым!', Console::FG_RED),
                'validator' => function ($input, &$error) {
                    $stringValidator = new StringValidator(
                        [
                            'max' => 255,
                            'tooLong' => 'Имя пользователя должно содержать максимум {max} символов.',
                        ]
                    );
                    if (!$stringValidator->validate($input, $error)) {
                        $error = $this->ansiFormat($error, Console::FG_RED);
                        return false;
                    }
                    return true;
                },
            ]
        );

        $model->email = $this->prompt(
            'Введите email: ',
            [
                'required' => true,
                'error' => $this->ansiFormat('Email не должен быть пустым!', Console::FG_RED),
                'validator' => function ($input, &$error) {
                    $emailValidator = new EmailValidator(
                        [
                            'message' => 'Введенное значение не является правильным email адресом.',
                        ]
                    );
                    if (!$emailValidator->validate($input, $error)) {
                        $error = $this->ansiFormat($error, Console::FG_RED);
                        return false;
                    }
                    return true;
                },
            ]
        );

        $password = $this->prompt(
            'Введите пароль: ',
            [
                'required' => true,
                'error' => $this->ansiFormat('Пароль не должен быть пустым!', Console::FG_RED),
                'validator' => function ($input, &$error) {
                    $stringValidator = new StringValidator(
                        [
                            'min' => 6,
                            'max' => 255,
                            'tooShort' => 'Пароль должен содержать минимум {min} символов.',
                            'tooLong' => 'Пароль должен содержать максимум {max} символов.',
                        ]
                    );
                    if (!$stringValidator->validate($input, $error)) {
                        $error = $this->ansiFormat($error, Console::FG_RED);
                        return false;
                    }
                    return true;
                },
            ]
        );
        $model->setPassword($password);

        $model->type = UserType::USER;
        $model->employee = Employment::STAFF;
        $model->auth_key = Yii::$app->security->generateRandomString();

        if ($model->save()) {
            $rbacService = Yii::$app->rbacService;
            $roleAdmin = $rbacService->findRole(Permission::ADMIN);
            $rbacService->assign($roleAdmin, $model->id);
            $this->stdout("Пользователь успешно создан.\n");
            return ExitCode::OK;
        } else {
            $this->stdout("Пользователь не создан.\n");
            Yii::error($model->errors);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
