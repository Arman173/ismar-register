<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%registration}}`.
 */
class m260318_063309_add_confirmado_column_to_registration_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('registration', 'confirmado', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('registration', 'confirmado');
    }
}
