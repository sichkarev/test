<?php

declare(strict_types=1);

namespace backend\services;

use common\enums\AppleStatus;
use common\factories\AppleFactory;
use common\models\AppleModel;
use common\service\AppleService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use yii\db\Exception;

/**
 * Сервис для обработки логики контроллера яблок.
 */
final class AppleControllerService
{
    /**
     * Обновить статус гнилости для всех упавших яблок.
     *
     * В нормальной системе такие задачи лучше решать кроном
     */
    public function updateAllRottenStatuses(): void
    {
        $apples = AppleModel::find()
            ->where(['status' => AppleStatus::FELL])
            ->all();

        foreach ($apples as $apple) {
            AppleService::updateRottenStatus($apple);
        }
    }

    /**
     * Сгенерировать случайное количество яблок.
     *
     * @return array{generated: int}
     *
     * @throws Exception
     */
    public function generateApples(): array
    {
        $count = rand(1, 10);
        $generated = 0;

        for ($i = 0; $i < $count; ++$i) {
            AppleFactory::createAndSave();
            $generated++;
        }

        return ['generated' => $generated];
    }

    /**
     * Уронить яблоко на землю.
     *
     * @return array{success: bool, error?: string}
     */
    public function fallApple(AppleModel $apple): array
    {
        try {
            AppleService::fallToGround($apple);

            return ['success' => true];
        } catch (RuntimeException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Съесть процент от яблока.
     *
     * @return array{success: bool, error?: string}
     */
    public function eatApple(AppleModel $apple, int $percent): array
    {
        try {
            AppleService::eat($apple, $percent);

            return ['success' => true];
        } catch (RuntimeException|InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Удалить яблоко.
     *
     * @return array{success: bool, error?: string}
     */
    public function deleteApple(AppleModel $apple): array
    {
        try {
            $apple->delete();

            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

}
