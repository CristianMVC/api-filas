<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Validator\AdminValidator;
use ApiV1Bundle\Entity\Validator\AgenteValidator;
use ApiV1Bundle\Entity\Validator\ResponsableValidator;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Repository\AdminRepository;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ResponsableRepository;
use ApiV1Bundle\Repository\VentanillaRepository;

/**
 * Class UsuarioSyncStrategy
 * @package ApiV1Bundle\Entity\Sync
 */
class UsuarioSyncStrategy implements UsuarioSyncInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator  */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\AdminRepository $adminRepository  */
    private $adminRepository;

    /** @var \ApiV1Bundle\Entity\Validator\AdminValidator $adminValidator  */
    private $adminValidator;

    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository  */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Entity\Validator\AgenteValidator $agenteValidator  */
    private $agenteValidator;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository $responsableRepository  */
    private $responsableRepository;

    /** @var \ApiV1Bundle\Entity\Validator\ResponsableValidator  $responsableValidator  */
    private $responsableValidator;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /** @var AdminSync|AgenteSync|ResponsableSync $sync */
    private $sync;

    /** @var ResponsableRepository|AdminRepository|AgenteRepository $repository */
    private $repository;

    /**
     * UsuarioSyncStrategy construct
     * @param UserValidator $userValidator,
     * @param AdminRepository $adminRepository,
     * @param AdminValidator $adminValidator,
     * @param AgenteRepository $agenteRepository,
     * @param AgenteValidator $agenteValidator,
     * @param ResponsableRepository $responsableRepository,
     * @param ResponsableValidator $responsableValidator,
     * @param VentanillaRepository $ventanillaRepository,
     * @param PuntoAtencionRepository $puntoAtencionRepository,
     * @param integer $userRol
     */
    public function __construct(
        UserValidator $userValidator,
        AdminRepository $adminRepository,
        AdminValidator $adminValidator,
        AgenteRepository $agenteRepository,
        AgenteValidator $agenteValidator,
        ResponsableRepository $responsableRepository,
        ResponsableValidator $responsableValidator,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        $userRol
    ) {
        $this->userValidator = $userValidator;
        $this->adminRepository = $adminRepository;
        $this->adminValidator = $adminValidator;
        $this->agenteRepository = $agenteRepository;
        $this->agenteValidator = $agenteValidator;
        $this->responsableRepository = $responsableRepository;
        $this->responsableValidator = $responsableValidator;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->sync = $this->getSync($userRol);
    }

    /**
     * Obtenemos el repositorio
     *
     * @return \ApiV1Bundle\Repository\ResponsableRepository|\ApiV1Bundle\Repository\AdminRepository|\ApiV1Bundle\Repository\AgenteRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Editar usuario
     *
     * {@inheritDoc}
     * @see \ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface::edit()
     * @throws \Doctrine\ORM\ORMException
     */
    public function edit($id, $params)
    {
        return $this->sync->edit($id, $params);
    }

    /**
     * Eliminar usuario
     *
     * {@inheritDoc}
     * @see \ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface::delete()
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($id)
    {
        return $this->sync->delete($id);
    }

    /**
     * Seteamos el sync de acuerdo al tipo de usuario
     *
     * @param $userRol
     * @return \ApiV1Bundle\Entity\Sync\AdminSync|\ApiV1Bundle\Entity\Sync\AgenteSync|\ApiV1Bundle\Entity\Sync\ResponsableSync
     */
    private function getSync($userRol)
    {
        switch ($userRol) {
            case User::ROL_ADMIN:
                return $this->adminSyncSetup();
                break;
            case User::ROL_AGENTE:
                return $this->agenteSyncSetup();
                break;
            case User::ROL_RESPONSABLE:
                return $this->responsableSyncSetup();
                break;
        }
    }

    /**
     * Sync del usuario admin
     *
     * @return \ApiV1Bundle\Entity\Sync\AdminSync
     */
    private function adminSyncSetup()
    {
        $this->repository = $this->adminRepository;
        $sync = new AdminSync(
            $this->userValidator,
            $this->adminValidator,
            $this->adminRepository
        );
        return $sync;
    }

    /**
     * Sync del usuario agente
     *
     * @return \ApiV1Bundle\Entity\Sync\AgenteSync
     */
    private function agenteSyncSetup()
    {
        $this->repository = $this->agenteRepository;
        $sync = new AgenteSync(
            $this->agenteValidator,
            $this->agenteRepository,
            $this->ventanillaRepository,
            $this->puntoAtencionRepository
        );
        return $sync;
    }

    /**
     * Sync del usuario responsable
     *
     * @return \ApiV1Bundle\Entity\Sync\ResponsableSync
     */
    private function responsableSyncSetup()
    {
        $this->repository = $this->responsableRepository;
        $sync = new ResponsableSync(
            $this->userValidator,
            $this->responsableRepository,
            $this->responsableValidator,
            $this->puntoAtencionRepository
        );
        return $sync;
    }
}