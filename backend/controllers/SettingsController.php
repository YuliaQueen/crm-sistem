<?php

namespace backend\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\SystemSettings;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * SettingsController implements the CRUD actions for Settings model.
 */
class SettingsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Permission::SETTINGS],
                    ],
                ],
            ],
        ];
    }

    /**
     * Creates a new Settings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new SystemSettings();
        $jiraPassword = $model->jiraPassword;
        $slackToken = $model->slackToken;
        $passReplace = '';
        $tokenReplace = '';

        if (!empty($jiraPassword)) {
            $passReplace = str_repeat('*', strlen($jiraPassword));
        }

        if (!empty($slackToken)) {
            $tokenReplace = str_repeat('*', strlen($slackToken));
        }

        if ($model->load(Yii::$app->request->post())) {
            $isPasswordChange = (int)Yii::$app->request->post('hasChangePassword');
            $isTokenChange = (int)Yii::$app->request->post('hasChangeToken');

            if ($isPasswordChange === 0) {
                $model->jiraPassword = $jiraPassword;
            }

            if ($isTokenChange === 0) {
                $model->slackToken = $slackToken;
            }

            $model->save();
        }

        return $this->render('index', [
            'model' => $model,
            'passReplace' => $passReplace,
            'tokenReplace' => $tokenReplace,
        ]);
    }
}
