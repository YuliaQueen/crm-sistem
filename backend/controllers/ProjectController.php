<?php

namespace backend\controllers;

use backend\modules\rbac\models\enums\Permission;
use common\models\domains\Project;
use Exception;
use JiraRestApi\JiraException;
use JsonMapper_Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\db\Transaction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProjectController extends Controller
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

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Project::find()->notDeleted(),
            ]
        );

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|Response
     * @throws Throwable
     */
    public function actionCreateProject()
    {
        $project = new Project();
        $project->scenario = Project::SCENARIO_CREATE_PROJECT;

        if ($project->load(Yii::$app->request->post()) && $project->save()) {
            return $this->redirect('index');
        }

        return $this->render('worklogs-loading', compact('project'));
    }

    /**
     * Перезагрузка проекта
     * @param $id
     * @return Response
     * @throws JiraException
     * @throws JsonMapper_Exception
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws \yii\base\Exception
     */
    public function actionReload($id): Response
    {
        /** @var Project $project */
        $project = $this->findModel($id);
        $project->scenario = Project::SCENARIO_RELOAD_PROJECT;
        $projectId = $project->id;
        $projectKey = $project->project_key;
        $projectStartDate = $project->load_start_date;
        if ($project->saveIssuesAndWorklogsData($project, $projectStartDate, $projectId)) {
            $project->last_load_date = time();
            if (!$project->save()) {
                Yii::error('Не удалось сохранить проект', 'project');
            }
            Yii::$app->session->setFlash('success', "Данные проекта {$projectKey} обновлены");
        } else {
            Yii::$app->session->setFlash('error', "Ошибка обновления проекта {$projectKey}");
        }
        return $this->redirect('index');
    }

    /**
     * Редактирование проекта
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /** @var Project $project */
        $project = $this->findModel($id);

        if ($project->load(Yii::$app->request->post()) && $project->save()) {
            return $this->redirect('index');
        }

        return $this->render('update', compact('project'));
    }

    /**
     * Удаление проекта с его задачами и ворклогами задач
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id): Response
    {
        /** @var Project $project */
        $project = $this->findModel($id);
        $projectKey = $project->project_key;
        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($project->deleteIssuesAndWorklogsByProject($project)) {
                $project->delete();
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Проект {$projectKey} успешно удален");
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $exception) {
            Yii::error($exception, 'project');
            Yii::$app->session->setFlash('error', "Проект {$projectKey} не был удален");
        }

        return $this->redirect('index');
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        $model = Project::find()->whereId($id)->notDeleted()->one();
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
