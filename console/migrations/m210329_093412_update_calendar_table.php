<?php

use yii\db\Migration;

/**
 * Class m210329_093412_update_calendar_table
 */
class m210329_093412_update_calendar_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): bool
    {
        $this->renameColumn('calendar', 'date', 'date_start');
        $this->addColumn('calendar', 'date_end', $this->date());
        $this->execute('update calendar set date_end = date_start;');
        $this->addColumn('calendar', 'user_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'fk__calendar__user_id__to__user__id',
            'calendar',
            'user_id',
            'user',
            'id'
        );

        $this->dropIndex('idx__unique__date__deleted_at', 'calendar');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): bool
    {
        $this->createIndex(
            'idx__unique__date__deleted_at',
            'calendar',
            ['date_start', 'deleted_at'],
            true
        );
        $this->dropForeignKey('fk__calendar__user_id__to__user__id', 'calendar');
        $this->renameColumn('calendar', 'date_start', 'date');
        $this->dropColumn('calendar', 'date_end');
        $this->dropColumn('calendar', 'user_id');

        return true;
    }
}
