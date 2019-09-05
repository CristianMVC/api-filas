<?php

namespace ApiV1Bundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ControllerTestCase
 * @package ApiV1Bundle\Tests\Controller
 */
class ControllerTestCase extends WebTestCase
{
    private $container;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass()
    {
    }

    /**
     * Obtenemos el contenedor
     * @return object
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    protected function loginTestUser()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test',
            'password' => 'test'
        ];
        $client->request('POST', '/api/v1.0/auth/login', $params);
        // content
        $content = json_decode($client->getResponse()->getContent(), true);
        return $content['token'];
    }

    /**
     * Recibe un nombre de usuario, obtiene un JWT y devuelve el arreglo de
     * headers
     *
     * @param $username
     * @return array
     */
    protected function getHeaders($username)
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => $username,
            'password' => 'test1234'
        ];
        $client->request('POST', '/api/v1.0/auth/login', $params);
        $data = json_decode($client->getResponse()->getContent(), true);
        return [
            'HTTP_AUTHORIZATION' => "Bearer {$data['token']}"
        ];
    }

    /**
     * Obtener un token que no expira, sólo para las pruebas
     *
     * @param int $puntoatencionId
     * @return array
     */
    protected function getTokenPruebas($puntoatencionId=2)
    {
        switch ($puntoatencionId) {
            case 0: //test@test.com ROL administrador (sin permiso en carteleras)
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNDgwNTE5LCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTQ4MDUxOSwidWlkIjoyLCJ1c2VybmFtZSI6InRlc3RAdGVzdC5jb20iLCJyb2xlIjoiUk9MX0FETUlOIiwiZXhwIjoxOTgwMzc2NjA3fQ.quLWhrkrFRJ4riYt5lmdLYJuf4-x8JmM90l1S2i1af0";
                break;
            case 1: //pda2@pda.com responsable del punto de atención con id snt 2 | id snc 684
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNDgxNDk1LCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTQ4MTQ5NSwidWlkIjozNTgsInVzZXJuYW1lIjoicGRhMkBwZGEuY29tIiwicm9sZSI6IlJPTF9QVU5UT0FURU5DSU9OIiwicHVudG9hdGVuY2lvbiI6MiwiZXhwIjoxOTgwMzk5MTgzfQ.9HfaGV6tYKW7JIq0irzzKdWq3wm_RjCljKHtx5cUIZg";
                break;
            case 3: //pda3@pda.com responsable del punto de atención con id snt 3 | id snc inexistente
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNDgxNDMzLCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTQ4MTQzMywidWlkIjozNTksInVzZXJuYW1lIjoicGRhM0BwZGEuY29tIiwicm9sZSI6IlJPTF9QVU5UT0FURU5DSU9OIiwicHVudG9hdGVuY2lvbiI6MywiZXhwIjoxOTgwMzk5MTIxfQ.03tMaBcjBf1S_urLgb3xOOD9aPFkk91YMxRIFFjgcZ8";
                break;
            case 4: //agent.zed@mib.com agente del punto de atención con id snt 1 | id snc 1 | ventanilla actual 3
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNjQwMzMzLCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTY0MDMzMywidWlkIjo0LCJ1c2VybmFtZSI6ImFnZW50LnplZEBtaWIuY29tIiwicm9sZSI6IlJPTF9BR0VOVEUiLCJwdW50b2F0ZW5jaW9uIjoxLCJleHAiOjE5NzcyMjQ2ODh9.VcMwGZluTPRlUKh-CxWdUkti6CBmC1O9RPPTBuqR5D8";
                break;
            case 5: //agent.smith@mib.com agente del punto de atención con id snt 1 | id snc 1 | ventanilla actual 2
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNTc3MjY3LCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTU3NzI2NywidWlkIjozLCJ1c2VybmFtZSI6ImFnZW50LnNtaXRoQG1pYi5jb20iLCJyb2xlIjoiUk9MX0FHRU5URSIsInB1bnRvYXRlbmNpb24iOjEsImV4cCI6MTk3NzE2MTYyMn0.tcZiTNBPCpgreDcV3N-B_P8c6RAQShgReL-2DCoA9nc";
                break;
            default: //pda@mail.com responsable del punto de atención con id snt 1 | id snc 1
                $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTIxNDgwMzgyLCJqdGkiOiI5Zjc0YTdkZWU5ZTk3NWIwNmIzNTY4YTU2NDZmZTFiNCIsInRpbWVzdGFtcCI6MTUyMTQ4MDM4MiwidWlkIjoxMDQsInVzZXJuYW1lIjoicGRhQG1haWwuY29tIiwicm9sZSI6IlJPTF9QVU5UT0FURU5DSU9OIiwicHVudG9hdGVuY2lvbiI6MSwiZXhwIjoxOTgwMzk4MDcwfQ.4KIQXnQ23fXIfCRVRuJBU2vNt6-_41kP5gWa6-lXv7I";

        }
        return ['HTTP_AUTHORIZATION' => "Bearer {$token}"];
    }
}

