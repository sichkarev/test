<?php

declare(strict_types=1);

namespace common\factories;

use common\models\Apple;
use yii\db\Exception;

/**
 * Фабрика для создания экземпляров Apple.
 */
final class AppleFactory
{
    /**
     * Создать новое яблоко с указанным или случайным цветом
     *
     * @param string|null $color Цвет яблока. Если null, будет установлен случайный цвет
     */
    public static function create(?string $color = null): Apple
    {
        return new Apple($color);
    }

    /**
     * Создать и сохранить новое яблоко.
     *
     * @param string|null $color Цвет яблока
     *
     * @return Apple Возвращает яблоко, если успешно сохранено
     *
     * @throws Exception если яблоко не может быть сохранено
     */
    public static function createAndSave(?string $color = null): Apple
    {
        $apple = self::create($color);

        if (!$apple->save()) {
            throw new Exception('Apple cannot be saved');
        }

        return $apple;
    }
}
