<?php
namespace ApiV1Bundle\Tests\Controller;

use ApiV1Bundle\Mocks\SNTExternalServiceMock;

/**
 * Class ColasControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class ColasControllerTest extends ControllerTestCase
{
    /**
     * @var $postaId
     */
    protected $postaId;
    /**
     * testGetVisualizarColasAction
     * Test automatizado que permite testar el visualizar las colas disponibles
     * @params array con los datos para realizar la busqueda
     * Endpoint: /colas
     */
    public function testGetColasAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'puntoatencion' => 1,
            'offset' => '0',
            'limit' => '10'
        ];
        $client->request('GET', '/api/v1.0/colas', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testGetObntenerColasAction
     * Test automatizado que pemite testear el obtener una cola de entre las disponibles
     *
     * @param integer ColaId
     * Endpoint: /colas/{colaId}
     */
    public function testGetColasByIdAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/colas/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * test getDelanteAction
     * Test automatizado que pemite testear obtener las personas delante en una cola
     * a partir del punto de atención y el grupo trámite
     *
     * @param integer puntoAtencionId
     * @param  integer grupoTramiteSNTId
     * Endpoint: /integration/tramite/delante
     */
    public function testGetDelanteAction()
    {
        $client = static::createClient();
        $client->followRedirects();

        $body = [
            'puntoatencion' => 1,
            'grupo_tramite' => 1
        ];

        $client->request('POST', '/api/v1.0/integration/tramite/delante', $body);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostAction
     * Test automatizado para crear una nueva cola /grupo tramite
     * @params array con los datos de la cola a crear
     * Endpoint: /colas/grupotramite
     * @return mixed
     */
    public function testPostAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Cola 2',
            'grupoTramite' => 2,
            'puntoAtencion' => 1
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);
        $client->request('POST', '/api/v1.0/colas/grupotramite', $signedParams);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPutModificarColaGrupoTramiteAction
     * Test automatizado para probar modificar una cola grupo Tramite determinada
     *
     * @params array con los datos para modificar  la cola
     * Endpoint: /colas/grupotramite/{colaId}
     */
    public function testPutAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Cola 2 update'
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);

        $client->request('PUT', '/api/v1.0/colas/grupotramite/2', $signedParams);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostColaPostaAction
     * Test automatizado para crear postas de una cola
     * $params contiene los datos para dar de alta la Posta
     * Endpoint: /colas/postas
     * @return mixed
     */
    public function testPostColaPostaAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Cola 2 para la Posta',
            'puntoAtencion' => 1
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);

        $client->request('POST', '/api/v1.0/colas/postas', $signedParams);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->postaId = $data['additional']['id'];
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $this->postaId;
    }

    /**
     * testPostColaPostaAction
     * Test automatizado para modificar una posta
     * $params Contiene el dato del nombre de la posta para modificar
     * Endpoint: colas/postas/{postaId}
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostColaPostaAction y testputColaPostaAction
     * @depends testPostColaPostaAction
     */

    public function testputColaPostaAction($idposta)
    {
        $this->postaId = $idposta;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Cola 2 update de posta'
        ];

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $signedParams = $externalService->getTestSignedBody($params, false);

        $client->request('PUT', '/api/v1.0/colas/postas/'.$this->postaId, $signedParams);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testDeleteColaPostaAction
     * Test automatizado para borrar una posta
     * Endpoint: colas/postas/{postaId}
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostColaPostaAction y testputColaPostaAction
     * @depends testPostColaPostaAction
     */

    public function testdeleteColaPostaAction($idposta)
    {
        $this->postaId = $idposta;
        $client = static::createClient();
        $client->followRedirects();

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $url = $externalService->getTestUrl('/api/v1.0/colas/postas/'.$this->postaId);

        $client->request('DELETE', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }


    /**
     * testEliminarColaGrupoTramite
     * Test automatizado para probar eliminar una cola determinada
     *
     * Endpoint:/colas/grupotramite/{colaId}
     * @param integer $colaId
     */

    public function testDeleteAction()
    {
        $client = static::createClient();
        $client->followRedirects();

        $externalService = new SNTExternalServiceMock($this->getContainer());
        $url = $externalService->getTestUrl('/api/v1.0/colas/grupotramite/2');

        $client->request('DELETE', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testGetPostas()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/colas/postas?puntoatencion=1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testGetPostasAsignadas()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/colas/postas/asignadas?puntoatencion=1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
