<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class sirenApiTest extends WebTestCase 
{
    public function testSirenApiPage()
    {
        $client = static::createClient();

        $client->request('GET', '/sirene/1');
        $this->assertResponseHeaderSame('Content-type', 'application/json');
        // $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseFormatSame('json');
    }

    public function testSirenCsvPage()
    {
        $client = static::createClient();

        $client->request('GET', '/sireneCsv/1');
        $this->assertResponseHeaderSame('Content-type', 'application/json');
        // $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseFormatSame('json');
    }
}

