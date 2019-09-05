<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\PuntoAtencionRepository;

/**
 * Class PuntoAtencionValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class PuntoAtencionValidator extends SNCValidator
{
    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository PuntoAtencionRepository  */
    private $puntoAtencionRepository;

    /**
     * PuntoAtencionValidator constuct
     *
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        PuntoAtencionRepository $puntoAtencionRepository
    ) {
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * Valida crear
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarCrear($params)
    {
        $error = [];
        $puntoAtencion = null;

        $error = $this->validar($params, [
            'punto_atencion_id_SNT' => 'required',
            'nombre' => 'required',
        ]);

        if (isset($params['punto_atencion_id_SNT'])) {
            $puntoAtencion = $this->puntoAtencionRepository->findOneBy([
                'puntoAtencionIdSnt' => $params['punto_atencion_id_SNT']
            ]);
        }

        if ($puntoAtencion) {
            $error['PuntoAtencion'] = 'El punto de atención ya existe';
        }
        return new ValidateResultado(null, $error);
    }

    /**
     * Valida campos a editar y punto de atencion
     *
     * @param PuntoAtencion $puntoAtencion del punto de atencion a editar
     * @param  array $params Array de parametros a modificar
     * @return \ApiV1Bundle\Entity\Validator\ValidateResultado
     */
    public function validarEditar($puntoAtencion, $params)
    {
        $error = $this->validar($params, [
            'nombre' => 'required',
        ]);

        if (!count($error)) {
            $validateResult = $this->validarPuntoAtencion($puntoAtencion);

            if (!$validateResult->hasError()) {
                return new ValidateResultado($puntoAtencion, null);
            } else {
                return $validateResult;
            }
        }

        return new ValidateResultado(null, $error);
    }

    /**
     * Valida el borrado de un punto de atención
     *
     * @param PuntoAtencion $puntoAtencion Entidad Punto de atencion
     * @return ValidateResultado
     */
    public function validarDelete($puntoAtencion)
    {
        return $this->validarPuntoAtencion($puntoAtencion);
    }
}
