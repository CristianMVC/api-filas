<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class TurnosControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */

class TurnoControllerTest extends ControllerTestCase
{

    /**
     * Test enviar información de proximo turno a carteleras (nodejs)
     *
     */
    public  function  testPostEnviarTurnoNodeJsAction()
    {
        $puntoAtencion = 1;
        $grupoTramite = 1;
        $ventanilla = 3;

        //se recepciona el turno
        $client = static::createClient();
        $client->followRedirects();
        $code = md5(uniqid());
        $params = [
            'puntoAtencion' => $puntoAtencion,
            'tramite' => 'testPostEnviarTurnoNodeJsAction',
            'grupoTramite' => $grupoTramite,
            'fecha' => '2018-03-27T03:00:00.000Z',
            'hora' => '16:26',
            'estado' => 3,
            'codigo' => $code,
            'prioridad' => 2,
            'datosTurno' => [
                'nombre' => 'Juan',
                'apellido' => 'Perez',
                'documento' => 93941677,
                'email' => 'nowhere@example.com',
                'telefono' => 123456,
                'campos' => [
                    'cuil' => "123456",
                    'dolor' => 'sit amet'
                ]
            ]
        ];
        $client->request('POST', '/api/v1.0/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se atiende y envía la información a la cartelera
        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanilla,
            [], [], $this->getTokenPruebas(4)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('error', $data['result']['cartelera']));
        $this->assertTrue(array_key_exists('mensaje', $data['result']['cartelera']));
    }

