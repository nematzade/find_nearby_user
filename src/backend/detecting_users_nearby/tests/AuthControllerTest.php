<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }

    public function testBadToken()
    {
        $client = static::createClient();
        $client->request('GET', '/api/locations',[
            'CONTENT_TYPE' => 'application/json',
            'headers' => [
                'Authorization' => 'Bearer Wrong'
            ]
        ]);
        $this->assertEquals(401,$client->getResponse()->getStatusCode());
    }
}
