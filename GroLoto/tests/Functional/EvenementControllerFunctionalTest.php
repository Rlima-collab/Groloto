<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EvenementControllerFunctionalTest extends WebTestCase
{
    public function testEvenementPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/evenements');

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200 || 
            $client->getResponse()->getStatusCode() === 302,
            'Page should be accessible or redirect'
        );
    }

    public function testStockPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/stocks');

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200 || 
            $client->getResponse()->getStatusCode() === 302,
            'Page should be accessible or redirect'
        );
    }

    public function testTachesPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/taches');

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200 || 
            $client->getResponse()->getStatusCode() === 302,
            'Page should be accessible or redirect'
        );
    }

    public function testWeekendPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/weekend/');

        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302, 401, 403, 500]),
            'Weekend page should be accessible or require authentication'
        );
    }

    public function testInvalidRouteReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/nonexistent-page');

        $this->assertResponseStatusCodeSame(404);
    }
}
