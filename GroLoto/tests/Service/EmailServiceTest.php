<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;

class EmailServiceTest extends TestCase
{
    public function testEmailValidation(): void
    {
        $emails = [
            'valid@example.com' => true,
            'also.valid@example.co.uk' => true,
            'invalid.email@' => false,
            'plainaddress' => false,
            'another@example.co' => true,
        ];

        foreach ($emails as $email => $shouldBeValid) {
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $this->assertEquals(
                $shouldBeValid,
                $isValid,
                "Email '$email' validation failed"
            );
        }
    }

    public function testEmailFormatting(): void
    {
        $email = 'Test@Example.COM';
        $formatted = strtolower(trim($email));

        $this->assertEquals('test@example.com', $formatted);
    }

    public function testEmailDomainExtraction(): void
    {
        $email = 'user@example.com';
        $domain = substr(strrchr($email, "@"), 1);

        $this->assertEquals('example.com', $domain);
    }
}
