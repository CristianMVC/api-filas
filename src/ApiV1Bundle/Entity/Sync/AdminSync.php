<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Admin;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\AdminValidator;
use ApiV1Bundle\Repository\AdminRepository;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface;

/**
 * Class AdminSync
 * @package ApiV1Bundle\Entity\Sync
 */
class AdminSync implements UsuarioSyncInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator  */
    private $userValidator;

    /** @var \ApiV1Bundle\Entity\Validator\AdminValidator $adminValidator  */
    private $adminValidator;

    /** @var \ApiV1Bundle\Repository\AdminRepository $adminRepository  */
    private $adminRepository;

    /**
     * AdminSync constructor
     *
     * @param UserValidator $userValidator
     * @param AdminValidator $adminValidator
     * @param AdminRepository $adminRepository
     */
    public function __construct(
        UserValidator $userValidator,
        AdminValidator $adminValidator,
        AdminRepository $adminRepository)
    {
        $this->userValidator = $userValidator;
        $this->adminValidator = $adminValidator;
        $this->adminRepository = $adminRepository;
    }

    /**
     * Editar Admin
     *
     * @param integer $id Identificador único del admin
     * @param array $params arreglo con los datos para editar el admin
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function edit($id, $params)
    {
        $validateResultado = $this->adminValidator->validarParams($params);

        if (! $validateResultado->hasError()) {

            $admin = $this->adminRepository->findOneByUser($id);
            $user = $admin->getUser();

            $admin->setNombre($params['nombre']);
            $admin->setApellido($params['apellido']);

            if (isset($params['username'])) {
                $user->setUsername($params['username']);
            }

            if (isset($params['password'])) {
                $user->setPassword($params['password']);
            }

            $validateResultado->setEntity($admin);
        }

        return $validateResultado;
    }

    /**
     * Borra un admin
     *
     * @param integer $id Identificador único del admin
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($id)
    {
        $admin = $this->adminRepository->findOneByUser($id);

        $validateResultado = $this->userValidator->validarUsuario($admin);

        if (! $validateResultado->hasError()) {
            $validateResultado->setEntity($admin);
            return $validateResultado;
        }
        return $validateResultado;
    }
}
