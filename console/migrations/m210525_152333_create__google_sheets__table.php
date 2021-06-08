<?php

use yii\db\Migration;

/**
 * Handles the creation of table `google_sheets`.
 */
class m210525_152333_create__google_sheets__table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('google_sheets', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'spreadsheet' => $this->string()->notNull(),
            'range' => $this->string()->notNull(),
            'sheet' => $this->string()->notNull(),
            'reload_days' => $this->integer()->notNull(),
            'last_load_date' => $this->integer()->defaultValue(null),
            'deleted_at' => $this->integer()->notNull()->defaultValue(0),
            'created_by_id' => $this->bigInteger(),
            'updated_by_id' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk__google_sheets__project_id__to__project__id',
            'google_sheets',
            'project_id',
            'project',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk__google_sheets__project_id__to__project__id', 'google_sheets');
        $this->dropTable('google_sheets');
    }
}
