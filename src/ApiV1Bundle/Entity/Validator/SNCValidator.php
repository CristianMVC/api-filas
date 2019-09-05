<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Helper\ServicesHelper;

/**
 * Class SNCValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class SNCValidator
{
    const CAMPO_REQUERIDO = 'Es un campo requerido';
    const CAMPO_NO_EXISTE = 'El campo no existe para poder ser validado';
    const NUMERICO = 'Debe ser un valor numérico';
    const MATRIZ = 'Debe ser del tipo array.';
    const EMAIL = 'Debe ser una dirección de mail valida';
    const FECHA = 'Debe ser una fecha valida';
    const HORA = 'Debe ser una hora valida';
    const CUIL = 'Debe ser un cuil válido';
    const JSON = 'Debe ser un objeto JSON valido';

    /**
     * Valida campos según las reglas
     *
     * @param array $campos
     * @param array $reglas
     * @return array
     */
    public function validar($campos, $reglas)
    {
        $errores = [];
        foreach ($reglas as $key => $regla) {
            $validaciones = $this->getValidaciones($regla);
            // valido si el campo existe
            if (array_key_exists($key, $campos)) {
                $validacion = $this->validarReglas($validaciones, $campos, $key);
                if (count($validacion)) {
                    $errores[ucfirst($key)] = $validacion;
                }
            } else {
                // es no requerido, lo tenemos que validar
                if (in_array('required', $validaciones)) {
                    $errores[ucfirst($key)] = self::CAMPO_NO_EXISTE;
                }
            }
        }
        return $errores;
    }


    /**
     * Obtiene las reglas de validación de un campo
     *
     * @param string $regla
     * @return array
     */
    private function getValidaciones($regla)
    {
        return explode(':', $regla);
    }

    /**
     * Valida reglas
     *
     * @param array $validaciones
     * @param array $campos
     * @param array $key
     * @return array
     */
    private function validarReglas($validaciones, $campos, $key)
    {
        $errores = [];
        foreach ($validaciones as $validacion) {
            $error = $this->{trim($validacion)}($campos, $key);
            if ($error) {
                return $error;
            }
        }
        return $errores;
    }

    /**
     * Valida si es requerido
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function required($campos, $key)
    {
        if (! isset($campos[$key]) || empty($campos[$key])) {
            return self::CAMPO_REQUERIDO;
        }
        return null;
    }

    /**
     * Valida si es un integro
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function integer($campos, $key)
    {
        $isInt = (bool) filter_var($campos[$key], FILTER_VALIDATE_INT) || (string) $campos[$key] === '0';
        if (! $isInt) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Valida si es numérico
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function numeric($campos, $key)
    {
        if (! is_numeric($campos[$key])) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Valida si es un email
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function email($campos, $key)
    {
        if (! filter_var($campos[$key], FILTER_VALIDATE_EMAIL)) {
            return self::EMAIL;
        }
        return null;
    }

    /**
     * Valida si es un float
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function float($campos, $key)
    {

        if (! filter_var($campos[$key], FILTER_VALIDATE_FLOAT)) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Valida si es una fecha
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function date($campos, $key)
    {
        $format = 'Y-m-d';
        $date = $campos[$key];

        try {
            $d = new \DateTime(trim($date));
        } catch (\Exception $e) {
            return self::FECHA;
        }
        if (! ($d && $d->format($format) == trim($date))) {
            return self::FECHA;
        }
        return null;
    }

    /**
     * Valida si es fecha con zona horaria
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function dateTZ($campos, $key)
    {
        $date = $campos[$key];
        $d = new \DateTime(trim($date));
        if (! ($d && $this->formatDateTZ($d) == trim($date))) {
            return self::FECHA;
        }
        return null;
    }

    /**
     * Valida si es una hora valida
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function time($campos, $key)
    {
        $format = 'H:i';
        $time = $campos[$key];
        $d = new \DateTime(trim($time));
        if (! ($d && $d->format($format) == trim($time))) {
            return self::HORA;
        }
        return null;
    }

    /**
     * Formato de fecha con timezone
     *
     * @param \Datetime $date
     * @return string
     */
    private function formatDateTZ($date)
    {
        return $date->format('Y-m-d\TH:i:s') . '.' . substr($date->format('u'), 0, 3) . 'Z';
    }

    /**
     * Valida si el texto es JSON
     *
     * @param array $campos
     * @param $key
     * @return number|NULL
     */
    private function json($campos, $key)
    {
        // this is probably a JSON object already decoded
        if (is_array($campos[$key])) {
            return null;
        }
        if (is_string($campos[$key]) && is_null(json_decode($campos[$key]))) {
            return self::JSON;
        }
        return null;
    }

    /**
     * Valida formato de CUIL
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function cuil($campos, $key)
    {
        $cuil = str_replace('-', '', $campos[$key]);
        $digitos = str_split($cuil);
        $digitoVerificador = array_pop($digitos);
        $cuilLen = strlen($cuil);
        if ($cuilLen === 10 || $cuilLen === 11) {
            $acumulado = 0;
            $diff = ($cuilLen == 11) ? 9 : 8;
            for ($i = 0; $i < count($digitos); $i++) {
                $acumulado += $digitos[$diff - $i] * (2 + ($i % 6));
            }
            $verif = 11 - ($acumulado % 11);
            $verificacion = ($verif == 11) ? 0 : $verif;
            if ($digitoVerificador == $verificacion) {
                return null;
            }
        }
        return self::CUIL;
    }

    /**
     * Valida si es un array
     *
     * @param array $campos
     * @param $key
     * @return string|NULL
     */
    private function matriz($campos, $key)
    {
        if (! is_array(ServicesHelper::toArray($campos[$key]))) {
            return self::MATRIZ;
        }
        return null;
    }

    /**
     * Valida un Agente
     * @param $agente
     * @return ValidateResultado
     */
    public function validarAgente($agente)
    {
        $errors = [];
        if (! $agente) {
            $errors['agente'] = 'Agente inexistente';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida Ventanilla
     * @param $ventanilla
     * @return ValidateResultado
     */
    public function validarVentanilla($ventanilla)
    {
        $errors = [];
        if (! $ventanilla) {
            $errors['Ventanilla'] = "Ventanilla inexistente.";
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validar Cartelera
     * @param $cartelera
     * @return ValidateResultado
     */
    public function validarCartelera($cartelera)
    {
        $errors = [];
        if (! $cartelera) {
            $errors['Cartelera'] = "Cartelera inexistente.";
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validar User
     * @param $user
     * @return ValidateResultado
     */
    public function validarUser($user)
    {
        $errors = [];
        if (! $user) {
            $errors['Usuario'] = 'Usuario inexistente';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida Cola
     * @param $cola
     * @return ValidateResultado
     */
    public function validarCola($cola)
    {
        $errors = [];
        if (! $cola) {
            $errors['Cola'] = 'Cola inexistente';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * @param $usuario
     * @return ValidateResultado
     */
    public function validarUsuario($usuario)
    {
        $errors = [];
        if (! $usuario) {
            $errors['Usuario'] = 'Usuario inexistente';
        }
        return new ValidateResultado($usuario, $errors);
    }

    /**
     * Valida Punto de Atencion
     * @param $puntoAtencion
     * @return ValidateResultado
     */
    public function validarPuntoAtencion($puntoAtencion)
    {
        $errors = [];
        if (! $puntoAtencion) {
            $errors['Punto de Atencion'] = 'Punto de atencion inexistente';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida Turno
     * @param $turno
     * @return ValidateResultado
     */
    public function validarTurno($turno)
    {
        $errors = [];
        if (! $turno) {
            $errors['Turno'] = 'Turno inexistente';
        }
        return new ValidateResultado(null, $errors);
    }
}
