<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Options;
use FOS\RestBundle\Controller\Annotations\Route;
use ApiV1Bundle\Entity\Response\Respuesta;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class RestTestController
 * @package ApiV1Bundle\Controller
 *
 * Test routes class
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
class RestTestController extends ApiController
{

    /**
     * Test GET action
     * @ApiDoc(section="Test")
     * @return Respuesta
     * @Get("/test")
     */
    public function testGetAction()
    {
        return $this->respuestaData(null, [
            'method' => 'GET',
            'lorem' => 'ipsum',
            'dolor' => 'sit amet'
        ]);
    }

    /**
     * Test POST action
     * @ApiDoc(section="Test")
     * @return Respuesta
     * @Post("/test")
     */
    public function testPostAction()
    {
        return $this->respuestaData(null, [
            'method' => 'POST',
            'lorem' => 'ipsum',
            'dolor' => 'sit amet'
        ]);
    }

    /**
     * Test PUT action
     * @ApiDoc(section="Test")
     * @return Respuesta
     * @Put("/test")
     */
    public function testPutAction()
    {
        return $this->respuestaData(null, [
            'method' => 'PUT',
            'lorem' => 'ipsum',
            'dolor' => 'sit amet'
        ]);
    }

    /**
     * Test DELETE action
     * @ApiDoc(section="Test")
     * @return Respuesta
     * @Delete("/test")
     */
    public function testDeleteAction()
    {
        return $this->respuestaData(null, [
            'method' => 'DELETE',
            'lorem' => 'ipsum',
            'dolor' => 'sit amet'
        ]);
    }

    /**
     * Test OPTIONS action
     * @ApiDoc(section="Test")
     * @return Respuesta
     * @Options("/test")
     */
    public function testOptionsAction()
    {
        return $this->respuestaData(null, [
            'method' => 'OPTIONS',
            'lorem' => 'ipsum',
            'dolor' => 'sit amet'
        ]);
    }

    /**
     * Test 400 response
     * @ApiDoc(section="Test")
     * @return \ApiV1Bundle\Entity\Response\RespuestaConEstado
     * @Get("/test/400")
     */
    public function test400Action()
    {
        return $this->respuestaBadRequest('Bad request');
    }

    /**
     * Test 403 response
     * @ApiDoc(section="Test")
     * @return \ApiV1Bundle\Entity\Response\RespuestaConEstado
     * @Get("/test/403")
     */
    public function test403Action()
    {
        return $this->respuestaForbiddenRequest('Forbidden');
    }

    /**
     * Test 404 response
     * @ApiDoc(section="Test")
     * @return \ApiV1Bundle\Entity\Response\RespuestaConEstado
     * @Get("/test/404")
     */
    public function test404Action()
    {
        return $this->respuestaNotFound('Not found');
    }
}
