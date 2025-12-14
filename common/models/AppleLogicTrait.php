<?php

declare(strict_types=1);

namespace common\models;

use common\service\AppleService;

/**
 * Так как в задаче было требование управлять состоянием через методы модели (аля AR как Domain Entity),
 * я решил перечислить методы в трейте, чтобы не перемешивать описание AR модели уровня БД и бизнес логику уровня домена.
 */
trait AppleLogicTrait
{
    public function fallToGround(): bool
    {
        return AppleService::fallToGround($this->model);
    }

    public function eat(int $percent): bool
    {
        return AppleService::eat($this->model, $percent);
    }
}
