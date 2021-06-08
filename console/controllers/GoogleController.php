<?php

namespace console\controllers;

use common\services\google\GoogleSheetsService;
use yii\console\Controller;

class GoogleController extends Controller
{
    /**
     * Действие обновления таблиц Google
     */
    public function actionUpdateSheet()
    {
        $googleService = new GoogleSheetsService();
        $googleService->loadGoogleSheets();
    }
}
