<?php

use backend\modules\rbac\models\enums\Permission;
use backend\modules\rbac\RbacService;
use yii\db\Migration;

/**
 * Handles the creation of table `settings`.
 */
class m210111_201414_create_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function safeUp()
    {
        $this->createTable('settings', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string()->notNull(),
            'value' => $this->string(),

            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false),
            'created_by_id' => $this->bigInteger()->notNull(),
            'updated_by_id' => $this->bigInteger()->notNull(),
        ]);

        $this->addForeignKey(
            'fk__settings__created_by_id__to__user__id',
            'settings',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__settings__updated_by_id__to__user__id',
            'settings',
            'updated_by_id',
            'user',
            'id'
        );

        // Добавляет разрешения на управление системными настройками.
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;
        $rbacService->createPermission(Permission::SETTINGS, 'Управление системными настройками');
        $rbacService->addChild(Permission::ADMIN, Permission::SETTINGS);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        if ($rbacService->findItem(Permission::SETTINGS)) {
            $rbacService->removeChild(Permission::ADMIN, Permission::SETTINGS);
            $rbacService->removePermission(Permission::SETTINGS);
        }

        $this->dropForeignKey('fk__settings__updated_by_id__to__user__id', 'settings');
        $this->dropForeignKey('fk__settings__created_by_id__to__user__id', 'settings');
        $this->dropTable('settings');

        return true;
    }
}
