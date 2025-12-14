<?php

declare(strict_types=1);

namespace common\models;

use common\enums\AppleColor;
use common\enums\AppleConfig;
use common\enums\AppleStatus;
use DateInterval;
use DateTime;
use yii\db\ActiveRecord;

/**
 * Модель яблока.
 *
 * @property int         $id
 * @property string      $color        Цвет
 * @property string      $created_at   Дата создания
 * @property string|null $fell_at      Дата падения
 * @property string      $status       Статус
 * @property float       $size         Размер
 *
 * @property-read bool   $isOnTree     Находится ли яблоко на дереве
 * @property-read bool   $isFell       Упало ли яблоко
 * @property-read bool   $isRotten     Гнилое ли яблоко
 * @property-read bool   $isFullyEaten Полностью ли съедено яблоко
 */
class AppleModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%apple}}';
    }

    public function rules(): array
    {
        return [
            [['color', 'status'], 'required'],
            [['size'], 'number', 'min' => 0, 'max' => 1],
            [['color'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => AppleStatus::getAll()],
            [['color'], 'in', 'range' => AppleColor::getAll()],
            [['created_at', 'fell_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'color' => 'Цвет',
            'created_at' => 'Дата создания',
            'fell_at' => 'Дата падения',
            'status' => 'Статус',
            'size' => 'Размер',
        ];
    }

    /**
     * Проверить, находится ли яблоко на дереве.
     * Магический метод, заполняет свойство $isOnTree
     */
    public function getIsOnTree(): bool
    {
        return AppleStatus::ON_TREE === $this->status;
    }

    /**
     * Проверить, упало ли яблоко.
     *  Магический метод, заполняет свойство $isFell
     */
    public function getIsFell(): bool
    {
        return AppleStatus::FELL === $this->status;
    }

    /**
     * Проверить, гнилое ли яблоко.
     *  Магический метод, заполняет свойство $isRotten
     */
    public function getIsRotten(): bool
    {
        return AppleStatus::ROTTEN === $this->status;
    }

    /**
     * Проверить, полностью ли съедено яблоко.
     *  Магический метод, заполняет свойство $isFullyEaten
     */
    public function getIsFullyEaten(): bool
    {
        return $this->size <= 0;
    }

    /**
     * Получить размер в процентах.
     */
    public function getSizePercent(): int
    {
        return (int) ($this->size * 100);
    }

    public function getTimeUntilRotten(): ?DateTime
    {
        if (!$this->fell_at) {
            return null;
        }

        return (new DateTime($this->fell_at))->add(DateInterval::createFromDateString(AppleConfig::ROTTEN_TIME_HOURS . ' hours'));
    }
}
