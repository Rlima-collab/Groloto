<?php

namespace App\Tests\Validator\Constraints;

use App\Entity\Evenement;
use App\Entity\Weekend;
use App\Validator\Constraints\DateWithinWeekend;
use App\Validator\Constraints\DateWithinWeekendValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DateWithinWeekendValidatorExtendedTest extends TestCase
{
    public function testValidatorWithNullEvenement(): void
    {
        $constraint = new DateWithinWeekend();
        $validator = new DateWithinWeekendValidator();
        
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())
            ->method('buildViolation');
        
        $validator->initialize($context);
        
        $evenement = new Evenement();
        $validator->validate($evenement, $constraint);
        
        $this->assertTrue(true);
    }

    public function testValidatorWithEvenementWithoutWeekend(): void
    {
        $constraint = new DateWithinWeekend();
        $validator = new DateWithinWeekendValidator();
        
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())
            ->method('buildViolation');
        
        $validator->initialize($context);
        
        $evenement = new Evenement();
        $evenement->setDateDebut(new \DateTime('2025-06-07'));
        $evenement->setDateFin(new \DateTime('2025-06-08'));
        
        $validator->validate($evenement, $constraint);
        
        $this->assertTrue(true);
    }

    public function testConstraintMessage(): void
    {
        $constraint = new DateWithinWeekend();
        $this->assertNotEmpty($constraint->message);
        $this->assertIsString($constraint->message);
    }
}
