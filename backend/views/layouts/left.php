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
                        'items' => [
                            [
                                'label' => 'Jira Worklogs report',
                                'icon' => 'file',
                                'url' => '/report',
                                'active' => Yii::$app->controller->id === 'report',
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
