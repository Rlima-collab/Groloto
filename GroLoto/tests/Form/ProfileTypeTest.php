<?php

namespace App\Tests\Form;

use App\Form\ProfileType;
use PHPUnit\Framework\TestCase;

class ProfileTypeTest extends TestCase
{
    public function testFormTypeExists(): void
    {
        $this->assertTrue(class_exists(ProfileType::class));
    }

    public function testFormTypeHasBuildFormMethod(): void
    {
        $this->assertTrue(method_exists(ProfileType::class, 'buildForm'));
    }

    public function testFormTypeHasConfigureOptionsMethod(): void
    {
        $this->assertTrue(method_exists(ProfileType::class, 'configureOptions'));
    }
}
