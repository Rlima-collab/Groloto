<?php

namespace App\Tests\Form;

use App\Form\InscriptionMeceneType;
use PHPUnit\Framework\TestCase;

class InscriptionMeceneTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(InscriptionMeceneType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(InscriptionMeceneType::class, 'buildForm'));
    }
}
