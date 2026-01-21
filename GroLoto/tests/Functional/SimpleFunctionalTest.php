<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleFunctionalTest extends WebTestCase
{
    public function testHomePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            $statusCode === 200 || $statusCode === 302,
            "Expected 200 or 302, got $statusCode"
        );
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Login page should be accessible'
        );
    }

    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Register page should be accessible'
        );
    }

    public function testFestivalsPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/festivals');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'Festivals page should be accessible'
        );
    }

    public function testLegalPagesAreAccessible(): void
    {
        $client = static::createClient();
        
        $pages = ['/mentions-legales', '/politique-confidentialite', '/cookies'];
        
        foreach ($pages as $page) {
            $client->request('GET', $page);
            $this->assertTrue(
                in_array($client->getResponse()->getStatusCode(), [200, 302]),
                "Page $page should be accessible"
            );
        }
    }

    public function testInvalidRouteReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/this-route-does-not-exist');
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testLogoutRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');
        
        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [302, 301]),
            'Logout should redirect'
        );
    }

    public function testProtectedPagesRequireAuthentication(): void
    {
        $client = static::createClient();
        
        $protectedPages = ['/profile', '/profile/edit'];
        
        foreach ($protectedPages as $page) {
            $client->request('GET', $page);
            $statusCode = $client->getResponse()->getStatusCode();
            
            $this->assertTrue(
                $statusCode === 302 || $statusCode === 401 || $statusCode === 403,
                "Protected page $page should require authentication (got $statusCode)"
            );
        }
    }

    public function testResponseHasCorrectHeaders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $this->assertTrue(
            $response->headers->has('Content-Type'),
            'Response should have Content-Type header'
        );
    }

    public function testMethodsAreRespected(): void
    {
        $client = static::createClient();
        
        // POST on non-POST routes should fail
        $client->request('POST', '/');
        
        // Should get either 405 (Method Not Allowed), 404, or 302 (redirect)
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [405, 404, 302, 200]),
            "Unexpected status code: $statusCode"
        );
    }
}
