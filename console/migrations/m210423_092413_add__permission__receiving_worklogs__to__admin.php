<?php

use backend\modules\rbac\models\enums\Permission;
use backend\modules\rbac\RbacService;
use yii\db\Migration;

/**
 * Class m210423_092413_add__permission__receiving_worklogs__to__admin__role
 */
class m210423_092413_add__permission__receiving_worklogs__to__admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): bool
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        $rbacService->createPermission(Permission::RECEIVING_WORKLOGS, 'Доступ к jira worklogs report');

        $rbacService->addChild(Permission::ADMIN, Permission::RECEIVING_WORKLOGS);

        return true;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function safeDown(): bool
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        $rbacService->removeChild(Permission::ADMIN, Permission::RECEIVING_WORKLOGS);

        if ($rbacService->findItem(Permission::RECEIVING_WORKLOGS)) {
            $rbacService->removePermission(Permission::RECEIVING_WORKLOGS);
        }
        return true;
    }
}
