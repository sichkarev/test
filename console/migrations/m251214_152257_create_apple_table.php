<?php

declare(strict_types=1);

use yii\db\Migration;

class m251214_152257_create_apple_table extends Migration
{
    private const TABLE_NAME = '{{%apple}}';

    public function safeUp(): void
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'color' => $this->string(50)->notNull()->comment('Цвет'),
            'created_at' => $this
                ->timestamp()
                ->notNull()
                ->comment('Дата создания'),
            'fell_at' => $this->timestamp()->null()->comment('Дата падения'),
            'status' => $this->string(20)->notNull()->defaultValue('on_tree')->comment('Статус'),
            'size' => $this
                ->float()
                ->notNull()
                ->defaultValue(1.0)
                ->comment('Размер'),
        ]);

        $this->createIndex(
            'idx-apple-status',
            '{{%apple}}',
            'status'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
