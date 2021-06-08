<?php

namespace backend\controllers;

use backend\models\forms\UserForm;
use backend\modules\rbac\models\enums\Permission;
use common\models\domains\Calendar;
use common\models\enums\UserType;
use Yii;
use common\models\domains\User;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Permission::USER],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->notSystem()->notDeleted(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidArgumentException|Exception
     */
    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $model->scenario = UserForm::SCENARIO_CHANGE_PASS;

        $dataProvider = new ActiveDataProvider([
            'query' => Calendar::find()->notDeleted()->whereUserId($model->id),
        ]);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->validate()) {
                $model->setPassword($model->password);
                $model->save(false);
                Yii::$app->getSession()->setFlash('success', 'Пароль успешно изменен');
            }
        }
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new UserForm();
        if (Yii::$app->request->isPost) {
            $model->scenario = UserForm::SCENARIO_CREATE;
            $model->load(Yii::$app->request->post());
            if ($model->validate()) {
                $model->type = UserType::USER;
                $model->setPassword($model->password);
                $model->generateAuthKey();
                $model->save();

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $model->scenario = UserForm::SCENARIO_UPDATE;

        if ($model->load(Yii::$app->request->post())) {
            $oldSlackEmail = $model->getOldAttribute('slack_email');
            if ($model->slack_email != $oldSlackEmail) {
                $cache = Yii::$app->cache;
                $key = 'slackId.' . $oldSlackEmail;
                if ($cache->exists($key)) {
                    $cache->delete($key);
                }
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Добавление пользовательского события
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionAddEvent(int $id)
    {
        $user = $this->findModel($id);
        $calendar = new Calendar();
        $calendar->scenario = Calendar::SCENARIO_CREATE_USER_EVENT;
        $post = Yii::$app->request->post();

        if ($calendar->load($post)) {
            $calendar->user_id = $user->id;

            if ($calendar->save()) {
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        return $this->render('add-event', [
            'calendar' => $calendar,
            'user' => $user,
        ]);
    }

    /**
     * Удаление пользовательского события
     *
     * @param int $id
     * @return Response
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionDeleteEvent(int $id): Response
    {
        /** @var Calendar $calendar */
        $calendar = Calendar::find()->whereId($id)->one();
        $userId = $calendar->user_id;

        $calendar->delete();

        return $this->redirect(['view', 'id' => $userId]);
    }

    /**
     * Изменение пользовательского события
     *
     * @param int $id
     * @return string|Response
     */
    public function actionUpdateEvent(int $id)
    {
        /** @var Calendar $calendar */
        $calendar = Calendar::find()->whereId($id)->one();
        $userId = $calendar->user_id;
        $user = User::find()->notDeleted()->notSystem()->whereId($userId)->one();

        if ($calendar->load(Yii::$app->request->post()) && $calendar->save()) {
           return $this->redirect(['view', 'id' => $userId]);
        }

        return $this->render('update-event', [
            'calendar' => $calendar,
            'user' => $user,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return array|User|ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id)
    {
        $model = UserForm::find()->whereId($id)->notDeleted()->notSystem()->one();
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
