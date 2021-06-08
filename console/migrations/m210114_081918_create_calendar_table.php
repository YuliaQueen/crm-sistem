<?php

use backend\modules\rbac\models\enums\Permission;
use backend\modules\rbac\RbacService;
use yii\db\Migration;

/**
 * Handles the creation of table `calendar`.
 */
class m210114_081918_create_calendar_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('calendar', [
            'id' => $this->bigPrimaryKey(),
            'date' => $this->date()->notNull(),
            'type' => $this->tinyInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->notNull()->defaultValue(0),
            'created_by_id' => $this->bigInteger()->notNull(),
            'updated_by_id' => $this->bigInteger()->notNull(),
        ]);

        $this->createIndex(
            'idx__unique__date__deleted_at',
            'calendar',
            ['date', 'deleted_at'],
            true
        );

        $this->addForeignKey(
            'fk__calendar__created_by_id__to__user__id',
            'calendar',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__calendar__updated_by_id__to__user__id',
            'calendar',
            'updated_by_id',
            'user',
            'id'
        );

        // Добавляет разрешения на управление настройками календаря.
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;
        $rbacService->createPermission(Permission::CALENDAR, 'Управление настройками календаря');
        $rbacService->addChild(Permission::ADMIN, Permission::CALENDAR);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$app->rbacService;

        if ($rbacService->findItem(Permission::CALENDAR)) {
            $rbacService->removeChild(Permission::ADMIN, Permission::CALENDAR);
            $rbacService->removePermission(Permission::CALENDAR);
        }

        $this->dropForeignKey('fk__calendar__updated_by_id__to__user__id', 'calendar');
        $this->dropForeignKey('fk__calendar__created_by_id__to__user__id', 'calendar');
        $this->dropTable('calendar');

        return true;
    }
}
