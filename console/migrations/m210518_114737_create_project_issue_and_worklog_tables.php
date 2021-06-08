<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project}}`.
 */
class m210518_114737_create_project_issue_and_worklog_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): bool
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'project_key' => $this->string()->notNull(),
            'autoload' => $this->boolean()->defaultValue(false),
            'load_start_date' => $this->date()->defaultValue(null),
            'reload_days' => $this->integer()->defaultValue(null),
            'last_load_date' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer()->notNull()->defaultValue(0),
            'created_by_id' => $this->bigInteger()->notNull(),
            'updated_by_id' => $this->bigInteger()->notNull(),
        ]);

        $this->createIndex(
            'idx__unique__project__project_key__deleted_at',
            'project',
            ['project_key', 'deleted_at'],
            true
        );

        $this->addForeignKey(
            'fk__project__created_by_id__to__user__id',
            'project',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__project__updated_by_id__to__user__id',
            'project',
            'updated_by_id',
            'user',
            'id'
        );

        $this->createTable('issue', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->defaultValue(null),
            'level' => $this->integer(),
            'project_id' => $this->integer()->notNull(),
            'issue_key' => $this->string()->notNull(),
            'issue_summary' => $this->string()->notNull(),
            'issue_type' => $this->string()->null(),
            'deleted_at' => $this->integer()->notNull()->defaultValue(0),
            'created_by_id' => $this->bigInteger(),
            'updated_by_id' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk__issue__project_id__to__project__id',
            'issue',
            'project_id',
            'project',
            'id'
        );

        $this->addForeignKey(
            'fk__issue__parent_id__to__issue__id',
            'issue',
            'parent_id',
            'issue',
            'id'
        );

        $this->addForeignKey(
            'fk__issue__created_by_id__to__user__id',
            'issue',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__issue__updated_by_id__to__user__id',
            'issue',
            'updated_by_id',
            'user',
            'id'
        );

        $this->createTable('worklog', [
            'id' => $this->primaryKey(),
            'worklog_id' => $this->integer(),
            'issue_id' => $this->integer(),
            'date' => $this->date(),
            'author_id' => $this->integer(),
            'timespent' => $this->integer(),
            'worklog_comment' => $this->text()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'deleted_at' => $this->integer()->defaultValue(0),
            'created_by_id' => $this->bigInteger(),
            'updated_by_id' => $this->bigInteger(),
        ]);

        $this->addForeignKey(
            'fk__worklog__issue_id__to__issue__id',
            'worklog',
            'issue_id',
            'issue',
            'id'
        );

        $this->addForeignKey(
            'fk__worklog__created_by_id__to__user__id',
            'worklog',
            'created_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__worklog__updated_by_id__to__user__id',
            'worklog',
            'updated_by_id',
            'user',
            'id'
        );

        $this->addForeignKey(
            'fk__worklog__author_id__to__user__id',
            'worklog',
            'author_id',
            'user',
            'id'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): bool
    {
        $this->dropForeignKey('fk__worklog__author_id__to__user__id', 'worklog');
        $this->dropForeignKey('fk__worklog__updated_by_id__to__user__id', 'worklog');
        $this->dropForeignKey('fk__worklog__created_by_id__to__user__id', 'worklog');
        $this->dropForeignKey('fk__worklog__issue_id__to__issue__id', 'worklog');
        $this->dropTable('worklog');
        $this->dropForeignKey('fk__issue__updated_by_id__to__user__id', 'issue');
        $this->dropForeignKey('fk__issue__created_by_id__to__user__id', 'issue');
        $this->dropForeignKey('fk__issue__parent_id__to__issue__id', 'issue');
        $this->dropForeignKey('fk__issue__project_id__to__project__id', 'issue');
        $this->dropTable('issue');
        $this->dropForeignKey('fk__project__updated_by_id__to__user__id', 'project');
        $this->dropForeignKey('fk__project__created_by_id__to__user__id', 'project');
        $this->dropTable('project');

        return true;
    }
}
