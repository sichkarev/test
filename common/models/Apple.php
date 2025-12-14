<?php

declare(strict_types=1);

namespace common\models;

use common\enums\AppleColor;
use common\enums\AppleStatus;
use common\service\AppleService;

/**
 * Класс для работы с яблоками.
 * Обертка над AppleModel с бизнес-логикой.
 *
 * @property string $color
 * @property string $percent
 * @property string $sizePercent
 * @property string $status
 * @property string $created_at
 * @property string $fell_at
 * @property float $size
 */
final class Apple
{
    use AppleLogicTrait;
    private AppleModel $model;

    /**
     * Создать новое яблоко с указанным цветом.
     */
    public function __construct(?string $color = null)
    {
        $apple = new AppleModel();
        $apple->color = $color ?? AppleColor::getAll()[array_rand(AppleColor::getAll())];
        $apple->status = AppleStatus::ON_TREE;
        $apple->size = 1.0;

        $this->model = $apple;
    }

    /**
     * Сохранить яблоко в базу данных.
     */
    public function save(): bool
    {
        return $this->model->save();
    }

    public function updateRottenStatus(): void
    {
        AppleService::updateRottenStatus($this->model);
    }

    /**
     * Магические методы для доступа к свойствам модели.
     */
    public function __get(string $name)
    {
        return $this->model->$name;
    }

    public function __set(string $name, $value): void
    {
        $this->model->$name = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->model->$name);
    }
}
