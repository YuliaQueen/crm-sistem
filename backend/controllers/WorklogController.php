<?php

namespace backend\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\domains\Project;
use common\models\domains\Worklog;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WorklogController implements the CRUD actions for Worklog model.
 */
class WorklogController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Permission::RECEIVING_WORKLOGS],
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
     * Lists all Worklog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $projects = Project::find()->asArray()->notDeleted()->all();

        $dataProvider = new ActiveDataProvider([
            'query' => Worklog::find()->notDeleted()->with('author', 'issue.project'),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'projects' => $projects,
        ]);
    }

    /**
     * Displays a single Worklog model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing Worklog model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Worklog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return array|ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id)
    {
        $model = Worklog::find()->whereId($id)->notDeleted()->one();
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
