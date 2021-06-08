<?php

namespace common\services\google;

use common\models\domains\GoogleSheets;
use common\models\domains\Worklog;
use common\models\enums\IssueLevel;
use Google_Client;
use Google_Service_Exception;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_ValueRange;
use Yii;
use yii\helpers\VarDumper;

class GoogleSheetsService
{
    private object $googleClient;

    public function __construct()
    {
        // Путь к файлу ключа сервисного аккаунта
        $googleAccountKeyFilePath = Yii::$app->params['googleKeyPath'];
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $googleAccountKeyFilePath);

        $this->googleClient = new Google_Client();
        $this->googleClient->useApplicationDefaultCredentials();

        $this->googleClient->addScope(Google_Service_Sheets::SPREADSHEETS);
    }

    /**
     * Метод вставляет строки в Google таблицу,
     * форматирует заголовки и ширину столбцов, удаляет дубли строк
     * @param $projectId
     * @param $spreadsheetDB
     * @param $rangeDB
     * @param $sheetDB
     * @param $dateStart
     * @param $dateEnd
     * @return bool
     */
    public function insertToGoogleSheet($projectId, $spreadsheetDB, $rangeDB, $sheetDB, $dateStart, $dateEnd): bool
    {
        $rowsArray = $this->getRowsArrayFromDB($projectId, $dateStart, $dateEnd);
        $client = $this->googleClient;
        $service = new Google_Service_Sheets($client);

        $spreadsheetId = $spreadsheetDB; //ID таблицы

        $valueRange = new Google_Service_Sheets_ValueRange();

        $valueRange->setValues($rowsArray);

        $range = $rangeDB; //Диапазон для вставки данных

        $conf = ['valueInputOption' => 'USER_ENTERED'];

        // формирование массива реквестов на форматирование таблицы, сортировку и удаление дублей
        $requests = [
            // присвоение типа date столбцу 'Updated'
            new Google_Service_Sheets_Request(
                [
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                            'startColumnIndex' => 14,
                            'endColumnIndex' => 15,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'numberFormat' => [
                                    'type' => 'DATE',
                                    'pattern' => 'dd.mm.yyyy hh:mm:ss',
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat.numberFormat',
                    ],
                ]
            ),
            // сортировка по столбцу 'Updated' - DESC
            new Google_Service_Sheets_Request(
                [
                    'sortRange' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                            'startRowIndex' => 1,
                            'startColumnIndex' => 0,
                        ],
                        'sortSpecs' => [
                            [
                                'dimensionIndex' => 14,
                                'sortOrder' => 'DESCENDING',
                            ],
                        ],
                    ],
                ]
            ),
            // форматирование заголовка
            new Google_Service_Sheets_Request(
                [
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                            'startRowIndex' => 0,
                            'endRowIndex' => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => 15,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'horizontalAlignment' => 'CENTER',
                                'textFormat' => [
                                    'fontSize' => 10,
                                    'bold' => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(textFormat,horizontalAlignment)',
                    ],
                ]
            ),
            // автоподбор ширины столбцов
            new Google_Service_Sheets_Request(
                [
                    'autoResizeDimensions' => [
                        'dimensions' => [
                            'sheetId' => $sheetDB,
                            'dimension' => 'COLUMNS',
                            'startIndex' => 0,
                            'endIndex' => 12,
                        ],
                    ],
                ]
            ),
            // закрепление строки заголовков таблицы
            new Google_Service_Sheets_Request(
                [
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId' => $sheetDB,
                            'gridProperties' => [
                                'frozenRowCount' => 1,
                            ],
                        ],
                        'fields' => 'gridProperties.frozenRowCount',
                    ],
                ]
            ),
            // присвоение типа date столбцу 'Date'
            new Google_Service_Sheets_Request(
                [
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                            'startColumnIndex' => 9,
                            'endColumnIndex' => 10,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'numberFormat' => [
                                    'type' => 'DATE',
                                    'pattern' => 'dd.mm.yyyy',
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat.numberFormat',
                    ],
                ]
            ),
            // удаление дубликатов строк по столбцу 'Worklog ID'
            new Google_Service_Sheets_Request(
                [
                    'deleteDuplicates' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                        ],
                        'comparisonColumns' => [
                            [
                                'sheetId' => $sheetDB,
                                'dimension' => 'COLUMNS',
                                'startIndex' => 13,
                                'endIndex' => 14,
                            ],
                        ],
                    ],
                ]
            ),
            // сортировка по столбцу 'Date' - ASCENDING
            new Google_Service_Sheets_Request(
                [
                    'sortRange' => [
                        'range' => [
                            'sheetId' => $sheetDB,
                            'startRowIndex' => 1,
                            'startColumnIndex' => 0,
                        ],
                        'sortSpecs' => [
                            [
                                'dimensionIndex' => 9,
                                'sortOrder' => 'ASCENDING',
                            ],
                        ],
                    ],
                ]
            ),
        ];

        $result = false;
        $i = 1;
        while (($result === false) && ($i <= 10)) {
            try {
                // вставка строк в таблицу
                $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);

                $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
                    [
                        'requests' => $requests,
                    ]
                );

                $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
                Yii::info(VarDumper::dumpAsString($response, 'google_sheets_service'));
                $result = true;
            } catch (Google_Service_Exception $e) {
                $reason = $e->getErrors()[0]['reason'];
                // При превышении лимита на количество запросов к API ставит паузу и продолжает цикл while, который
                // повторно сделает запрос к API после паузы.
                Yii::error($e, 'google_sheets_service');
                if ($reason === 'rateLimitExceeded') {
                    sleep(100);
                    $i++;
                    $result = false;
                    if ($i > 10) {
                        $result = false;
                        break;
                    }
                } else {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Обновление Google Sheets
     */
    public function loadGoogleSheets()
    {
        $googleSheetsTasks = GoogleSheets::find()->notDeleted()->all();
        if (!empty($googleSheetsTasks)) {
            foreach ($googleSheetsTasks as $task) {
                $projectId = $task->project_id;
                $spreadsheet = $task->spreadsheet;
                $sheet = $task->sheet;
                $range = $task->range;
                $dateStart = date('Y-m-d', strtotime("today - {$task->reload_days} days"));
                $dateEnd = date('Y-m-d', strtotime('tomorrow'));
                $this->insertToGoogleSheet($projectId, $spreadsheet, $range, $sheet, $dateStart, $dateEnd);
            }
        }
    }

    /**
     * @return array
     * Получает ворклоги из БД и формирует массив строк для вставки в Google Sheets
     */
    private function getRowsArrayFromDB($projectId, $dateStart, $dateEnd): array
    {
        /** @var Worklog[] $worklogs */
        $worklogs = Worklog::find()
            ->notDeleted()
            ->whereProjectId($projectId)
            ->getBetweenDate($dateStart, $dateEnd)
            ->with('issue.project', 'issue.parent.parent')
            ->all();
        $rowsArray = [];
        $tableHeaders = [
            'Project key',
            'Epic Key',
            'Epic Summary',
            'Task Key',
            'Task Summary',
            'Task Type',
            'SubTask Key',
            'SubTask Summary',
            'SubTask Type',
            'Worklog Date',
            'Author',
            'Timespent',
            'Worklog Summary',
            'Worklog ID',
            'Updated',
        ];
        $rowsArray[] = $tableHeaders;
        if (!empty($worklogs)) {
            foreach ($worklogs as $worklog) {
                if ($worklog->issue->level === IssueLevel::SUB_TASK) {
                    $parent = $worklog->issue->parent;
                    if ($parent->level === IssueLevel::EPIC) {
                        $epicKey = $parent->issue_key;
                        $epicSummary = $parent->issue_summary;
                        $taskKey = '';
                        $taskSummary = '';
                        $taskType = '';
                    } else {
                        $epicKey = $parent->parent->issue_key ?? '';
                        $epicSummary = $parent->parent->issue_summary ?? '';
                        $taskKey = $parent->issue_key;
                        $taskSummary = $parent->issue_summary;
                        $taskType = $parent->issue_type;
                    }
                    $subtaskKey = $worklog->issue->issue_key;
                    $subtaskSummary = $worklog->issue->issue_summary;
                    $subtaskType = $worklog->issue->issue_type;
                } elseif ($worklog->issue->level === IssueLevel::EPIC) {
                    $epicKey = $worklog->issue->issue_key;
                    $epicSummary = $worklog->issue->issue_summary;
                    $taskKey = '';
                    $taskType = '';
                    $taskSummary = '';
                    $subtaskKey = '';
                    $subtaskSummary = '';
                    $subtaskType = '';
                } else {
                    $epicKey = $worklog->issue->parent->issue_key ?? '';
                    $epicSummary = $worklog->issue->parent->issue_summary ?? '';
                    $taskKey = $worklog->issue->issue_key;
                    $taskSummary = $worklog->issue->issue_summary;
                    $taskType = $worklog->issue->issue_type;
                    $subtaskKey = '';
                    $subtaskSummary = '';
                    $subtaskType = '';
                }
                $rowsArray[] = [
                    $worklog->issue->project->project_key ?? '', // project
                    $epicKey,
                    $epicSummary,
                    $taskKey,
                    $taskSummary,
                    $taskType,
                    $subtaskKey,
                    $subtaskSummary,
                    $subtaskType,
                    $worklog->date ?? '', // worklog date
                    $worklog->author->name ?? '', // worklog author
                    round($worklog->timespent, 2) / 60 / 60 ?? '', // worklog timespent
                    $worklog->worklog_comment ?? '', // worklog comment
                    $worklog->worklog_id ?? '', // worklog id
                    date('Y-m-d H:i:s', time()),
                ];
            }
        }
        return $rowsArray;
    }
}
