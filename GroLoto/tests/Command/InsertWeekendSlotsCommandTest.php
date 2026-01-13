<?php

namespace App\Tests\Command;

use App\Command\InsertWeekendSlotsCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class InsertWeekendSlotsCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(InsertWeekendSlotsCommand::class));
    }

    public function testCommandExtendsBaseCommand(): void
    {
        $reflection = new \ReflectionClass(InsertWeekendSlotsCommand::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    public function testCommandHasExecuteMethod(): void
    {
        $this->assertTrue(method_exists(InsertWeekendSlotsCommand::class, 'execute'));
    }
}
