<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class VentanillasControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class VentanillasControllerTest extends ControllerTestCase
{
    /**
     * testGetVisualizarVentanillaAction
     * Test automatizado para comprobar el obtener los agentes disponibles asignados a ventanillas
     * Endpoint: /ventanillas
     */
    public function testGetVisualizarVentanillaAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/ventanillas');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostAction
     * Test automatizado para comprobar la creación de una nueva ventanilla
     * Endpoint: /agentes/{id_agente}/ventanilla/{id_ventanilla}
     * return int
     */
    public function testPostAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "puntoAtencion" => 1,
            "identificador" => "A3",
            "colas" => [1,3]
        ];
        $client->request('POST', '/api/v1.0/ventanillas',$params);
        $nuevaVentanilla = json_decode($client->getResponse()->getContent(), true);
        $ventanillaId   = $nuevaVentanilla['additional']['id'];
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $ventanillaId;
    }

    /**
     * testPutModificarVentanillaAction
     * Test automatizado para comprobar la modificación de una ventanilla previamente creada
     * Endpoint: /ventanilla/{ventanillaId}
     * @depends testPostAction
     */
    public function testPutModificarVentanillaAction($ventanillaId)
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "identificador" => "A3",
            "colas" => [3]
        ];
        $client->request('PUT', '/api/v1.0/ventanillas/'.$ventanillaId ,$params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * TestDeleteVentanillaAction
     * Test automatizado para comprobar el borrado de una ventanilla
     * @param $ventanillaId
     * Annotation depends permite generar una dependencia explicita entre métodos, pera el caso particular entre testPostAction y testDeleteVentanillaAction
     * @depends testPostAction
     */
    public function testDeleteVentanillaAction($ventanillaId)
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/ventanillas/'.$ventanillaId);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testGetObtenerVentanillaAction
     * Test automatizado que permite comprobar el obtener una determinada ventanilla
     * Endpoint: /ventnillas/{ventanillaId}
     */
    public function testGetObtenerVentanillaAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/ventanillas/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostVentanillaWithOutColasFieldAction
     * Test automatizado que permite comprobar el fallo de la creación de ventanillas ante la falta del parámetro "colas"
     * EndPoint: /ventanillas
     */
    public function testPostVentanillaWithOutColasFieldAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        // Ventanilla sin colas
        $params = [
            "puntoAtencion" => 1,
            "identificador" => "A3",
            "colas" => []
        ];
        $client->request('POST', '/api/v1.0/ventanillas', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPutVentanillaNotExistAction
     * Test automatizado que permite comprobar el fallo de la modificación de una ventanilla inexistente
     * EndPoint: /ventanillas/{ventanillaId}
     */

    public function testPutVentanillaNotExistAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "identificador" => "A3",
            "colas" => [1,13]
        ];
        $client->request('PUT', '/api/v1.0/ventanillas/0', $params);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

}