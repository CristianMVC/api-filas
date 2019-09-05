<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\ApplicationServices\RedisServices;
use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Repository\PuntoAtencionRepository;

/**
 * Class ColaValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class ColaValidator extends SNCValidator
{
    /** @var RedisServices $redisServices */
    private $redisServices;

    /**
     * ColaValidator constructor.
     * @param RedisServices $redisServices
     */
    public function __construct(RedisServices $redisServices)
    {
        $this->redisServices = $redisServices;
    }

    /**
     * Valida punto de atención
     *
     * @param $puntoAtencion | Punto de atención a verificar
     * @return ValidateResultado
     */
    public function validarParamsGet($puntoAtencion){
        return $this->validarPuntoAtencion($puntoAtencion);
    }

    /**
     * Valida crear cola grupo trámite
     *
     * @param array $params arreglo con los datos a validar
     * @param PuntoAtencion $puntoAtencion
     * @return ValidateResultado
     */
    public function validarCreateByGrupoTramite($params, $puntoAtencion)
    {
        $errors = $this->validar($params, [
            'nombre' => 'required',
            'puntoAtencion' => 'required',
            'grupoTramite' => 'required:integer'
        ]);

        if (! count($errors) > 0 ) {
            return $this->validarPuntoAtencion($puntoAtencion);
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida editar cola grupo trámite
     *
     * @param array $params arreglo con los datos a validar
     * @param Cola $cola
     * @return ValidateResultado
     */
    public function validarEdit($params, $cola)
    {
        $errors = $this->validar($params, [
            'nombre' => 'required'
        ]);

        if (! count($errors) > 0) {
            return $this->validarCola($cola);
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida crear cola posta
     *
     * @param array $params arreglo con los datos a validar
     * @param PuntoAtencion $puntoAtencion
     * @return ValidateResultado
     */
    public function validarCreateByPosta($params, $puntoAtencion)
    {
        $errors = $this->validar($params, [
            'nombre' => 'required',
            'puntoAtencion' => 'required',
        ]);

        if (! count($errors) > 0 ) {
            return $this->validarPuntoAtencion($puntoAtencion);
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida los parámetros requeridos para eliminar una cola
     *
     * @param $cola
     * @return ValidateResultado
     * @throws \Exception
     */
    public function validarDelete($cola)
    {
        $validateResultado = $this->validarCola($cola);

        if (! $validateResultado->hasError()) {
            if (! empty($this->redisServices->getTotalCola($cola->getPuntoAtencion()->getId(), $cola->getId()))){
                $errors[] = 'La cola no puede ser eliminado ya que tiene turnos asignados';
                return new ValidateResultado(null, $errors);
            }
        }

        return $validateResultado;
    }

    /**
     * Valida los parámetros requeridos para obtener la cántidad de turnos delante
     *
     * @param array $params Datos requeridos para la petición
     * @return ValidateResultado
     */
    public function validarIntegrationGetDelante($params)
    {
        $errors = $this->validar($params, [
            'puntoatencion' => 'required:integer',
            'grupo_tramite' => 'required:integer'
        ]);

        return new ValidateResultado(null, $errors);
    }
}