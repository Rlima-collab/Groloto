<?php

namespace App\Tests\Service;

use App\Service\BatchTaskAssignmentService;
use PHPUnit\Framework\TestCase;

class BatchTaskAssignmentServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(BatchTaskAssignmentService::class));
    }
}
