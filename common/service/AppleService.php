<?php

namespace common\service;

use common\enums\AppleConfig;
use common\enums\AppleStatus;
use common\models\AppleModel;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

final class AppleService
{
    public static function updateRottenStatus(AppleModel $model): bool
    {
        if ($model->fell_at === null) {
            return false;
        }

        if ($model->isFell && !$model->isRotten) {
            $timeSinceFell = (new DateTime($model->fell_at))
                ->add(DateInterval::createFromDateString(AppleConfig::ROTTEN_TIME_HOURS . ' hours'));

            if ($timeSinceFell->getTimestamp() <= (new DateTime())->getTimestamp()) {
                $model->status = AppleStatus::ROTTEN;

                return $model->save();
            }
        }

        return false;
    }

    public static function fallToGround(AppleModel $model): bool
    {
        if (!$model->isOnTree) {
            throw new RuntimeException('Яблоко не на дереве');
        }

        $model->fell_at = (new DateTime())->format('Y-m-d H:i:s');
        $model->status = AppleStatus::FELL;

        return $model->save();
    }

    public static function eat(AppleModel $model, int $percent): bool
    {
        if ($percent <= 0 || $percent > 100) {
            throw new InvalidArgumentException('Процент должен быть от 1 до 100');
        }

        if ($model->size && $percent > $model->size * 100) {
            throw new InvalidArgumentException('Процент должен быть не более ' . $model->size * 100);
        }

        // Обновить статус если испортилось
        self::updateRottenStatus($model);

        if ($model->isOnTree) {
            throw new RuntimeException('Нельзя съесть яблоко, которое висит на дереве');
        }

        if ($model->isRotten) {
            throw new RuntimeException('Нельзя съесть гнилое яблоко');
        }

        // Вычислить новый размер
        $model->size = max(0, $model->size * 100 - $percent) / 100;

        // Сохранить яблоко
        if (!$model->save(false)) {
            return false;
        }

        // Удалить если полностью съедено
        if ($model->isFullyEaten) {
            return false !== $model->delete();
        }

        return true;
    }
}
