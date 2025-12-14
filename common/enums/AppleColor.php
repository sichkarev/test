<?php

declare(strict_types=1);

namespace common\enums;

/**
 * Перечисление цветов яблока.
 */
final class AppleColor
{
    public const RED = 'red';
    public const GREEN = 'green';
    public const YELLOW = 'yellow';

    /**
     * Получить все доступные цвета.
     *
     * @return array<string>
     */
    public static function getAll(): array
    {
        return [
            self::RED,
            self::GREEN,
            self::YELLOW,
        ];
    }

    /**
     * Получить emoji цветов яблок.
     *
     * @return array<string>
     */
    public static function getIcons(): array
    {
        return [
            self::RED => '🍎',
            self::GREEN => '🍏',
            self::YELLOW => '🟡',
        ];
    }

    /**
     * Получить метку для конкретного цвета.
     */
    public static function getEmoji(string $color): string
    {
        $labels = self::getIcons();

        return $labels[$color] ?? ucfirst($color);
    }
}
