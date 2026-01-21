<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function testPhoneNumberValidation(): void
    {
        $validNumbers = [
            '0123456789',
            '0 123 456 789',
            '01 23 45 67 89',
        ];

        foreach ($validNumbers as $number) {
            $cleaned = preg_replace('/[^0-9]/', '', $number);
            $this->assertEquals(10, strlen($cleaned), "Phone number should have 10 digits");
        }
    }

    public function testPasswordStrength(): void
    {
        $passwords = [
            'weak' => false,
            '123456' => false,
            'Password123!' => true,
            'P@ssw0rd' => true,
            'abc' => false,
        ];

        foreach ($passwords as $password => $shouldBeStrong) {
            $isStrong = $this->isStrongPassword($password);
            $this->assertEquals(
                $shouldBeStrong,
                $isStrong,
                "Password '$password' strength check failed"
            );
        }
    }

    public function testDateValidation(): void
    {
        $validDates = [
            '2026-01-21' => true,
            '2025-12-31' => true,
            '2025-13-01' => false,
            '2025-02-30' => false,
        ];

        foreach ($validDates as $date => $shouldBeValid) {
            $isValid = $this->isValidDate($date);
            $this->assertEquals(
                $shouldBeValid,
                $isValid,
                "Date '$date' validation failed"
            );
        }
    }

    public function testURLValidation(): void
    {
        $validUrls = [
            'https://example.com' => true,
            'http://example.com' => true,
            'ftp://example.com' => true,
            'not-a-url' => false,
            'example.com' => false,
        ];

        foreach ($validUrls as $url => $shouldBeValid) {
            $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;
            $this->assertEquals(
                $shouldBeValid,
                $isValid,
                "URL '$url' validation failed"
            );
        }
    }

    private function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
