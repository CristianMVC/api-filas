<?php

namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Interfaces\UsuarioFactoryInterface;
use ApiV1Bundle\Entity\Admin;
use ApiV1Bundle\Entity\Responsable;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;

/**
 * Class AdminFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class AdminFactory extends UsuarioFactory implements UsuarioFactoryInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator */
    private $userValidator;

    /**
     * ResponsableFactory constructor
     *
     * @param UserValidator $userValidator
     */
    public function __construct(
        UserValidator $userValidator)
    {
        $this->userValidator = $userValidator;
    }

    /**
     * Crea un admin
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {

        $validateResultado = $this->userValidator->validarParamsAdmin($params);

        if (! $validateResultado->hasError()) {

            $user = new User(
                $params['username'],
                $params['rol']
            );

            $admin = new Admin(
                $params['nombre'],
                $params['apellido'],
                $user
            );

            $validateResultado->setEntity($admin);
        }

        return $validateResultado;
    }
}
