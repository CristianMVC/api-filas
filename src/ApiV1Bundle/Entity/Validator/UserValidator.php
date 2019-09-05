<?php

namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Entity\Agente;
use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Entity\Ventanilla;
use ApiV1Bundle\Repository\UserRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Entity\User;

/**
 * Class UserValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class UserValidator extends SNCValidator
{
    /** @var \ApiV1Bundle\Repository\UserRepository UserRepository  */
    private $userRepository;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository VentanillaRepository  */
    private $ventanillaRepository;

    /** @var $encoder */
    private $encoder;

    /**
     * UserValidator construct
     *
     * @param UserRepository $userRepository
     * @param ventanillaRepository $ventanillaRepository
     * @param $encoder
     */
    public function __construct(UserRepository $userRepository, VentanillaRepository $ventanillaRepository, $encoder)
    {
        $this->userRepository = $userRepository;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->encoder = $encoder;
    }

    /**
     * Valida que exista usuario y contraseña
     *
     * @param array $params arreglo con los datos a validar
     * @param User $user
     * @return ValidateResultado
     */
    public function validarParamsLogin($params, $user)
    {
        $errors = $this->validar($params, [
            'username' => 'required',
            'password' => 'required'
        ]);
        if (! count($errors) && ! $user) {
            $errors['error'] = 'Usuario/contraseña incorrectos';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida el login del usuario
     *
     * @param string $user
     * @param string $password
     * @return ValidateResultado
     */
    public function validarLogin($user, $password)
    {
        $errors = [];
        if (! $this->encoder->isPasswordValid($user, $password)) {
            $errors['error'] = 'Usuario/contraseña incorrectos';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida el login del usuario al modificar su contraseña
     *
     * @param string $user Nombre de usuario
     * @param string $password Contraseña del usuario
     * @return \ApiV1Bundle\Entity\Validator\ValidateResultado
     */
    public function validarModificarContrasena($user, $password)
    {
        $errors = [];
        if (! $this->encoder->isPasswordValid($user, $password)) {
            $errors['error'] = 'La contraseña actual es incorrecta';
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida crear usuario
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarCreate($params)
    {
        $rules = [
            'rol' => 'required',
            'username' => 'required:email',
            'nombre' => 'required'
        ];

        if (isset($params['rol']) && $params['rol'] == User::ROL_AGENTE ) {
            $rules['ventanillas'] = 'matriz';
        }

        $errors = $this->validar($params, $rules);

        if (!count($errors)) {
            $user = $this->userRepository->findOneByUsername($params['username']);
            if ($user) {
                return new ValidateResultado(null, ['Ya existe un usuario registrado con ese email']);
            }
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida parámetros de creación y edición de usuarios
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    private function validarParams($params)
    {
        $errors = $this->validar($params, [
            'username' => 'required:email',
            'rol' => 'required:integer'
        ]);

        if (! count($errors) > 0) {
            $user = $this->userRepository->findOneByUsername($params['username']);
            if ($user) {
                $errors['User'] = 'Ya existe un usuario con el email ingresado';
                return new ValidateResultado(null, $errors);
            }

            if (! in_array((int) $params['rol'], [1,2,3,4,5], true)) {
                $errors['Rol'] = 'Rol inexistente.';
                return new ValidateResultado(null, $errors);
            }
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida parámetros para la creación de un Agente
     *
     * @param array $params arreglo con los datos a validar
     * @param PuntoAtencion $puntoAtencion
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarParamsAgente($params, $puntoAtencion)
    {
        $validateResultado = $this->validarParams($params);

        if (! $validateResultado->hasError()) {
            $errors = $this->validar($params, [
                'nombre' => 'required',
                'apellido' => 'required',
                'puntoAtencion' => 'required:integer',
                'ventanillas' => 'matriz'
            ]);
            if (isset($params['ventanillas'])) {
                foreach ($params['ventanillas'] as $idVentanilla) {
                    $ventanilla = $this->ventanillaRepository->find($idVentanilla);
                    $validateVentanilla = $this->validarVentanilla($ventanilla);
                    if ($validateVentanilla->hasError()) {
                        return $validateVentanilla;
                    }
                    if ($ventanilla->getPuntoAtencion()->getId()!= $puntoAtencion->getId()){
                        $errors[] = 'La ventanilla con ID: ' . $ventanilla->getId() . ' no pertenece al Punto de Atención con ID: ' . $puntoAtencion->getId();
                    }
                }
            }
            if (! count($errors) > 0) {
                return $this->validarPuntoAtencion($puntoAtencion);
            }
            return new ValidateResultado(null, $errors);
        }
        return $validateResultado;
    }

    /**
     * Valida asignación de ventanilla a agente
     *
     * @param Agente $agente
     * @param Ventanilla $ventanilla
     * @return ValidateResultado
     */
    public function validarAsignarVentanilla($agente, $ventanilla)
    {
        $validateResultadoAgente = $this->validarAgente($agente);

        if($validateResultadoAgente->hasError()) {
            return $validateResultadoAgente;
        }

        $validateResultadoVentanilla = $this->validarVentanilla($ventanilla);

        if($validateResultadoVentanilla->hasError()) {
            return $validateResultadoVentanilla;
        }

        return new ValidateResultado(null, []);
    }

    /**
     * Valida parametros para la creación de un usuario responsable
     * @param array $params arreglo con los datos a validar
     * @param object $puntoAtencion Punto de atención
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarParamsResponsable($params, $puntoAtencion)
    {
        $validateResultado = $this->validarParams($params);

        if (! $validateResultado->hasError()) {
            $errors = $this->validar($params, [
                'nombre' => 'required',
                'apellido' => 'required',
                'puntoAtencion' => 'required:integer'
            ]);

            if (! count($errors) > 0) {
                return $this->validarPuntoAtencion($puntoAtencion);
            }

            return new ValidateResultado(null, $errors);
        }

        return $validateResultado;
    }

    /**
     * Valida parametros para la creación de un admin
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarParamsAdmin($params)
    {
        $validateResultado = $this->validarParams($params);

        if (! $validateResultado->hasError()) {
            $errors = $this->validar($params, [
                'nombre' => 'required',
                'apellido' => 'required'
            ]);

            return new ValidateResultado(null, $errors);
        }

        return $validateResultado;
    }

    /**
     * Comprueba si una contraseña es válida
     *
     * @param string $password
     * @return bool
     */
    private function isValidPassword($password){
        if (strlen($password) >= 8 && strlen($password) <= 15) {
            if (preg_match('/^[a-zA-Z0-9]+/', $password)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valida usuario y contraseña para modificar contraseña
     *
     * @param array $params arreglo con los datos a validar
     * @return \ApiV1Bundle\Entity\Validator\ValidateResultado
     */
    public function validarModificarPassword($params)
    {
        $errors = [];
        $user = $this->userRepository->findOneByUsername($params['username']);
        $validateResult = $this->validarUsuario($user);
        if ($validateResult->hasError()) {
            return $validateResult;
        }

        if(! $this->isValidPassword($params['nuevoPassword'])) {
            $errors['Password'] = 'La contraseña no es válida. Debe tener entre 8 y 15 caracteres alfanuméricos';
            return new ValidateResultado(null, $errors);
        }

        return new ValidateResultado($user, []);
    }
}
