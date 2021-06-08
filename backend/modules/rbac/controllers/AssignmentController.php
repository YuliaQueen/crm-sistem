<?php

namespace backend\modules\rbac\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\domains\User;
use Exception;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use backend\modules\rbac\models\domains\AssignmentModel;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;

class AssignmentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Permission::RBAC]
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'view' => ['get'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['assign', 'remove'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Displays all AuthAssignment model.
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->with('authAssignments.itemName')->notDeleted()->notSystem()
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single Assignment model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionView(int $id)
    {
        $user = $this->findUser($id);

        $assignment = new AssignmentModel($user);

        return $this->render('view', compact('user', 'assignment'));
    }

    /**
     * Assign items
     * @param int $id
     * @return array
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionAssign(int $id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = new AssignmentModel($this->findUser($id));
        $assignmentModel->assign($items);

        return $assignmentModel->getItems();
    }

    /**
     * Remove items
     * @param int $id
     * @return array
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionRemove(int $id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = new AssignmentModel($this->findUser($id));
        $assignmentModel->revoke($items);

        return $assignmentModel->getItems();
    }

    /**
     * @param int $id
     * @return array|User|ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findUser(int $id)
    {
        /** @var User|null $user */
        $user = User::find()->whereId($id)->notDeleted()->notSystem()->one();

        if ($user === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $user;
    }
}
