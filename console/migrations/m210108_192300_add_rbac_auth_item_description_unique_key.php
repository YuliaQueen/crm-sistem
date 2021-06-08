<?php

use yii\db\Migration;

/**
 * Class m210108_192300_add_rbac_auth_item_description_unique_key
 */
class m210108_192300_add_rbac_auth_item_description_unique_key extends Migration
{
    /**
     * {@inheritdoc}
     * @noinspection PhpUnused
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx__unique__auth_item__description',
            'auth_item',
            'description',
            true
        );

        return true;
    }

    /**
     * {@inheritdoc}
     * @noinspection PhpUnused
     */
    public function safeDown()
    {
        $this->dropIndex('idx__unique__auth_item__description', 'auth_item');

        return true;
    }
}
