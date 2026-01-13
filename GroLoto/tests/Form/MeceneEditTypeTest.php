<?php

namespace App\Tests\Form;

use App\Form\MeceneEditType;
use PHPUnit\Framework\TestCase;

class MeceneEditTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(MeceneEditType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(MeceneEditType::class, 'buildForm'));
    }
}
