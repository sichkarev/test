<?php

declare(strict_types=1);

use common\enums\AppleColor;
use common\enums\AppleStatus;
use common\models\AppleModel;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var AppleModel[] $apples */

$this->title = '🍏 Управление яблоками';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('.apple-icon {
    font-size: 100px;
}');
?>
<div class="apple-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo Html::encode($this->title); ?></h1>
        <?php echo Html::a('Сгенерировать яблоки', ['generate'], [
            'class' => 'btn btn-success',
            'data' => [
                'method' => 'post',
            ],
        ]); ?>
    </div>

    <?php if (empty($apples)) { ?>
        <div class="alert alert-info">
            Яблок не найдено. Нажмите "Сгенерировать яблоки" чтобы создать.
        </div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($apples as $apple) { ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 <?php echo $apple->isRotten ? 'border-danger' : ($apple->isFell ? 'border-warning' : 'border-success'); ?>">
                        <div class="card-header <?php echo $apple->isRotten ? 'bg-danger text-white' : ($apple->isFell ? 'bg-warning' : 'bg-success text-white'); ?>">
                            <span>
                                <i class="bi bi-apple"></i>
                                Яблоко #<?php echo $apple->id; ?> (<span><?php echo AppleStatus::getLabel($apple->status); ?>)</span>
                            </span>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-3">
                                <dt class="col-sm-4">
                                    <span class="h1 apple-icon">
                                        <?php echo Html::encode(AppleColor::getEmoji($apple->color)); ?>
                                    </span>
                                </dt>
                                <dd class="col-sm-8">
                                    Размер:
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?php echo $apple->isRotten ? 'bg-danger' : 'bg-success'; ?>"
                                             role="progressbar"
                                             style="width: <?php echo $apple->getSizePercent(); ?>%"
                                             aria-valuenow="<?php echo $apple->getSizePercent(); ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?php echo $apple->getSizePercent(); ?>%
                                        </div>
                                    </div>
                                    Создано: <?php echo Yii::$app->formatter->asRelativeTime($apple->created_at); ?>
                                    <br />
                                    Упало: <?php echo Yii::$app->formatter->asRelativeTime($apple->fell_at); ?>

                                    <?php
                                        $text = !$apple->isRotten ? 'пропадет' : 'пропала';
                $timeUntilRotten = Yii::$app->formatter->asRelativeTime($apple->getTimeUntilRotten());
                ?>
                                    <br />
                                    Спелость <?php echo $text; ?>: <?php echo $timeUntilRotten; ?>
                                </dd>
                            </dl>

                            <div class="d-grid gap-2">
                                <?php if ($apple->isOnTree) { ?>
                                    <?php echo Html::a('Уронить на землю', ['fall', 'id' => $apple->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'method' => 'post',
                    ],
                ]); ?>
                                <?php } ?>

                                <?php if ($apple->isFell && !$apple->isRotten) { ?>
                                    <form action="<?php echo Url::to(['eat', 'id' => $apple->id]); ?>" method="post" class="eat-form">
                                        <input type="hidden" name="<?php echo Yii::$app->request->csrfParam; ?>" value="<?php echo Yii::$app->request->csrfToken; ?>">
                                        <div class="input-group mb-2">
                                            <input type="number"
                                                   name="percent"
                                                   class="form-control"
                                                   placeholder="%"
                                                   min="1"
                                                   max="100"
                                                   value="25"
                                                   required>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-egg-fried"></i> Съесть %
                                            </button>
                                        </div>
                                    </form>
                                <?php } ?>

                                <?php echo Html::a('Удалить', ['delete', 'id' => $apple->id], [
                'class' => 'btn btn-danger btn-sm',
                'data' => [
                    'method' => 'post',
                    'confirm' => 'Вы уверены, что хотите удалить это яблоко?',
                ],
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

</div>

<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
