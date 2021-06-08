<?php
/* @var string $directoryAsset */

use backend\modules\rbac\helpers\RbacHelper;
use backend\modules\rbac\models\enums\Permission;

?>

<aside class="main-sidebar">
    <section class="sidebar">
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => [
                    ['label' => 'Menu', 'options' => ['class' => 'header']],
                    ['label' => 'Главная', 'icon' => 'globe', 'url' => ['/']],
                    [
                        'label' => 'Отчеты',
                        'icon' => 'files-o',
                        'url' => '#',
                        'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                        'items' => [
                            [
                                'label' => 'Jira Worklogs report',
                                'icon' => 'file',
                                'url' => '/report',
                                'active' => Yii::$app->controller->id === 'report',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                            [
                                'label' => 'Create Project',
                                'icon' => 'file',
                                'url' => '/project/create-project',
                                'active' => Yii::$app->controller->route === 'project/create-project',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                            [
                                'label' => 'All Projects',
                                'icon' => 'file',
                                'url' => '/project',
                                'active' => Yii::$app->controller->route === 'project/index',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                            [
                                'label' => 'All Issues',
                                'icon' => 'file',
                                'url' => '/issue/index',
                                'active' => Yii::$app->controller->id === 'issue',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                            [
                                'label' => 'All Worklogs',
                                'icon' => 'file',
                                'url' => '/worklog/index',
                                'active' => Yii::$app->controller->id === 'worklog',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                            [
                                'label' => 'Google Sheets list',
                                'icon' => 'file',
                                'url' => '/google-sheets/index',
                                'active' => Yii::$app->controller->id === 'google-sheets',
                                'visible' => RbacHelper::checkAccess([Permission::RECEIVING_WORKLOGS]),
                            ],
                        ],
                    ],
                    [
                        'label' => 'Пользователи',
                        'icon' => 'user',
                        'url' => '#',
                        'visible' => RbacHelper::checkAccess([Permission::USER, Permission::RBAC]),
                        'items' => [
                            [
                                'label' => "Список пользователей",
                                'icon' => 'users',
                                'url' => ['/user'],
                                'active' => Yii::$app->controller->id === 'user',
                                'visible' => RbacHelper::checkAccess([Permission::USER]),
                            ],
                            [
                                'label' => "Управление правами",
                                'icon' => 'filter',
                                'url' => ['/rbac/assignment'],
                                'active' => Yii::$app->controller->id === 'assignment' || Yii::$app->controller->id === 'role',
                                'visible' => RbacHelper::checkAccess([Permission::RBAC]),
                            ],
                        ],
                    ],
                    [
                        'label' => 'Настройки',
                        'icon' => 'wrench',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Настройки системы',
                                'icon' => 'cog',
                                'url' => '/settings',
                                'active' => Yii::$app->controller->id === 'settings',
                                'visible' => RbacHelper::checkAccess([Permission::SETTINGS]),
                            ],
                            [
                                'label' => 'Настройки календаря',
                                'icon' => 'calendar',
                                'url' => '/calendar',
                                'active' => Yii::$app->controller->id === 'calendar',
                                'visible' => RbacHelper::checkAccess([Permission::CALENDAR]),
                            ],
                        ],
                        'visible' => RbacHelper::checkAccess([Permission::SETTINGS, Permission::CALENDAR]),
                    ],
                ],
            ],
        ) ?>
    </section>
</aside>
