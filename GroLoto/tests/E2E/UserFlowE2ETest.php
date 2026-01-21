<?php

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserFlowE2ETest extends WebTestCase
{
    public function testUserCanVisitHomepage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Homepage should be accessible'
        );
    }

    public function testUserCanNavigateToLoginPage(): void
    {
        $client = static::createClient();
        
        // Visit home
        $client->request('GET', '/');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Homepage should load'
        );
        
        // Navigate to login
        $client->request('GET', '/login');
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Login page should be accessible'
        );
    }

    public function testUserCanAccessPublicFestivals(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/festivals');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Festivals page should be accessible'
        );
    }

    public function testUserCanAccessRegisterPage(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/register');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Register page should be accessible'
        );
    }

    public function testUserCanViewLegalPages(): void
    {
        $client = static::createClient();
        
        $pages = [
            '/mentions-legales',
            '/politique-confidentialite',
            '/cookies'
        ];
        
        foreach ($pages as $page) {
            $client->request('GET', $page);
            $this->assertTrue(
                in_array($client->getResponse()->getStatusCode(), [200, 302]),
                "Legal page $page should be accessible"
            );
        }
    }

    public function testPageResponseTimeIsReasonable(): void
    {
        $client = static::createClient();
        
        $start = microtime(true);
        $client->request('GET', '/');
        $elapsed = microtime(true) - $start;
        
        // Response should be under 5 seconds
        $this->assertLessThan(5, $elapsed, 'Page load should be under 5 seconds');
    }

    public function testResponseHasValidContentType(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $contentType = $response->headers->get('Content-Type');
        
        $this->assertNotNull($contentType, 'Response should have Content-Type header');
        $this->assertStringContainsString('text/html', $contentType);
    }

    public function testNavigationBetweenPages(): void
    {
        $client = static::createClient();
        
        $pages = ['/', '/login', '/register', '/festivals'];
        
        foreach ($pages as $page) {
            $client->request('GET', $page);
            $statusCode = $client->getResponse()->getStatusCode();
            
            $this->assertTrue(
                in_array($statusCode, [200, 302]),
                "Should be able to navigate to $page (got $statusCode)"
            );
        }
    }

    public function testUserCanAccessMultiplePages(): void
    {
        $client = static::createClient();
        
        // Simulate user journey
        $client->request('GET', '/');
        $this->assertLessThan(400, $client->getResponse()->getStatusCode());
        
        $client->request('GET', '/festivals');
        $this->assertLessThan(400, $client->getResponse()->getStatusCode());
        
        $client->request('GET', '/login');
        $this->assertLessThan(400, $client->getResponse()->getStatusCode());
    }

    public function testInvalidPageReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/page-that-does-not-exist-xyz-123');
        
        $this->assertResponseStatusCodeSame(404);
    }
}
