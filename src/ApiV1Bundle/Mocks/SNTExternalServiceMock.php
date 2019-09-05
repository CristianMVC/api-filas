<?php
namespace ApiV1Bundle\Mocks;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SNTExternalServiceMock
 * @package ApiV1Bundle\Mocks
 */
class SNTExternalServiceMock
{
    /** @var null $host  */
    private $host = null;

    /** @var array $urls */
    private $urls = [];
    private $apiId = [];
    private $keys = [];

    /**
     * SNTExternalServiceMock constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $config = $container->getParameter('integration');
        $this->host = $config['host'];
        $this->urls = $config['urls'];
        $this->apiId = $config['api_id'];
        $this->keys = $config['keys'];
    }

    /**
     * Mock get
     *
     * @param string $url
     * @return stdClass
     */
    public function get($url, $parameters = null)
    {
        $urlParts = parse_url($url);
        switch ($urlParts['path']) {
            case '/api/v1.0/turnos/2':
                $response = $this->getTurno();
                break;
            default:
                $response = $this->getResponse($parameters);
        }
        return $response;
    }

    /**
     * Mock post
     *
     * @param string $url
     * @param $body
     * @return mixed
     */
    public function post($url, $body = null)
    {
        $urlParts = parse_url($url);
        switch ($urlParts['path']) {
            case '/api/v1.0/integracion/turnos/fecha':
                $response = $this->getPuntosAtencionByFecha();
                break;
            case '/api/v1.0/integracion/turnos':
                $response = $this->getTurnoById();
                break;
            default:
                $response = $this->getResponse($body);
        }
        return $response;
    }

    /**
     * Mock put
     *
     * @param string $url
     * @param $body
     * @return mixed
     */
    public function put($url, $body)
    {
        return $this->getResponse($body);
    }

    /**
     * Mock delete
     *
     * @param string  $url
     * @param $body
     * @return mixed
     */
    public function delete($url, $body)
    {
        return $this->getResponse($body);
    }

