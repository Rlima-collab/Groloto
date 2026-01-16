<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateWithinWeekendValidator extends ConstraintValidator
{
    /**
     * $value is expected to be a \DateTimeInterface or null
     * The constraint should be applied on the date field (date_debut)
     * The validator will check for the presence of a sibling 'weekend' on the root object
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $object = $this->context->getObject();

        if (!is_object($object)) {
            return;
        }

        // Attempt to get weekend from the form root data or the object
        $weekend = null;

        // If the validated object has a getWeekend() method, use it
        if (method_exists($object, 'getWeekend')) {
            $weekend = $object->getWeekend();
        }

        // Also support forms where weekend is a field on the parent data
        if (null === $weekend && method_exists($object, 'getData')) {
            try {
                $root = $this->context->getRoot();
                if (is_object($root) && method_exists($root, 'getData')) {
                    $data = $root->getData();
                    if (is_object($data) && method_exists($data, 'getWeekend')) {
                        $weekend = $data->getWeekend();
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (!$weekend) {
            // nothing to validate against
            return;
        }

        $dateVendredi = null;
        $dateDimanche = null;

        if (method_exists($weekend, 'getDateVendredi')) {
            $dateVendredi = $weekend->getDateVendredi();
        }
        if (method_exists($weekend, 'getDateDimanche')) {
            $dateDimanche = $weekend->getDateDimanche();
        }

        if (!$dateVendredi || !$dateDimanche) {
            return;
        }

        // Normalize times: compare dates only
        $valueDate = \DateTime::createFromFormat('Y-m-d', $value->format('Y-m-d'));
        $startDate = \DateTime::createFromFormat('Y-m-d', $dateVendredi->format('Y-m-d'));
        $endDate = \DateTime::createFromFormat('Y-m-d', $dateDimanche->format('Y-m-d'));

        if ($valueDate < $startDate || $valueDate > $endDate) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ date }}', $value->format('d/m/Y'))
                ->addViolation();
        }
    }
}
