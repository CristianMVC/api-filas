<?php
namespace ApiV1Bundle\Entity;

use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\ResponsableRepository;
use ApiV1Bundle\Repository\AdminRepository;

/**
 * Class UsuarioStrategy
 * @package ApiV1Bundle\Entity
 */
class UsuarioStrategy
{
    /* @var \ApiV1Bundle\Repository\AgenteRepository AgenteRepository */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository ResponsableRepository  */
    private $responsableRepository;

    /** @var \ApiV1Bundle\Repository\AdminRepository AdminRepository  */
    private $adminRepository;

    /**
     * UsuarioStrategy constructor
     *
     * @param AgenteRepository $agenteRepository
     * @param ResponsableRepository $responsableRepository
     * @param AdminRepository $adminRepository
     */
    public function __construct(
        AgenteRepository $agenteRepository,
        ResponsableRepository $responsableRepository,
        AdminRepository $adminRepository
    ) {
        $this->agenteRepository = $agenteRepository;
        $this->responsableRepository = $responsableRepository;
        $this->adminRepository = $adminRepository;
    }

    /**
     * Obtenemos los datos del usuario
     *
     * @param \ApiV1Bundle\Entity\User $user
     * @return array
     */
    public function getUser($user)
    {
        $repository = $this->getRepository($user->getRol());
        $usuario = $repository->findOneByUser($user);
        return $this->getUserData($usuario, $user->getRol());
    }

    /**
     * Datos básicos del usuario
     *
     * @param \ApiV1Bundle\Entity\Usuario $usuario
     * @param integer $userRol
     * @return array
     */
    private function getUserData($usuario, $userRol)
    {
        $userData = [
            'id' => $usuario->getUser()->getId(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'username' => $usuario->getUser()->getUsername(),
            'rol' => $usuario->getUser()->getRol()
        ];
        $userData = array_merge($userData, $this->userDataByRol($usuario, $userRol));
        return $userData;
    }

    /**
     * Obtenemos el repositorio de acuerdo al tipo de usuario
     *
     * @param integer $userRol
     * @return repository
     */
    private function getRepository($userRol)
    {
        switch ($userRol) {
            case User::ROL_ADMIN:
                return $this->adminRepository;
                break;
            case User::ROL_AGENTE:
                return $this->agenteRepository;
            case User::ROL_RESPONSABLE:
                return $this->responsableRepository;
        }
    }

    /**
     * Obtenemos los datos de usuario de acuerdo al tipo de usuario
     *
     * @param \ApiV1Bundle\Entity\Usuario $usuario
     * @param integer $userRol
     * @return repository
     */
    private function userDataByRol($usuario, $userRol)
    {
        switch ($userRol) {
            case User::ROL_ADMIN:
                return $this->adminData($usuario);
                break;
            case User::ROL_AGENTE:
                return $this->agenteData($usuario);
            case User::ROL_RESPONSABLE:
                return $this->responsableData($usuario);
        }
    }

    /**
     * Datos adicionales del admin
     *
     * @param \ApiV1Bundle\Entity\Usuario $usuario
     * @return array
     */
    private function adminData($usuario)
    {
        return [];
    }

    /**
     * Datos adicionales del agente
     *
     * @param \ApiV1Bundle\Entity\Usuario $usuario
     * @return array
     */
    private function agenteData($usuario)
    {
        $data = [
            'puntoAtencion' => [
                'id' => $usuario->getPuntoAtencionIdSnt(),
                'nombre' => $usuario->getNombrePuntoAtencion()
            ],
            'ventanillas' => [],
            'ventanillaActual' => $usuario->getVentanillaActualId()
        ];
        foreach ($usuario->getVentanillas() as $ventanilla) {
            $data['ventanillas'][] = [
                'id' => $ventanilla->getId(),
                'identificador' => $ventanilla->getIdentificador()
            ];
        }
        return $data;
    }

    /**
     * Datos adicionales del responsable
     *
     * @param \ApiV1Bundle\Entity\Usuario $usuario
     * @return array
     */
    private function responsableData($usuario)
    {
        $data = [
            'puntoAtencion' => [
                'id' => $usuario->getPuntoAtencionId(),
                'nombre' => $usuario->getNombrePuntoAtencion(),
                'snt_id' => $usuario->getPuntoAtencionIdSnt()
            ]
        ];
        return $data;
    }
}