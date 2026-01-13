<?php

namespace App\Tests\Form;

use App\Form\EvenementEmbeddedType;
use PHPUnit\Framework\TestCase;

class EvenementEmbeddedTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(EvenementEmbeddedType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(EvenementEmbeddedType::class, 'buildForm'));
    }
}
