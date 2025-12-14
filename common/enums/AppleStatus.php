<?php

declare(strict_types=1);

namespace common\enums;

/**
 * Перечисление статусов яблока.
 */
final class AppleStatus
{
    public const ON_TREE = 'on_tree';
    public const FELL = 'fell';
    public const ROTTEN = 'rotten';

    /**
     * Получить все доступные статусы.
     *
     * @return array<string>
     */
    public static function getAll(): array
    {
        return [
            self::ON_TREE,
            self::FELL,
            self::ROTTEN,
        ];
    }

    /**
     * Получить человекочитаемые метки.
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::ON_TREE => 'На дереве',
            self::FELL => 'Упало',
            self::ROTTEN => 'Гнилое',
        ];
    }

    /**
     * Получить метку для конкретного статуса.
     */
    public static function getLabel(string $status): string
    {
        $labels = self::getLabels();

        return $labels[$status] ?? $status;
    }
}
