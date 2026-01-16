<?php

namespace App\Tests\Form;

use App\Form\TacheAffectationType;
use PHPUnit\Framework\TestCase;

class TacheAffectationTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(TacheAffectationType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(TacheAffectationType::class, 'buildForm'));
    }
}
