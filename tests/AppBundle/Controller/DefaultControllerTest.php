<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultControllerTest
 * @author ereshkidal
 */
class DefaultControllerTest extends WebTestCase
{
    public function testHomepageIsUp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
