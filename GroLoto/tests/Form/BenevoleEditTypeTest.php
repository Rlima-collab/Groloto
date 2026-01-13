<?php

namespace App\Tests\Form;

use App\Form\BenevoleEditType;
use PHPUnit\Framework\TestCase;

class BenevoleEditTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(BenevoleEditType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(BenevoleEditType::class, 'buildForm'));
    }
}
