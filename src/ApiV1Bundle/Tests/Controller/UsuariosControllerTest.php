<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class UsuariosControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */
class UsuariosControllerTest extends ControllerTestCase
{
    /** @var integer $userId */
    protected $userId;

    /** @var integer $userAdminId     */
    protected $userAdminId;

    /** @var integer $userResponsableId;     */
    protected $userResponsableId;

    /** @var integer $userAenteId; */
    protected $userAgenteId;

    /** @var string $userToken */
    protected $userToken;

    /**
     * generateRandomString
     * función que se utiliza para generar strings aleatorios para usarlos como parte del nombre de usuario.
     * @param int $length
     *
     * @return string
     */
    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * testGetVisualizarUsuariosAction
     * Test automatizado para probar el obtener un listado de usuarios
     * Endpoint: /usuarios
     */
    public function testGetVisualizarUsuariosAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'offset' => '0',
            'limit'  => '10'
        ];
        $client->request('GET', '/api/v1.0/usuarios', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostAction
     * Test automatizado para probar el alta de usuario
     * Endpoint: /usuarios
     * @return int
     */
    public function testPostAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre'        => 'Juan',
            'apellido'      => 'Albornoz',
            'username'      => $this->generateRandomString().'@nomail.com.ar',
            'rol'           => 5,
            'puntoAtencion' => 1,
            'ventanillas'   => [2]
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuarioId = json_decode($client->getResponse()->getContent(), true);
        $this->userId   = $nuevoUsuarioId['additional']['id'];
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Punto de atención inexistente
        $params['puntoAtencion'] = 0;
        $client->request('POST', '/api/v1.0/usuarios', $params);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $params['puntoAtencion'] = 1;

        // Apellido faltante
        unset($params['apellido']);
        $client->request('POST', '/api/v1.0/usuarios', $params);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // Username faltante
        $params['apellido'] = 'Albornoz';
        unset($params['username']);
        $client->request('POST', '/api/v1.0/usuarios', $params);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        return $this->userId;
    }

    /**
     * testPostAgenteAction
     * Test automatizado para testear la creación de un nuevo agente SIN ventanilla
     * Endpoint /usuarios
     */
    public function testPostAgenteSinVentanillaAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre'        => 'Mariano',
            'apellido'      => 'Moreno',
            'username'      => $this->generateRandomString().'@nomail.com.ar',
            'rol'           => 5,
            'puntoAtencion' => 1,
            'ventanillas'   => []
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testPostAgenteAction
     * Test automatizado para testear la creación de un nuevo agente
     * Endpoint /usuarios
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testLogin y testPostAgenteAction
     * @return int
     */
    public function testPostAgenteAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre'        => 'Mariano',
            'apellido'      => 'Alvear',
            'username'      => $this->generateRandomString().'@nomail.com.ar',
            'rol'           => 5,
            'puntoAtencion' => 1,
            'ventanillas'   => [2]
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuarioId = json_decode($client->getResponse()->getContent(), true);
        $this->userAgenteId   = $nuevoUsuarioId['additional']['id'];
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        return $this->userAgenteId;
    }

    /**
     * testModificarAgenteAction
     * Test automático para testear la modificación de un agente
     * Endpoint: /usuarios/{idUser}
     * @param $userAgenteId
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostAction y testModificarAgenteAction y
     * entre testLogin y testModificarAgenteAction
     * @depends testPostAction
     */
    public function testModificarAgenteAction($userAgenteId)
    {
        $this->userId = $userAgenteId;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre'        => 'Guillermo',
            'apellido'      => 'Copola',
            'username'      => $this->generateRandomString().'@nomail.com.ar',
            'rol'           => 5,
            'puntoAtencion' => 1,
            'ventanillas'   => [2]
        ];

        $client->request('PUT', '/api/v1.0/usuarios/'.$this->userId, $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testObtenerUsuario
     * Test automatizado para probar el obtener los datos de un usuario
     * Endpoint: /usuarios/{idUser}
     */
    public function testObtenerUsuario()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/usuarios/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testEliminarUsuarioAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}
     * @param $userId
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostAction y testEliminarUsuarioAction
     * @depends testPostAction
     *
     */
    public function testEliminarUsuarioAction($userId)
    {
        $this->userId = $userId;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/'.$this->userId);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * testEliminarUsuarioAgenteAction
     * Test automatizado para testear el borrado de un usuario agente
     * @param $userAgenteId
     * /usuarios/{userAgenteId}
     * Annotation depends permite generar una dependencia explicita entre métodos,
     * para el caso particular entre testPostAgenteAction y testEliminarUsuarioResponsableAction
     * @depends testPostAgenteAction
     *
     */
    public function testEliminarUsuarioAgenteAction($userAgenteId)
    {
        $this->userAgenteId = $userAgenteId;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/'.$this->userAgenteId);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
