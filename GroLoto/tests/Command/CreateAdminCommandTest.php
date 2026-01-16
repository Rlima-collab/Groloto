<?php

namespace App\Tests\Command;

use App\Command\CreateAdminCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CreateAdminCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(CreateAdminCommand::class));
    }

    public function testCommandExtendsBaseCommand(): void
    {
        $reflection = new \ReflectionClass(CreateAdminCommand::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    public function testCommandHasExecuteMethod(): void
    {
        $this->assertTrue(method_exists(CreateAdminCommand::class, 'execute'));
    }
}
