<?php
namespace ApiV1Bundle\ExternalServices;

use Symfony\Component\DependencyInjection\Container;
use Unirest\Request;

/**
 * Class NotificationsExternalService
 * @package ApiV1Bundle\ExternalServices
 */
class NotificationsExternalService
{
    /** @var Container  */
    private $container;

    /** @var  $host*/
    private $host;

    /** @var  $user*/
    private $user;

    /** @var  $pass*/
    private $pass;

    /** @var  $token*/
    private $token;

    /** @var  $from*/
    private $from;

    /** @var  $subject*/
    private $subject;

    /** @var  $templates*/
    private $templates;

    /** @var  $urls*/
    private $urls;

    /** @var  $environment*/
    private $environment;

    /**
     * NotificationsExternalService constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $config = $container->getParameter('notificaciones');
        $this->container = $container;
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->pass = $config['pass'];
        $this->token = $config['token'];
        $this->from = $config['from'];
        $this->subject = $config['subject'];
        $this->templates = $config['templates'];
        $this->urls = $config['urls'];
        $this->environment = $this->getEnvironment();
    }

    /**
     * Obtiene el ambiente en que corre la aplicación
     *
     * @return string
     * @throws \Exception
     */
    private function getEnvironment()
    {
        return $this->container->get('kernel')->getEnvironment();
    }

    /**
     * Obtenemos la configuración del servicio
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'host' => $this->host,
            'user' => $this->user,
            'pass' => $this->pass,
            'token' => $this->token,
            'urls' => $this->urls
        ];
    }

    /**
     * Generate API token
     *
     * @return array|mixed|NULL
     * @throws \Unirest\Exception
     */
    public function getToken()
    {
        $url = $this->host . $this->urls['auth'];
        $body = Request\Body::json([
            'username' => $this->user,
            'password' => $this->pass
        ]);
        $response = Request::post($url, $this->getHeaders(), $body);
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * Obtener la lista de templates
     *
     * @param $name
     * @return mixed
     */
    public function getTemplate($name = null)
    {
        $url = $this->host . $this->urls['templates'];
        if ($name) {
            $url .= '/' . $name;
        }
        $response = Request::get($url, $this->getHeaders(true));
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * Test del template
     *
     * @param $name
     * @param $data
     * @return mixed
     */
    public function testTemplate($name, $data)
    {
        $url = $this->host . sprintf($this->urls['template_test'], $name);
        $response = Request::post($url, $this->getHeaders(true), $data);
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * Crear template
     *
     * @param $name
     * @param $template
     * @return mixed
     * @throws \Unirest\Exception
     */
    public function crearTemplate($name, $template)
    {
        $url = $this->host . $this->urls['templates'];
        $body = Request\Body::json([
            'name' => $name,
            'template' => $template
        ]);
        $response = Request::post($url, $this->getHeaders(true), $body);
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * Editar un template
     *
     * @param $name
     * @param $template
     * @return mixed
     * @throws \Unirest\Exception
     */
    public function editarTemplate($name, $template)
    {
        $url = $this->host . $this->urls['templates'] . '/' . $name;
        $body = Request\Body::json([
            'template' => $template
        ]);
        $response = Request::put($url, $this->getHeaders(true), $body);
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * ENviar una notificacion
     *
     * @param $template
     * @param $email
     * @param $cuil
     * @param $params
     * @return mixed
     * @throws \Unirest\Exception
     */
    public function enviarNotificacion($template, $email, $cuil, $params)
    {
        $url = $this->host . $this->urls['notifications'];
        $body = Request\Body::json([
            'recipients' => [
                [
                    'content' => [
                        'email' => [
                            'params' => $params,
                            'from' => $this->from,
                            'to' => $email,
                            'subject' => $this->subject,
                            'template' => $template
                        ],
                    ],
                    'cuil' => $cuil,
                    'force' => true
                ]
            ]
        ]);
        // enviar mensaje solo si estamos en un entorno productivo
        if ($this->environment == 'prod') {
            $response = Request::post($url, $this->getHeaders(true), $body);
            $errors = $this->checkErrors($response->body);
            if ($errors) {
                return $errors;
            }
            return $response->body;
        }
        return ['env' => $this->environment];
    }

    /**
     * Verificar estado de una notificación
     *
     * @param $id
     * @return mixed
     */
    public function verificarNotificacion($id)
    {
        $url = $this->host . $this->urls['notifications'] . $id;
        $response = Request::get($url, $this->getHeaders(true));
        $errors = $this->checkErrors($response->body);
        if ($errors) {
            return $errors;
        }
        return $response->body;
    }

    /**
     * Devuelve el nombre del template que se usa
     *
     * @return string
     */
    public function getEmailTemplate($name)
    {
        return $this->templates[$name];
    }

    /**
     * Check errors
     *
     * @param $body
     * @return array|NULL
     */
    private function checkErrors($body)
    {
        if (property_exists($body, 'non_field_errors')) {
            return $body->non_field_errors;
        }
        return null;
    }

    /**
     * Headers de la llamada a la API
     *
     * @param string $token
     * @return []
     */
    private function getHeaders($token = null)
    {
        $headers = [];
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        if ($token) {
            $headers['Authorization'] = 'Token ' . $this->token;
        }
        return $headers;
    }
}
