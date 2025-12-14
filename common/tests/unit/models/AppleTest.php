<?php

declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\enums\AppleColor;
use common\enums\AppleStatus;
use common\factories\AppleFactory;
use common\models\Apple;
use common\models\AppleModel;
use common\tests\UnitTester;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * Apple model test.
 */
class AppleTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        parent::_before();

        // Clean up any existing apples
        AppleModel::deleteAll();
    }

    protected function _after(): void
    {
        parent::_after();
        // Clean up after tests
        AppleModel::deleteAll();
    }

    public function testCreateAppleWithColor(): void
    {
        $apple = AppleFactory::create('green');

        verify($apple->color)->equals('green');
        verify($apple->status)->equals(AppleStatus::ON_TREE);
        verify($apple->size)->equals(1);
        verify($apple->created_at)->null();
        verify($apple->fell_at)->null();
    }

    public function testCreateAppleWithRandomColor(): void
    {
        $apple = AppleFactory::create();

        $this->assertContains($apple->color, AppleColor::getAll());
        verify($apple->status)->equals(AppleStatus::ON_TREE);
        verify($apple->size)->equals(1.00);
    }

    public function testSaveApple(): void
    {
        $apple = AppleFactory::create('red');
        $result = $apple->save();

        verify($result)->true();
        verify($apple->id)->notNull();
    }

    public function testFallToGround(): void
    {
        $apple = AppleFactory::create('yellow');
        $apple->save();

        $result = $apple->fallToGround();

        verify($result)->true();
        verify($apple->status)->equals(AppleStatus::FELL);
        verify($apple->fell_at)->greaterThan(0);
    }

    public function testFallToGroundWhenAlreadyFell(): void
    {
        $apple = AppleFactory::create('green');
        $apple->save();
        $apple->fallToGround();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Яблоко не на дереве');

        $apple->fallToGround();
    }

    public function testEatAppleOnTree(): void
    {
        $apple = AppleFactory::create('red');
        $apple->save();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нельзя съесть яблоко, которое висит на дереве');

        $apple->eat(50);
    }

    public function testEatAppleSuccess(): void
    {
        $apple = AppleFactory::create('green');
        $apple->save();
        $apple->fallToGround();

        $result = $apple->eat(25);

        verify($result)->true();
        verify($apple->size)->equals(0.75);
    }

    public function testEatAppleMultipleTimes(): void
    {
        $apple = AppleFactory::create('yellow');
        $apple->save();
        $apple->fallToGround();

        $apple->eat(30);
        $this->assertEquals(0.70, round($apple->size, 2));

        $apple->eat(20);
        $this->assertEquals(0.50, round($apple->size, 2));
    }

    public function testEatAppleFullyDeletes(): void
    {
        $apple = AppleFactory::create('red');
        $apple->save();
        $apple->fallToGround();
        $id = $apple->id;

        $result = $apple->eat(100);

        verify($result)->true();
        verify(AppleModel::findOne($id))->null();
    }

    public function testEatAppleInvalidPercent(): void
    {
        $apple = AppleFactory::create('green');
        $apple->save();
        $apple->fallToGround();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Процент должен быть от 1 до 100');

        $apple->eat(0);
    }

    public function testEatAppleInvalidPercentNegative(): void
    {
        $apple = AppleFactory::create('green');
        $apple->save();
        $apple->fallToGround();

        $this->expectException(InvalidArgumentException::class);

        $apple->eat(-10);
    }

    public function testEatAppleInvalidPercentTooHigh(): void
    {
        $apple = AppleFactory::create('green');
        $apple->save();
        $apple->fallToGround();

        $this->expectException(InvalidArgumentException::class);

        $apple->eat(101);
    }

    public function testAppleBecomesRottenAfter5Hours(): void
    {
        $apple = AppleFactory::create('yellow');
        $apple->save();
        $apple->fallToGround();

        // Simulate 5 hours + 1 second passed
        $apple->fell_at = (new DateTime())->modify('- 5 hours')->modify('- 1 second')->format('Y-m-d H:i:s');
        $apple->save(false);

        $apple->updateRottenStatus();

        verify($apple->status)->equals(AppleStatus::ROTTEN);
    }

    public function testCannotEatRottenApple(): void
    {
        $apple = AppleFactory::create('red');
        $apple->save();
        $apple->fallToGround();

        // Make it rotten
        $apple->fell_at = (new DateTime())->modify('- 5 hours')->modify('- 1 second')->format('Y-m-d H:i:s');
        $apple->save(false);
        $apple->updateRottenStatus();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нельзя съесть гнилое яблоко');

        $apple->eat(10);
    }

    public function testAppleDoesNotRotOnTree(): void
    {
        $apple = AppleFactory::create('green');
        $apple->created_at = (new DateTime())->modify('- 1000 hours')->format('Y-m-d H:i:s');
        $apple->save();

        $apple->updateRottenStatus();

        verify($apple->status)->equals(AppleStatus::ON_TREE);
        verify($apple->isRotten)->false();
    }

    public function testIsOnTreeGetter(): void
    {
        $apple = AppleFactory::create('red');
        verify($apple->isOnTree)->true();

        $apple->save();
        $apple->fallToGround();
        verify($apple->isOnTree)->false();
    }

    public function testIsFellGetter(): void
    {
        $apple = AppleFactory::create('green');
        verify($apple->isFell)->false();

        $apple->save();
        $apple->fallToGround();
        verify($apple->isFell)->true();
    }

    public function testIsRottenGetter(): void
    {
        $apple = AppleFactory::create('yellow');
        $apple->save();
        $apple->fallToGround();

        verify($apple->isRotten)->false();

        // Make it rotten
        $apple->fell_at = (new DateTime())->modify('- 5 hours')->modify('- 1 second')->format('Y-m-d H:i:s');
        $apple->save(false);
        $apple->updateRottenStatus();

        verify($apple->isRotten)->true();
    }

    public function testIsFullyEatenGetter(): void
    {
        $apple = AppleFactory::create('red');
        $apple->save();
        $apple->fallToGround();

        verify($apple->isFullyEaten)->false();

        $apple->size = 0.0;
        verify($apple->isFullyEaten)->true();
    }

    public function testGetStatusLabel(): void
    {
        $apple = AppleFactory::create('green');
        verify(AppleStatus::getLabel($apple->status))->equals('На дереве');

        $apple->status = AppleStatus::FELL;
        verify(AppleStatus::getLabel($apple->status))->equals('Упало');

        $apple->status = AppleStatus::ROTTEN;
        verify(AppleStatus::getLabel($apple->status))->equals('Гнилое');
    }

    public function testGetSizePercent(): void
    {
        $apple = AppleFactory::create('yellow');
        verify($apple->sizePercent)->equals(100);

        $apple->size = 0.75;
        verify($apple->sizePercent)->equals(75);

        $apple->size = 0.50;
        verify($apple->sizePercent)->equals(50);

        $apple->size = 0.0;
        verify($apple->sizePercent)->equals(0);
    }

    public function testValidation(): void
    {
        $apple = new AppleModel();
        verify($apple->validate())->false();

        $apple->color = 'red';
        $apple->created_at = time();
        $apple->status = AppleStatus::ON_TREE;
        $apple->size = 1.0;

        verify($apple->validate())->true();
    }

    public function testValidationInvalidColor(): void
    {
        $apple = new AppleModel();
        $apple->color = 'blue'; // Invalid color
        $apple->created_at = time();
        $apple->status = AppleStatus::ON_TREE;
        $apple->size = 1.0;

        verify($apple->validate())->false();
        verify($apple->errors)->arrayHasKey('color');
    }

    public function testValidationInvalidStatus(): void
    {
        $apple = new AppleModel();
        $apple->color = 'red';
        $apple->created_at = time();
        $apple->status = 'invalid_status';
        $apple->size = 1.0;

        verify($apple->validate())->false();
        verify($apple->errors)->arrayHasKey('status');
    }

    public function testValidationInvalidSize(): void
    {
        $apple = new AppleModel();
        $apple->color = 'red';
        $apple->created_at = time();
        $apple->status = AppleStatus::ON_TREE;
        $apple->size = 1.5; // Too big

        verify($apple->validate())->false();
        verify($apple->errors)->arrayHasKey('size');
    }

    public function testConstructor(): void
    {
        $apple = new Apple('green');
        verify($apple->color)->equals('green');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Нельзя съесть яблоко, которое висит на дереве');
        $apple->eat(50); // Бросить исключение - Съесть нельзя, яблоко на дереве

        verify($apple->size);

        $apple->fallToGround(); // упасть на землю

        $apple->eat(25); // откусить четверть яблока

        verify($apple->size)->equals(0.75);
    }
}
