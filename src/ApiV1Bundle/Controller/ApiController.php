<?php
namespace ApiV1Bundle\Controller;

use ApiV1Bundle\Entity\Response\Respuesta;
use ApiV1Bundle\Entity\Response\RespuestaConEstado;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class ApiController
 * @package ApiV1Bundle\Controller
 *
 * Clase base de los controladores de la API
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
class ApiController extends FOSRestController
{

    /**
     * Obtiene Login service
     *
     * @return object
     */
    protected function getSecurityServices()
    {
        return $this->container->get('snc.services.security');
    }

    /**
     * Obtiene Agente service
     *
     * @return object
     */
    protected function getAgenteServices()
    {
        return $this->container->get('snc.services.agente');
    }

    /**
     * Obtiene Ventanilla service
     *
     * @return object
     */
    protected function getVentanillaServices()
    {
        return $this->container->get('snc.services.ventanilla');
    }

    /**
     * Obtiene Cartelera Services
     *
     * @return object
     */
    protected function getCarteleraServices()
    {
        return $this->container->get('snc.services.cartelera');
    }

    /**
     * Obtiene Punto Atencion services
     *
     * @return object
     */
    protected function getPuntoAtencionServices()
    {
        return $this->container->get('snc.services.puntoatencion');
    }

    /**
     * Obtiene Usuario service
     *
     * @return object
     */
    protected function getUsuarioServices()
    {
        return $this->container->get('snc.services.usuario');
    }

    /**
     * Obtiene Cola service
     *
     * @return object
     */
    protected function getColasServices()
    {
        return $this->container->get('snc.services.cola');
    }

    /**
     * Obtiene Cola service
     *
     * @return object
     */
    protected function getTurnoServices()
    {
        return $this->container->get('snc.services.turno');
    }

    /**
     * Retorna una Respuesta con estado SUCCESS
     *
     * @param array $message Mensaje de éxito
     * @param string $additional
     * @return RespuestaConEstado
     */
    protected function respuestaOk($message, $additional = '')
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_SUCCESS,
            RespuestaConEstado::CODE_SUCCESS,
            $message,
            '',
            $additional
        );
    }

    /**
     * Retorna una Respuesta con estado FATAL
     *
     * @param array $message Mensaje Fatal
     * @return RespuestaConEstado
     */
    protected function respuestaError($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_FATAL,
            RespuestaConEstado::CODE_FATAL,
            $message,
            '',
            ''
        );
    }

    /**
     * Retorna una Respuesta con estado Not Found
     *
     * @param array $message Mensaje No encontrado
     * @return RespuestaConEstado
     */
    protected function respuestaNotFound($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_NOT_FOUND,
            RespuestaConEstado::CODE_NOT_FOUND,
            $message
        );
    }

    /**
     * Retorna una Respuesta con estado Bad Request
     *
     * @param array $message Mensaje respuesta errónea
     * @return RespuestaConEstado
     */
    protected function respuestaBadRequest($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_BAD_REQUEST,
            RespuestaConEstado::CODE_BAD_REQUEST,
            $message
        );
    }

    /**
     * Retorna una Respuesta con estado Forbidden
     *
     * @param $message
     * @return \ApiV1Bundle\Entity\Response\RespuestaConEstado
     */
    protected function respuestaForbiddenRequest($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_FORBIDDEN,
            RespuestaConEstado::CODE_FORBIDDEN,
            $message
        );
    }

    /**
     * Retorna una Respuesta con datos
     *
     * @param $metadata
     * @param $result
     * @return \ApiV1Bundle\Entity\Response\Respuesta
     */
    protected function respuestaData($metadata, $result)
    {
        return new Respuesta($metadata, $result);
    }

    protected function getContainerRedis()
    {
        return $this->container->get('snc_redis.default');
    }

    /**
     * Obtiene PuntoAtencion service
     *
     * @return object
     */
    protected function getPuntosAtencionService()
    {
        return $this->container->get('snc.services.puntoatencion');
    }
}
