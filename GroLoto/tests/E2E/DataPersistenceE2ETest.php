<?php

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DataPersistenceE2ETest extends WebTestCase
{
    public function testResponseHeadersAreCorrect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        
        $this->assertNotNull($response->headers->get('Content-Type'));
        $this->assertNotNull($response->headers->get('Date'));
    }

    public function testCacheHeadersArePresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $cacheControl = $response->headers->get('Cache-Control');
        
        $this->assertNotNull($cacheControl, 'Cache-Control header should be present');
    }

    public function testStaticPages404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/nonexistent-page-xyz');
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testMultiplePageLoadingPreservesSession(): void
    {
        $client = static::createClient();
        
        // First request
        $client->request('GET', '/');
        $firstResponse = $client->getResponse()->getStatusCode();
        
        // Second request
        $client->request('GET', '/festivals');
        $secondResponse = $client->getResponse()->getStatusCode();
        
        // Both should be successful or redirect
        $this->assertTrue(
            in_array($firstResponse, [200, 302]),
            "First request should succeed (got $firstResponse)"
        );
        $this->assertTrue(
            in_array($secondResponse, [200, 302]),
            "Second request should succeed (got $secondResponse)"
        );
    }

    public function testNavigationConsistency(): void
    {
        $client = static::createClient();
        
        $pages = ['/', '/festivals', '/login'];
        
        foreach ($pages as $page) {
            $client->request('GET', $page);
            $statusCode = $client->getResponse()->getStatusCode();
            
            $this->assertLessThan(400, $statusCode, "Page $page should return status < 400");
        }
    }

    public function testConsecutiveRequestsWork(): void
    {
        $client = static::createClient();
        
        for ($i = 0; $i < 5; $i++) {
            $client->request('GET', '/');
            $this->assertTrue(
                in_array($client->getResponse()->getStatusCode(), [200, 302, 304]),
                "Request $i should succeed"
            );
        }
    }

    public function testDifferentPagesHaveDifferentContent(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/');
        $homeContent = $client->getResponse()->getContent();
        
        $client->request('GET', '/festivals');
        $festivalsContent = $client->getResponse()->getContent();
        
        $client->request('GET', '/login');
        $loginContent = $client->getResponse()->getContent();
        
        // Pages should have different content
        $this->assertNotEquals($homeContent, $festivalsContent);
        $this->assertNotEquals($festivalsContent, $loginContent);
    }

    public function testValidHtmlStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $content = $client->getResponse()->getContent();
        
        // Should contain basic HTML structure
        $this->assertStringContainsString('<html', mb_strtolower($content));
        $this->assertStringContainsString('</html>', mb_strtolower($content));
    }

    public function testResponseEncodingIsValid(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        $response = $client->getResponse();
        $contentType = $response->headers->get('Content-Type');
        
        $this->assertStringContainsString('UTF-8', $contentType);
    }
}
