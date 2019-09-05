<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Response\Respuesta;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SNCServices
 * @package ApiV1Bundle\ApplicationServices
 */

class SNCServices
{
    /** @var Container $container */
    private $container;

    /**
     * SNTServices constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Obtiene uno de los parametros de la configuración
     *
     * @param string $parameter Parámetro a obtener
     * @return mixed|mixed[]
     */
    protected function getParameter($parameter)
    {
        return $this->container->getParameter($parameter);
    }

    /**
     * Obtiene el ambiente en que corre la aplicación
     *
     * @return string
     * @throws \Exception
     */
    protected function getEnvironment()
    {
        return $this->container->get('kernel')->getEnvironment();
    }

    /**
     * Valida una entidad que recibe por parámetro
     *
     * @param object $entity Objeto entidad     *
     * @return array
     * @throws \Exception
     */
    protected function validate($entity)
    {
        $response = [
            'errors' => []
        ];
        $errors = $this->container->get('validator')->validate($entity);

        if (count($errors)) {
            foreach ($errors as $error) {
                $response['errors'][$error->getPropertyPath()] = $error->getMessage();
            }
        }
        return $response;
    }

    /**
     * Retorna la cantidad de errores que se produjeron
     *
     * @param array $errors Array con los errores que se produjeron     *
     * @return int
     */
    protected function hasErrors($errors)
    {
        return (count($errors['errors']));
    }

    /**
     * Procesa el resultado
     * @param object $validateResult Objeto a validar
     * @param callback $onSucess Callback para devolver respuesta exitosa
     * @param callback $onError Callback para devolver respuesta fallida
     *
     * @return mixed
     * @throws \Exception
     */
    protected function processResult($validateResult, $onSucess, $onError)
    {
        if ($validateResult->hasError()) {
            return call_user_func($onError, $validateResult->getErrors());
        } else {
            $errors = $this->validate($validateResult->getEntity());
            if ($this->hasErrors($errors)) {
                return call_user_func($onError, $errors);
            } else {
                return call_user_func($onSucess, $validateResult->getEntity());
            }
        }
    }

    /**
     * Procesa el error
     *
     * @param object $validateResult Objeto a validar
     * @param callback $onSucess Callback para devolver respuesta exitosa
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     */
    protected function processError($validateResult, $onSucess, $onError)
    {
        if ($validateResult->hasError()) {
            return call_user_func($onError, $validateResult->getErrors());
        }
        return call_user_func($onSucess);
    }

    /**
     * respuestaData
     *
     * @param $metadata
     * @param $result
     * @return object Respuesta
     */
    protected function respuestaData($metadata, $result)
    {
        return new Respuesta($metadata, $result);
    }

    /**
     * Obtiene el container Redis
     *
     * @return object
     * @throws \Exception
     */
    protected function getContainerRedis()
    {
        return $this->container->get('snc_redis.default');
    }

    /**
     * getSecurityPassword
     *
     * @return object
     * @throws \Exception
     */
    protected function getSecurityPassword()
    {
        return $this->container->get('security.password_encoder');
    }

    /**
     * Convierte una lista en un arreglo
     *
     * @param $list | lista a convertir
     * @return array
     */
    protected function toArray($list)
    {
        $array = [];
        foreach ($list as $item) {
            $array[] = (array) $item;
        }
        return $array;
    }
}