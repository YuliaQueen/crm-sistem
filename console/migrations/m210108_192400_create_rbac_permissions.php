<?php

use backend\modules\rbac\models\enums\Permission;
use backend\modules\rbac\RbacService;
use yii\db\Migration;

class m210108_192400_create_rbac_permissions extends Migration
{
    /**
     * @return bool|void
     * @throws \yii\base\Exception
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function safeUp()
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        $rbacService->createRole(Permission::ADMIN, 'Админ');

        $rbacService->createPermission(Permission::USER, 'Управление пользователями');
        $rbacService->createPermission(Permission::RBAC, 'Управление разрешениями');

        $rbacService->addChild(Permission::ADMIN, Permission::USER);
        $rbacService->addChild(Permission::ADMIN, Permission::RBAC);

        return true;
    }

    /**
     * @return bool|void
     * @noinspection PhpUnused
     */
    public function safeDown()
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        $rbacService->removeChild(Permission::ADMIN, Permission::RBAC);
        $rbacService->removeChild(Permission::ADMIN, Permission::USER);

        if ($rbacService->findItem(Permission::RBAC)) {
            $rbacService->removePermission(Permission::RBAC);
        }

        if ($rbacService->findItem(Permission::USER)) {
            $rbacService->removePermission(Permission::USER);
        }

        if ($rbacService->findRole(Permission::ADMIN)) {
            $rbacService->removeRole(Permission::ADMIN);
        }

        return true;
    }
}
