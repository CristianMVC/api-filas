<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class CarteleraControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class CarteleraControllerTest extends ControllerTestCase
{
    /** @var integer */
    protected $carteleraId;

    /**
     * testPostAction
     * Test automatizado que permite testear la creación de una cartelera en SNC
     * Requiere que las colas con ids 1,2 y 3 estén relacionadas al punto de atención con id snt 1 | id snc 1
     * Endpoint: /carteleras
     */
    public function testPostAction()
    {
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'testPostAction1',
            'colas' => [1,2,3]
        ];
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->carteleraId = $data['additional']['id'];
        return $this->carteleraId;
    }

    /**
     * testGetListAction
     * Test automatizado que permite probar el obtener el listado de carteleras
     * Endpoint: /carteleras
     */
    public function testGetListAction()
    {
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'offset'=>0,
            'limit'=>30
        ];
        $client->request('GET', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);

        //cantidad de registros
        $this->assertEquals($data['metadata']['resultset']['count'], count($data['result']));

        //estructura de la cartelera
        $this->assertTrue(array_key_exists('id', $data['result'][0]));
        $this->assertTrue(array_key_exists('nombre', $data['result'][0]));
        $this->assertTrue(array_key_exists('colas', $data['result'][0]));

        //estructura de la cola
        $this->assertTrue(array_key_exists('id', $data['result'][0]['colas'][0]));
        $this->assertTrue(array_key_exists('nombre', $data['result'][0]['colas'][0]));
    }

    /**
     * testFailedPostAction
     * Test automatizado que permite testear que falle la creación
     *   de una cartelera en SNC, por diversos motivos
     * Requiere que la cola con id 4 estén relacionada a un punto de atención diferente al id snt 1 | id snc 1
     * Endpoint: /carteleras
     */
    public function testFailedPostAction()
    {
        //colas es requerido (llega vacío el arreglo)
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'testPostActionFailed',
            'colas' => []
        ];
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(array_key_exists('Colas', $data['userMessage']['errors']));
        $this->assertTrue($data['userMessage']['errors']['Colas'] == 'Es un campo requerido');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        //el campo colas no existe
        $params = [
            'nombre' => 'testPostActionFailed'
        ];
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Colas'] == 'El campo no existe para poder ser validado');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        //cola inexistente
        $params = [
            'nombre' => 'testPostActionFailed',
            'colas' => [9999099]
        ];
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Cola'] == 'Cola inexistente');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        //la cola asignada es de otro punto de atención
        $params = [
            'nombre' => 'testPostActionFailed',
            'colas' => [4]
        ];
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue( in_array ('La cola con ID: 4 no pertenece al punto de atención con ID:1', $data['userMessage']['errors']));
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // el usuario logueado es responsable de un punto de atención inexistente
        $client->request('POST', '/api/v1.0/carteleras', $params, [], $this->getTokenPruebas(3));
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue($data['userMessage']['errors']['Punto de Atencion'] == 'Punto de atencion inexistente');
    }

    /**
     * GetItemAction
     * Este método testea el obtener una cartelera creada por el método PostAction
     * Endpoint: carteleras/{id}
     * @depends testPostAction
     */
    public function testGetItemAction($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);

        //verificar estructura de la cartelera
        $this->assertTrue(array_key_exists('id', $data['result']));
        $this->assertTrue(array_key_exists('nombre', $data['result']));
        $this->assertTrue(array_key_exists('puntoAtencion', $data['result']));
        $this->assertTrue(array_key_exists('colas', $data['result']));

        //verificar estructura de la cola
        $this->assertTrue(array_key_exists('id', $data['result']['colas'][0]));
        $this->assertTrue(array_key_exists('nombre', $data['result']['colas'][0]));

        //verificar estructura del punto de atención
        $this->assertTrue(array_key_exists('id', $data['result']['puntoAtencion']));
        $this->assertTrue(array_key_exists('nombre', $data['result']['puntoAtencion']));
    }

    /**
     * GetItemActionFailed403
     * Este método testea el fallar obtener una cartelera por permisología
     * Endpoint: carteleras/{id}
     * @depends testPostAction
     */
    public function testGetItemActionFailed403($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();

        // El usuario logueado es responsable de un punto de atención diferente al punto de atencion de la cartelera
        $client->request('GET', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas(1));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // El usuario logueado es responsable de un punto de atención inexistente
        $client->request('GET', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas(3));
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Punto de Atencion'] == 'Punto de atencion inexistente');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * GetItemActionFailed
     * Este método testea fallar obtener una cartelera por no existir
     * Endpoint: carteleras/{id}
     * @depends testPostAction
     */
    public function testGetItemActionFailed()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/carteleras/120999', [], [], $this->getTokenPruebas(1));
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Cartelera'] == 'Cartelera inexistente.');

    }

    /**
     * testPutAction
     * Este método testea modificar una cartelera creada por el método PostAction
     * Endpoint: carteleras/{id}
     * Requiere que las colas con ids 1 y 2 estén relacionadas al punto de atención con id snt 1 | id snc 1
     * @depends testPostAction
     */
    public function testPutAction($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'testPutAction',
            'colas' => [1,2]
        ];
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPutActionFailed
     * Este método testea que falle modificar una cartelera por diversos motivos
     * Endpoint: carteleras/{id}
     * Requiere que las colas con ids 1 y 2 estén relacionadas al punto de atención con id snt 1 | id snc 1*
     * Requiere que la cola con id 4 estén relacionada a un punto de atención diferente al id snt 1 | id snc 1
     * @depends testPostAction
     */
    public function testPutActionFailed($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'testPutAction',
            'colas' => [1,2]
        ];
        // El usuario logueado es responsable de un punto de atención diferente al punto de atencion de la cartelera
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas(1));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // El usuario logueado es responsable de un punto de atención inexistente
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas(3));
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Punto de Atencion'] == 'Punto de atencion inexistente');

        //El usuario logueado es el correcto pero la cola no pertenece al punto de atención
        $params = [
            'nombre' => 'testPutAction',
            'colas' => [4]
        ];
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue( in_array ('La cola con ID: 4 no pertenece al punto de atención con ID:1', $data['userMessage']['errors']));

        //El usuario logueado es el correcto pero la cola no existe
        $params = [
            'nombre' => 'testPutAction',
            'colas' => [7639999]
        ];
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue($data['userMessage']['errors']['Cola'] == 'Cola inexistente');

        //El usuario logueado es el correcto pero no se especificaron colas
        $params = [
            'nombre' => 'testPutAction',
            'colas' => []
        ];
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, $params, [], $this->getTokenPruebas());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['userMessage']['errors']['Colas'] == 'Es un campo requerido');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * testDelActionFailed
     * Este método testea fallar al eliminar una cartelera porque no existe
     * Endpoint: carteleras/{id}
     */
    public function testDelActionFailed()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/carteleras/120', [], [], $this->getTokenPruebas());
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * testDelActionFailed403
     * Este método testea que falle eliminar una cartelera por permisología
     * Endpoint: carteleras/{id}
     * @depends testPostAction
     */
    public function testDelActionFailed403($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();

        // El usuario logueado es responsable de un punto de atención diferente al punto de atencion de la cartelera
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas(1));
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        // El usuario logueado es responsable de un punto de atención inexistente
        $client->request('PUT', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas(3));
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue($data['userMessage']['errors']['Punto de Atencion'] == 'Punto de atencion inexistente');
    }

    /**
     * testDelAction
     * Este método testea eliminar una cartelera creada por el método PostAction
     * Endpoint: carteleras/{id}
     * @depends testPostAction
     */
    public function testDelAction($id)
    {
        $this->carteleraId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/carteleras/' . $this->carteleraId, [], [], $this->getTokenPruebas());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
