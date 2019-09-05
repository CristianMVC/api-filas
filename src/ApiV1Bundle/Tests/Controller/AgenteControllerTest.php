<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class AgenteControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class AgenteControllerTest extends ControllerTestCase
{
    /** @var integer $agenteId */

    private $agenteId;

    /**
     * testListAction
     *
     * Test automatizado que permite probar el obtener los agentes disponibles
     * Endpoint: /agentes
     */
    public function testListAction()
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/agentes');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test crear agente
     * Test automatizado que permite probar la creación de un agente
     * Params: Array con los datos del usuario que tendrá el rol de Agente.
     * Endpoint: /usuarios
     * @return integer
     */
    public function testCrearAgente()
    {
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Agente',
            'apellido' => 'McAgenteFace',
            'username' => uniqid('agente-') . '@example.com',
            'rol' => 5,
            'puntoAtencion' => 1,
            'ventanillas' => [5, 6]
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->agenteId = $data['additional']['id'];
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $this->agenteId;
    }


     /**
      * Test Get Ventanilla Action
      * Test automatizado que permite listar las ventanillas pertenecientes a un agente
      * Endpoint: /agentes/{id}/ventanillas
      * Annotation depends permite generar una dependencia explicita entre métodos,
      * para el caso particular entre testCrearAgente y testGetVentanillasAction
     * @depends testCrearAgente
     */
    public function testObtenerVentanillas($id)
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', "/api/v1.0/agentes/{$id}/ventanillas");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $id;
    }

    /** Test asignar ventanilla a agente
     * @param $id
     * @depends testObtenerVentanillas
     */
    public function testAsignarVentanillaAction($id)
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('POST', "/api/v1.0/agentes/{$id}/ventanilla/5");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $id;
    }

    /** Test desasignar ventanilla a agente
     * @param $id
     * @depends testAsignarVentanillaAction
     */
    public function testDesasignarVentanillaAction($id)
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('POST', "/api/v1.0/agentes/{$id}/desasignar");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test Get Eliminar Agente
     * Test automatizado que permite eliminar un agente
     * Param: $id - Identificador único de usuario Agente
     * Endpoint: /usuarios/{id}
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testCrearAgente y testEliminarAgente
     * @depends testCrearAgente
     */
    public function testEliminarAgente($id)
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('DELETE', "/api/v1.0/usuarios/{$id}");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**Test para  obtener la ventanilla actual de un agente
     * testGetVentanillaActualAction
     */
    public function testGetVentanillaActualAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/ventanillas/actual', [], [], $this->getTokenPruebas(4));
        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }

}