    /**
     * Componer una url
     *
     * @param string $name
     * @param $additional
     * @return NULL|string
     */
    public function getUrl($system, $name, $additional = null, $params = null)
    {
        $url = null;
        if (isset($this->urls[$system][$name])) {
            $url = $this->host[$system] . $this->urls[$system][$name];
        }
        if ($url && $additional) {
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }
            $url .= $additional;
        }
        if ($url) {
            $params = $this->getSignedBody($params, false);
        }
        if ($url && $params) {
            if (strpos($url, '?') !== false) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= http_build_query($params);
        }
        return $url;
    }

    /**
     * Get test URL
     *
     * @param $url
     * @param $additional
     * @param $params
     * @return string
     */
    public function getTestUrl($url, $additional = null, $params = null)
    {
        if ($url && $additional) {
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }
            $url .= $additional;
        }
        if ($url) {
            $params = $this->getSignedBody($params, false);
        }
        if ($url && $params) {
            if (strpos($url, '?') !== false) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= http_build_query($params);
        }
        return $url;
    }

    /**
     * Return signed body for test purpose
     * @param $body
     * @return string
     */
    public function getTestSignedBody($body, $asObject = true)
    {
        return $this->getSignedBody($body, $asObject);
    }

    /**
     * Headers de la llamada a la API
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        return $headers;
    }

    /**
     * Response con objeto mock
     *
     * @return \stdClass
     */
    private function getResponse($body)
    {
        $response = new \stdClass();
        $response->code = 200;
        $response->body = $this->getSignedBody($body);
        return $response;
    }

    /**
     * Obtenemos el cuerpo del mensaje firmado
     *
     * @param $body
     * @return string
     */
    private function getSignedBody($body = null, $asObject = true)
    {
        if (! $body || ! is_array($body)) {
            $body = [];
        }
        $body['api_id'] = $this->apiId['snt'];
        $body['signature'] = $this->sign($body);
        if ($asObject) {
            $body = (object) $body;
        }
        return $body;
    }

    /**
     * Firma digitalmente un request
     *
     * @param array $request
     * @return string
     */
    private function sign($request)
    {
        $signature = '';
        ksort($request);
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                $value = implode(':', $value);
            }
            $signature .= $key . '+' . $value;
        }
        return hash_hmac('sha256', $signature, $this->keys['snt']);
    }

    /**
     * función recursiva que permite recorrer un arreglo y pasarlo a una cadena
     * @param array $arreglo arrteglo, normalmente el request
     * @return string
     */
    public function arrayToSignature($arreglo)
    {
        $cadena = '';
        ksort($arreglo);
        foreach ($arreglo as $key => $value) {
            if (is_array($value)) {
                if (count($value) != count($value, COUNT_RECURSIVE)){
                    $value = $this->arrayToSignature($value);
                }else{
                    ksort($value);
                    $value = implode(':', $value);
                }
            }
            $cadena .= $key . '+' . $value;
        }
        return $cadena;
    }


    /**
     * Devuelve un validateresultado con un turno
     *
     * @return mixed
     */
    private function getTurnoById()
    {
        $response = json_decode('{
            "metadata": [],
            "result": {
                "id": 1,
                "codigo": "c2a107a9-cd88-45f5-b382-c058f8e5e6d5",
                "punto_atencion": {
                    "id": 1,
                    "nombre": "pda::ANSES::005::023",
                    "direccion": "Calle falsa 123",
                    "latitud": -34.6033,
                    "longitud": -58.3816
                },
                "alerta": 2,
                "fecha": "2017-09-12",
                "hora": "16:26",
                "tramite": {
                    "id": 29,
                    "nombre": "Morbi ornare ligula id mauris luctus"
                },
                "grupo_tramite": {
                    "id": 38
                },
                "datos_turno": {
                    "nombre": "Juan",
                    "apellido": "Perez",
                    "cuil": "20469731767",
                    "email": "nowhere@example.com",
                    "telefono": "123456",
                    "campos": {
                        "nombre": "Dar?o",
                        "apellido": "Cvitanich",
                        "sexo": "radio3",
                        "cuil": "23-28423371-9",
                        "email": "fernandomviale@hotmail.com",
                        "telefono": "1554926448"
                    }
                },
                "estado": 1,
                "area": {
                    "id": 15,
                    "nombre": "ANSES::005",
                    "abreviatura": "7BD"
                }
            }
        }',true);
        return new ValidateResultado($response,[]);
    }

    /**
     * Devuelve un turno
     *
     * @return mixed
     */
    private function getTurno()
    {
        $response = json_decode('{
            "metadata": [],
            "result": {
                "id": 1,
                "codigo": "c2a107a9-cd88-45f5-b382-c058f8e5e6d5",
                "punto_atencion": {
                    "id": 1,
                    "nombre": "pda::ANSES::005::023",
                    "direccion": "Calle falsa 123",
                    "latitud": -34.6033,
                    "longitud": -58.3816
                },
                "alerta": 2,
                "fecha": "2017-09-12",
                "hora": "16:26",
                "tramite": {
                    "id": 29,
                    "nombre": "Morbi ornare ligula id mauris luctus"
                },
                "grupo_tramite": {
                    "id": 38
                },
                "datos_turno": {
                    "nombre": "Juan",
                    "apellido": "Perez",
                    "cuil": "20469731767",
                    "email": "nowhere@example.com",
                    "telefono": "123456",
                    "campos": {
                        "nombre": "Dar?o",
                        "apellido": "Cvitanich",
                        "sexo": "radio3",
                        "cuil": "23-28423371-9",
                        "email": "fernandomviale@hotmail.com",
                        "telefono": "1554926448"
                    }
                },
                "estado": 1,
                "area": {
                    "id": 15,
                    "nombre": "ANSES::005",
                    "abreviatura": "7BD"
                }
            }
        }');
        $response->code = 200;
        return $response;
    }

    /*
     * Devuelve un listado de puntos de atención por fecha
     */
    private function getPuntosAtencionByFecha()
    {
        $response = json_decode('{
            "metadata": {
                "resultset": {
                    "count": 1,
                    "offset": 0,
                    "limit": 10
                }
            },
            "result": [
                {
                    "id": 1,
                    "punto_atencion": 1,
                    "campos": {
                        "nombre": "nombre",
                        "apellido": "apellido",
                        "cuil": "27-27104266-9",
                        "email": "e@mail.com",
                        "telefono": "1234"
                    },
                    "fecha": "2018-01-08T00:00:00-03:00",
                    "hora": "1970-01-01T13:00:00-03:00",
                    "estado": 1,
                    "tramite": "Morbi ornare ligula id mauris luctus",
                    "codigo": "22be6d17"
                }
            ]
        }',true);
        return new ValidateResultado($response,[]);
    }
}
