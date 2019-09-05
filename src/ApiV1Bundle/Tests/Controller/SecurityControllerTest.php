<?php
namespace ApiV1Bundle\Tests\Controller;

use ApiV1Bundle\Mocks\SNTExternalServiceMock;

/**
 * Class SecurityControllerTest
 * @package ApiV1Bundle\Tests\Controller
 */

class SecurityControllerTest extends ControllerTestCase
{
    /**
     * testLogin
     * Test automatizado que permite testear el login de usuario
     *
     * @params array con los datos del usuario que hace login
     * Endpoint: /auth/login
     * @return mixed
     */
    public function testLogin()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test@test.com',
            'password' => 'test1234'
        ];
        $client->request('POST', '/api/v1.0/auth/login', $params);
        // content
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('token', $content));
        return $content['token'];
    }

    /**
     * Validamos el token del usuario
     * @depends testLogin
     */
    public function testValidarToken($token)
    {
        $client = static::createClient();
        $client->followRedirects();
        $headers = [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/json'
        ];

        $client->request('POST', '/api/v1.0/auth/validar', [], [], $headers);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('test@test.com', json_decode($client->getResponse()->getContent())->additional->username);
        return $token;
    }

    /**
     * TestLogout user
     * Test automatizado que permite testear el logount de un usuario logueado al sistema
     * @param $token - Token de seguridad del usuario logueado
     * Endpoint: /auth/logout
     * @depends testValidarToken
     */
    public function testLogout($token)
    {
        $client = static::createClient();
        $client->followRedirects();
        $headers = [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/json'
        ];
        $client->request('POST', '/api/v1.0/auth/logout', [], [], $headers);
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('status', $content));
        $this->assertEquals('SUCCESS', $content['status']);
        return $token;
    }

    /**
     * Test recuperar contraseña
     * Test automatizado que permite testear el recuperar la contraseña de un usuario
     * $params - Array con parámetros necesarios para ejecutar la acción de recuperar la contraseña del usuario.
     * Endpoint: /auth/reset
     * @params array con el username para recuperar su contraseña
     */
    public function testRecuperarPassword()
    {
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test-recover@test.com'
        ];
        $client->request('POST', '/api/v1.0/auth/reset', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test modificar contraseña
     * Tests automatizado que permite testear el modificar la contraseña de un usuario
     * $params - Array con parámatros necesarios para modificar la contraseña del usuario
     * Endpoint: /auth/modificar
     */
    public function testModificarPassword()
    {
        $client = self::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test@test.com',
            'password' => 'test1234',
            'nuevoPassword' => 'test1234'
        ];
        $client->request('POST', '/api/v1.0/auth/modificar', $params);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test de comunicación entre APIs
     * Tests automatizado que permite testear la comunicación segura entre las apis
     * $params - Array con usuario y password para autenticar a un usuario con el fin de probar
     * la seguridad en la comunicación entre las APIS.
     * Endpoint: /integration/secure/request
     */
    public function testSecureCommunication()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test@test.com',
            'password' => 'test'
        ];
        // sign the reques
        $externalSevice = new SNTExternalServiceMock($this->getContainer());
        $signedRequest = $externalSevice->getTestSignedBody($params);
        // make the call
        $client->request('POST', '/api/v1.0/integration/secure/request', $params);
        // content
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($content['body']['username'], $params['username']);
        $this->assertEquals($content['body']['password'], $params['password']);
        $this->assertEquals($content['body']['api_id'], $signedRequest->api_id);
        $this->assertEquals($content['body']['signature'], $signedRequest->signature);
    }

    /**
     * Validamos el token
     */
    public function testTokenInvalid()
    {
        $client = static::createClient();
        $client->followRedirects();
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbmMuaGV4YWN0YS5jb20iLCJh';
        $token .= 'dWQiOiJodHRwOlwvXC9zbmMuaGV4YWN0YS5jb20iLCJpYXQiOjE1MTU3NjI3MTQsImV4cCI6MTUxNTc2OTkx';
        $token .= 'NCwianRpIjoiODExZmY0ZGI3NDc4NzFiOWRhZWFlMzYwYmFiNjJiZDYiLCJ0aW1lc3RhbXAiOjE1MTU3NjI3';
        $token .= 'MTQsInVpZCI6NjMsInVzZXJuYW1lIjoicGVAcGUuY29tIiwicm9sZSI6IlJPTF9BR0VOVEUifQ.3xyjJo_13';
        $token .= '0Me1dJiQu9n2nFMVL0xw9GdZNVN_D8Pwxg';
        $headers = [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/json'
        ];
        $client->request('POST', '/api/v1.0/auth/validar', [], [], $headers);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