    /**
     * Test obtener siguiente turno
     * Test automatizado que permite testear el llamado al siguiente turno desde una ventanilla
     * Endpoint: turnos/proximo
     * Parametros: PuntoAtencionId y VentanillaId
     * @param integer puntoAtencionId
     * @param integer ventanillaId
     */
    public function testGetNextTurnosAction()
    {
        //se crea un turno
        $client = static::createClient();
        $client->followRedirects();
        $code = md5(uniqid());
        $puntoAtencion = 1;
        $ventanilla = 3;
        $params = [
            'puntoAtencion' => $puntoAtencion,
            'tramite' => 'testGetNextTurnosAction',
            'grupoTramite' => 1,
            'fecha' => '2017-12-14T16:26:00.000Z',
            'hora' => '16:26',
            'estado' => 3,
            'codigo' => $code,
            'prioridad' => 1,
            'datosTurno' => [
                'nombre' => 'Juan',
                'apellido' => 'Perez',
                'documento' => 93941676,
                'email' => 'nowhere@example.com',
                'telefono' => 123456,
                'campos' => [
                    'cuil' => '93941676',
                    'dolor' => 'sit amet'
                ]
            ]
        ];
        $client->request('POST', '/api/v1.0/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se llama el turno
        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanilla,
            [],[], $this->getTokenPruebas(4)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test Integración Turnos
     *
     * testColaIntegrationAction
     * Test automatizado que permite testear el camino feliz del SNC
     *
     */
    public function testColaIntegrationAction()
    {
        $puntoAtencion = 1;
        $ventanillaGT = 3;
        $grupoTramite = 1;
        $ventanillaP = 2;
        $colaP = 1164;
        //se recepciona el turno
        $client = static::createClient();
        $client->followRedirects();
        $code = md5(uniqid());
        $params = [
            'puntoAtencion' => $puntoAtencion,
            'tramite' => 'testColaIntegrationAction',
            'grupoTramite' => $grupoTramite,
            'fecha' => '2017-12-14T16:26:00.000Z',
            'hora' => '16:26',
            'estado' => 3,
            'codigo' => $code,
            'prioridad' => 1,
            'datosTurno' => [
                'nombre' => 'Juan',
                'apellido' => 'Perez',
                'documento' => 93941677,
                'email' => 'nowhere@example.com',
                'telefono' => 123456,
                'campos' => [
                    'cuil' => '93941677',
                    'dolor' => 'sit amet'
                ]
            ]
        ];
        $client->request('POST', '/api/v1.0/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se lo atiende
        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanillaGT,
            [], [], $this->getTokenPruebas(4)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se pasa a una posta
        $params = [
            'codigo' => $code,
            'cuil' => '93941677',
            'prioridad' => 2,
            'estado' => 4,
            'ventanilla' => $ventanillaGT,
            'cola' => $colaP
        ];
        $client->request('POST', '/api/v1.0/turnos/estado', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se lo atiende
        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanillaP,
            [], [], $this->getTokenPruebas(5)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //se lo termina
        $params = [
            'codigo' => $code,
            'cuil' => '93941677',
            'prioridad' => 2,
            'estado' => 5,
            'ventanilla' => $ventanillaP
        ];
        $client->request('POST', '/api/v1.0/turnos/estado', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testObtenerTurnoAction
     * Test automatizado que permite testear el obtener un turno en el SNT
     * @param integer turnoId
     * Endpoint: /snt/turnos/{turnoId}
     */
    public function testObtenerTurnoAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/snt/turnos/2');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testObtenerTurnosDeSNT
     * Test automatizado que permite testear el obtener una lista de turnos dek SNT
     * $params Array con el id de PA, fecha y datos para el listado
     * @params array con los datos de la búsqueda
     * Endpoint: /snt/turnos
     */
    public function testObtenerTurnosDeSNT()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'puntoatencion' => 3,
            'fecha' => '2018-01-11',
            'offset' => 0,
            'limit' => 10
        ];
        $client->request('GET', '/api/v1.0/snt/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostGuardarTurnoAction
     * Test automatizado que permite testear el guardar un turno en el SNC
     * $params Array conteniendo los datos del turno a guardar en SNC
     * Endpoint: /turnos
     * @return string
     */
    public function testPostGuardarTurnoAction()
    {
        $puntoAtencion = 1;
        $ventanilla = 3;
        $client = static::createClient();
        $client->followRedirects();
        $code = md5(uniqid());
        $params = [
            'puntoAtencion' => 1,
            'tramite' => 'testPostGuardarTurnoAction',
            'grupoTramite' => 1,
            'fecha' => '2017-12-14T16:26:00.000Z',
            'hora' => '16:26',
            'estado' => 3,
            'codigo' => $code,
            'prioridad' => 1,
            'datosTurno' => [
                'nombre' => 'Juan',
                'apellido' => 'Perez',
                'documento' => 93941676,
                'email' => 'nowhere@example.com',
                'telefono' => 123456,
                'campos' => [
                    'cuil' => '93941676',
                    'dolor' => 'sit amet'
                ]
            ]
        ];
        $client->request('POST', '/api/v1.0/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanilla,
            [],[], $this->getTokenPruebas(4)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return $code;
    }

    /**
     * testPostCambiarEstadoAction
     * Test automatizado que permite testear el cambiar de estado un turno desde el SNC
     * @params array con los datos del turno
     * Endopint: /turnos/estado
     * $code: strig con el código correspondiente al turno obtenido de SNT
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostGuardarTurnoAction y testPostCambiarEstadoAction
     * @depends testPostGuardarTurnoAction
     */
    public function testPostCambiarEstadoAction($code)
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'codigo' => $code,
            'cuil' => '93941676',
            'prioridad' => 2,
            'estado' => 5,
            'ventanilla' => 3,
            'motivo' => 'Tramite completo',
            'fecha' => '2017-12-14',
            'hora' => '16:26'
        ];
        $client->request('POST', '/api/v1.0/turnos/estado', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostCambiarColaAction
     * Test automatizado que permite testear cambiar el estado de un turno a En Transcurso
     * y cambiarlo de cola
     *
     * @params array con los datos del turno
     * Endpoint: /turnos/estado
     * @depends testPostGuardarTurnoAction
     */
    public function testPostCambiarColaAction($code)
    {
        $client = static::createClient();
        $client->followRedirects();
        $puntoAtencion = 1;
        $ventanilla = 3;
        $ventanillaP = 2;
        $colaP = 1164;
        $params = [
            'codigo' => $code,
            'cuil' => '93941676',
            'prioridad' => 2,
            'estado' => 4,
            'ventanilla' => $ventanilla,
            'cola' => $colaP
        ];

        $client->request('POST', '/api/v1.0/turnos/estado', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(property_exists(json_decode($client->getResponse()->getContent())->additional, 'id'));

        //se lo atiende
        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanillaP,
            [], [], $this->getTokenPruebas(5)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostGuardarTurnoAction
     * Test automatizado que permite testear el guardar un turno en el SNC
     *
     * @params array con los datos del turno
     * Endpoint: /integration/turnos
     */
    public function testPostGuardarTurnoActionIntegracion()
    {
        $puntoAtencion = 1;
        $ventanilla = 3;
        $client = static::createClient();
        $client->followRedirects();
        $code = md5(uniqid());
        $params = array(
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'cuil' => '20327023250',
            'puntoatencion' => $puntoAtencion,
            'tramite' => 'testPostGuardarTurnoActionIntegracion',
            'grupo_tramite' => 1,
            'codigo' => $code,
            'prioridad' => 1,
            'fecha' => '2017-11-10T03:00:00.000Z',
            'hora'=> '10:00',
            'estado' => 3,
            'campos' => array(
                'apellido' => 'Perez',
                'cuil' => '12345678941'
            )
        );

        $client->request('POST', '/api/v1.0/integration/turnos', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request(
            'GET',
            '/api/v1.0/turnos/proximo?puntoatencion=' . $puntoAtencion . '&ventanilla=' . $ventanilla,
            [],[], $this->getTokenPruebas(4)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return $code;
    }

}
