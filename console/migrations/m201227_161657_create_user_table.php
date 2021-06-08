<?php

use common\models\enums\UserType;
use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m201227_161657_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'user',
            [
                'id' => $this->bigPrimaryKey(),
                'type' => $this->smallInteger(),
                'name' => $this->string(),
                'email' => $this->string(),
                'password_hash' => $this->string(),
                'auth_key' => $this->string(),
                'department_id' => $this->smallInteger(),
                'jira_user' => $this->string(),
                'slack_email' => $this->string(),
                'employee' => $this->smallInteger(),
                'weekly_load' => $this->smallInteger(),
                'leadership_id' => $this->bigInteger(),
                'date_of_employment' => $this->date(),
                'date_of_dismissal' => $this->date(),
                'deleted_at' => $this->integer()->notNull()->defaultValue(0),
                'created_by_id' => $this->bigInteger(),
                'updated_by_id' => $this->bigInteger(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull()
            ]
        );

        $this->createIndex(
            'idx__unique__user__email',
            'user',
            ['email', 'deleted_at'],
            true
        );

        $this->addForeignKey(
            'fk__user__leadership_id__to__user__id',
            'user',
            'leadership_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__user__created_by_id__to__user__id',
            'user',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__user__updated_by_id__to__user__id',
            'user',
            'updated_by_id',
            'user',
            'id'
        );

        $this->insert('user', ['type' => UserType::SYSTEM, 'created_at' => time(), 'updated_at' => time()]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk__user__updated_by_id__to__user__id', 'user');
        $this->dropForeignKey('fk__user__created_by_id__to__user__id', 'user');
        $this->dropForeignKey('fk__user__leadership_id__to__user__id', 'user');

        $this->dropTable('user');

        return true;
    }
}
