<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class DefaultControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class DefaultControllerTest extends ControllerTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}