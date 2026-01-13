<?php

namespace App\Tests\Form;

use App\Form\PlageHoraireType;
use PHPUnit\Framework\TestCase;

class PlageHoraireTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(PlageHoraireType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(PlageHoraireType::class, 'buildForm'));
    }
}
