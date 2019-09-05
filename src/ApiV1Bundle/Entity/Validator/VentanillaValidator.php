<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Entity\Ventanilla;

/**
 * Class VentanillaValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class VentanillaValidator extends SNCValidator
{
    private $colaRepository;

    public function __construct(ColaRepository $colaRepository)
    {
        $this->colaRepository= $colaRepository;
    }

    /**
     * Valida los parámetros para la creación o edición de una ventanilla
     *
     * @param array $params Datos de la ventanilla
     * @param object $puntoAtencion Punto de atención
     * @return ValidateResultado
     */
    public function validarParams($params, $puntoAtencion) {
        $errors = $this->validar($params, [
            'puntoAtencion' => 'required',
            'identificador' => 'required'
        ]);

        if (isset($params['colas'])) {
            foreach ($params['colas'] as $idCola) {
                $cola = $this->colaRepository->find($idCola);
                $validateCola = $this->validarCola($cola);
                if ($validateCola->hasError()) {
                    return $validateCola;
                }
            }

        }

        if (! count($errors) > 0) {
            return $this->validarPuntoAtencion($puntoAtencion);
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Validación del endpoint edit Ventanilla
     * @param object $ventanilla Ventanilla
     * @param array $params Datos de la ventanilla
     * @return ValidateResultado
     */
    public function validarEdit($ventanilla, $params) {
        $validateResultado = $this->validarVentanilla($ventanilla);

        if (! $validateResultado->hasError()) {
            $errors = $this->validar($params, [
                'identificador' => 'required',
                'colas' => 'required:matriz'
            ]);

            return new ValidateResultado(null, $errors);
        }

        return $validateResultado;
    }

    /**
     * Valida que la ventanilla no se este utilizando
     * @param object $ventanilla Datos de la ventanilla
     * @param $cantidadAgentes
     * @return ValidateResultado
     */
    public function validarDelete($ventanilla,$cantidadAgentes) {
        $errors = [];
        $validateResultado = $this->validarVentanilla($ventanilla);

        if (! $validateResultado->hasError()) {
            if ($cantidadAgentes>0){
                $errors[] = "No se puede eliminar la ventanilla, debido a que está siendo utilizada por un agente";
                return new ValidateResultado(null, $errors);
            }
            return new ValidateResultado(null, $errors);
        }
        return $validateResultado;
    }
}
