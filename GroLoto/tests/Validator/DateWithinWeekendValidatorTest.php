<?php

use PHPUnit\Framework\TestCase;
use App\Validator\Constraints\DateWithinWeekendValidator;
use App\Validator\Constraints\DateWithinWeekend;
use App\Entity\Weekend;

class DateWithinWeekendValidatorTest extends TestCase
{
    public function testNoViolationWhenDateInsideWeekend()
    {
        $validator = new DateWithinWeekendValidator();

        $weekend = new Weekend();
        $weekend->setDateDebut(new \DateTime('2025-03-07'));
        $weekend->setDateFin(new \DateTime('2025-03-09'));

        $object = new class($weekend) {
            private $weekend;
            public function __construct($w) { $this->weekend = $w; }
            public function getWeekend() { return $this->weekend; }
        };

        $context = $this->createMock(\Symfony\Component\Validator\Context\ExecutionContextInterface::class);
        $context->method('getObject')->willReturn($object);
        $context->expects($this->never())->method('buildViolation');

        $validator->initialize($context);

        $validator->validate(new \DateTime('2025-03-08'), new DateWithinWeekend());
    }

    public function testViolationWhenDateOutsideWeekend()
    {
        $validator = new DateWithinWeekendValidator();

        $weekend = new Weekend();
        $weekend->setDateDebut(new \DateTime('2025-03-07'));
        $weekend->setDateFin(new \DateTime('2025-03-09'));

        $object = new class($weekend) {
            private $weekend;
            public function __construct($w) { $this->weekend = $w; }
            public function getWeekend() { return $this->weekend; }
        };

        $builder = $this->createMock(\Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface::class);
        $builder->expects($this->once())->method('setParameter')->willReturnSelf();
        $builder->expects($this->once())->method('addViolation');

        $context = $this->createMock(\Symfony\Component\Validator\Context\ExecutionContextInterface::class);
        $context->method('getObject')->willReturn($object);
        $context->expects($this->once())->method('buildViolation')->willReturn($builder);

        $validator->initialize($context);

        $validator->validate(new \DateTime('2025-03-10'), new DateWithinWeekend());
    }
}
