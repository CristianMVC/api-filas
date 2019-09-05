<?php
namespace ApiV1Bundle\Tests\Controller;

use ApiV1Bundle\Mocks\SNTExternalServiceMock;

/**
 * Class PuntoAtencionControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class PuntoAtencionControllerTest extends ControllerTestCase
{
    /** @var integer $puntoAtencionId */
    protected $puntoAtencionId;

    /**
     * testGetListAction
     * Test automatizado que permite probar el obtener el listado de Puntos de Atención en SNC
     * Endpoint: /puntosatencion
     */
    public function testGetListAction()
    {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/puntoatencion');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostAction
     * Test automatizado que permite testear la creación de un Punto de Atención en SNC
     * $params array conteniendo datos básicos del PA para poder crearlo del lado de COLAS
     * Endpoint: integracion/puntosatencion/
     */

    public function testPostAction()
    {
        $id = random_int(1, 2147483647);
        $client = self::createClient();
        $client->followRedirects();

        $params = [
            'punto_atencion_id_SNT' => $id,
            'nombre' => 'PA de prueba'
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);

        $client->request('POST', '/api/v1.0/integracion/puntosatencion', $signedParams);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Ya existe
        $client->request('POST', '/api/v1.0/integracion/puntosatencion', $signedParams);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        return $params['punto_atencion_id_SNT'];
    }

    /**
     * Test automatizado para testear el modificar un punto de atención en SNC
     * @param $puntoAtencionId - Identificador unico del punto de atención
     * Endpoint: integracion/puntosatencion/{puntoAtencionId}
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostAction y testPutAction
     * @depends testPostAction
     */
    public function testPutAction($puntoAtencionId)
    {
        $client = self::createClient();
        $client->followRedirects();

        $params = [
            'punto_atencion_id_SNT' => $this->puntoAtencionId,
            'nombre' => 'PA de prueba #2'
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);

        $client->request('PUT', '/api/v1.0/integracion/puntosatencion/' . $puntoAtencionId, $signedParams);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Punto atención inexistente
        $client->request('PUT', '/api/v1.0/integracion/puntosatencion/0', $signedParams);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // Sin nombre
        $client->request('PUT', '/api/v1.0/integracion/puntosatencion/' . $puntoAtencionId, []);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Test Delete Action
     * Este método permite testear el borrado de un punto de atención
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostAction y testDeleteAction
     * Endpoint: integracion/puntosatencion/{puntoAtencionId}
     * @param integer $puntoAtencionId
     * @depends testPostAction
     */
    public function testDeleteAction($puntoAtencionId)
    {
        $client = self::createClient();
        $client->followRedirects();

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $url = $externalService->getTestUrl('/api/v1.0/integracion/puntosatencion/', $puntoAtencionId);

        $client->request('DELETE', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // No existe
        $client->request('DELETE', $url);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
