<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\User;

/**
 * Class UsuarioFactory
 * @package ApiV1Bundle\Entity\Factory
 */
abstract class UsuarioFactory
{
    abstract public function create($params);

    /**
     * @param User $user
     * @param $encoder
     * @return mixed
     */
    public function securityPassword($user, $encoder)
    {
        $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
    }
}