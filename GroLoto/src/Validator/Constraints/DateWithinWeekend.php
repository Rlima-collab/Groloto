<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateWithinWeekend extends Constraint
{
	public string $message = 'La date "{{ date }}" doit être comprise dans le week-end sélectionné.';

	public function validatedBy(): string
	{
		return static::class.'Validator';
	}
}

