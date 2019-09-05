<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Interfaces\UsuarioFactoryInterface;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\AdminRepository;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\ResponsableRepository;

/**
 * Class UsuarioFactoryStrategy
 * @package ApiV1Bundle\Entity\Factory
 */
class UsuarioFactoryStrategy implements UsuarioFactoryInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator  */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Repository\AdminRepository $adminRepository  */
    private $adminRepository;

    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository  */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository $responsableRepository  */
    private $responsableRepository;

    /** @var AdminFactory|AgenteFactory|ResponsableFactory $factory */
    private $factory;

    /** @var ResponsableRepository|AdminRepository|AgenteRepository $repository */
    private $repository;

    /*
     * UsuarioFactoryStrategy construct
     * @param UserValidator $userValidator
     * @param VentanillaRepository $ventanillaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param AdminRepository $adminRepository
     * @param AgenteRepository $agenteRepository
     * @param ResponsableRepository $responsableRepository
     * @param integer $userType
     */
    public function __construct(
        UserValidator $userValidator,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        AdminRepository $adminRepository,
        AgenteRepository $agenteRepository,
        ResponsableRepository $responsableRepository,
        $userType
    ) {
        $this->userValidator = $userValidator;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->adminRepository = $adminRepository;
        $this->agenteRepository = $agenteRepository;
        $this->responsableRepository = $responsableRepository;
        $this->factory = $this->setFactory($userType);
    }

    /**
     * Crea nuevo usuario
     *
     * {@inheritDoc}
     * @see \ApiV1Bundle\Entity\Interfaces\UsuarioInterface::create()
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {
        return $this->factory->create($params);
    }

    /**
     * Obtiene el repositorio
     *
     * @return \ApiV1Bundle\Repository\ResponsableRepository|\ApiV1Bundle\Repository\AdminRepository|\ApiV1Bundle\Repository\AgenteRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Encriptamos el password del usuario para poder guardarlo en la base de datos
     *
     * @param $user
     * @param $encoder
     */
    public function securityPassword($user, $encoder)
    {
        $this->factory->securityPassword($user, $encoder);
    }

    /**
     * Seteamos el factory de acuerdo al tipo de usuario
     *
     * @param $userType
     * @return AdminFactory|AgenteFactory|ResponsableFactory
     */
    private function setFactory($userType)
    {
        switch ($userType) {
            case User::ROL_ADMIN:
                return $this->adminFactorySetup();
                break;
            case User::ROL_AGENTE:
                return $this->agenteFactorySetup();
                break;
            case User::ROL_RESPONSABLE:
                return $this->responsableFactorySetup();
                break;
        }
    }

    /**
     * Factory de los usuarios tipo admin
     *
     * @return \ApiV1Bundle\Entity\Factory\AdminFactory
     */
    private function adminFactorySetup()
    {
        $this->repository = $this->adminRepository;
        $factory = new AdminFactory($this->userValidator);
        return $factory;
    }

    /**
     * Factory de los usuarios tipo agente
     *
     * @return \ApiV1Bundle\Entity\Factory\AgenteFactory
     */
    private function agenteFactorySetup()
    {
        $this->repository = $this->agenteRepository;
        $factory = new AgenteFactory(
            $this->userValidator,
            $this->ventanillaRepository,
            $this->puntoAtencionRepository
        );
        return $factory;
    }

    /**
     * Factory de los usuarios tipo responsable
     *
     * @return \ApiV1Bundle\Entity\Factory\ResponsableFactory
     */
    private function responsableFactorySetup()
    {
        $this->repository = $this->responsableRepository;
        $factory = new ResponsableFactory($this->userValidator, $this->puntoAtencionRepository);
        return $factory;
    }
}
