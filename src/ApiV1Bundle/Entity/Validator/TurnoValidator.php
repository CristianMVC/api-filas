<?php

namespace ApiV1Bundle\Entity\Validator;
use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\Entity\Ventanilla;


/**
 * Class ResponsableValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class TurnoValidator extends SNCValidator
{
    /**
     * Valida crear turno
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarCreate($params)
    {
        $errors = $this->validar($params, [
            'puntoAtencion' => 'required:integer',
            'tramite' => 'required',
            'grupoTramite' => 'required:integer',
            'fecha' => 'required:dateTZ',
            'hora' =>  'required:time',
            'estado' => 'required',
            'codigo' => 'required',
            'datosTurno' => 'required',
            'prioridad' => 'required:integer'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida crear turno desde SNT
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarCreateIntegration($params)
    {
        $errors = $this->validar($params, [
            'puntoatencion' => 'required:integer',
            'tramite' => 'required',
            'grupo_tramite' => 'required:integer',
            'nombre' => 'required',
            'apellido' => 'required',
            'codigo' => 'required',
            'prioridad' => 'required:integer'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida cambio de estatus
     * @param array $params arreglo con los datos a validar
     * @param object $ventanilla Ventanilla a la que se envía el turno
     * @return ValidateResultado
     */
    public function validarChangeStatus($params, $ventanilla)
    {
        $errors = $this->validar($params, [
            'codigo' => 'required',
            'estado' => 'required:integer',
            'ventanilla' => 'required:integer'
        ]);

        $validateVentanilla = $this->validarVentanilla($ventanilla);

        if ($validateVentanilla->hasError()) {
            return $validateVentanilla;
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida los parámetros para la obtención de turnos del SNT
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarGetSNT($params)
    {
        $errors = $this->validar($params, [
            'fecha' => 'required',
            'puntoatencion' => 'required'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida los parámetros de la búsqueda de turnos
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarSearchSNT($params)
    {
        $errors = $this->validar($params, [
            'codigo' => 'required',
            'puntoatencionid' => 'required:integer'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida los parámetros del turno recepcionado
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarParamsGetRecepcionados($params)
    {
        $errors = $this->validar($params, array(
            'puntoatencion' => 'required:integer',
            'ventanilla' => 'required:integer'
        ));
        return new ValidateResultado(null,$errors);
    }

    /**
     * Validamos el turno recepcionado
     *
     * @param Ventanilla $ventanilla
     * @param Usuario $usuario
     * @param PuntoAtencion $puntoAtencion
     * @return ValidateResultado
     */
    public function validarGetRecepcionados($usuario, $ventanilla, $puntoAtencion)
    {
        $validateResultado = $this->validarVentanilla($ventanilla);
        if (!$validateResultado->hasError()) {
            $validateResultado = $this->validarUsuarioVentanilla($usuario, $ventanilla);
            if (!$validateResultado->hasError()) {
                $validateResultado = $this->validarPuntoAtencion($puntoAtencion);
            }
        }
        return $validateResultado;
    }


    /**
     * Valida que el usuario logueado tenga como ventanilla actual la que se envía por parametro
     *
     * @param object $usuario Usuario logueado
     * @param object $ventanilla Ventanilla a verificar
     * @return ValidateResultado
     */
    private function validarUsuarioVentanilla($usuario, $ventanilla)
    {
        $errors = [];
        if ($usuario->getVentanillaActualId() != $ventanilla->getId()) {
            $errors['UsuarioVentanilla'] = 'El usuario no tiene una ventanilla asignada o no corresponde con la ventanilla actual';
        }
        return new ValidateResultado(null, $errors);
    }
}
