<?php

namespace backend\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\domains\Project;
use common\services\google\GoogleSheetsService;
use Yii;
use common\models\domains\GoogleSheets;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * GoogleSheetsController implements the CRUD actions for GoogleSheets model.
 */
class GoogleSheetsController extends Controller
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
     * Lists all GoogleSheets models.
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => GoogleSheets::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single GoogleSheets model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new GoogleSheets model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new GoogleSheets();
        $projects = Project::find()->asArray()->notDeleted()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Задача добавлена.');

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'projects' => $projects,
        ]);
    }

    /**
     * Updates an existing GoogleSheets model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $projects = Project::find()->asArray()->notDeleted()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'projects' => $projects,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionReload(int $id): Response
    {
        /** @var GoogleSheets $model */
        $model = $this->findModel($id);
        $googleSheetsService = new GoogleSheetsService();
        $projectId = $model->project_id;
        $startDate = $model->project->load_start_date;
        $endDate = date('Y-m-d', strtotime('tomorrow'));
        if ($googleSheetsService->insertToGoogleSheet($projectId, $model->spreadsheet, $model->range, $model->sheet, $startDate, $endDate)) {
            $model->last_load_date = time();
            if ($model->update(false, ['last_load_date'])) {
                Yii::$app->session->setFlash('success', 'Задача добавлена. Данные в Google Sheets обновлены');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка добавления данных в Google Sheets');
        }

        return $this->redirect('index');
    }

    /**
     * Deletes an existing GoogleSheets model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the GoogleSheets model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return array|ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id)
    {
        $model = GoogleSheets::find()->whereId($id)->notDeleted()->one();
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
