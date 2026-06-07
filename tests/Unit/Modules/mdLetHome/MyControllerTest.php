<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Lt\Modules\mdLetHome\Controllers\MyController;

final class MyControllerTest extends TestCase
{
    public function testAddReturnsCorrectSum(): void
    {
        $controller = new MyController();

        $this->assertSame(7, $controller->add(2, 5));
    }
}