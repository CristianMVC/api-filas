<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ResponsableRepository;
use ApiV1Bundle\Entity\Validator\ResponsableValidator;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface;

/**
 * Class ResponsableSync
 * @package ApiV1Bundle\Entity\Sync
 */
class ResponsableSync implements UsuarioSyncInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator  */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository $responsableRepository  */
    private $responsableRepository;

    /** @var \ApiV1Bundle\Entity\Validator\ResponsableValidator $responsableValidator  */
    private $responsableValidator;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /**
     * ResponsableSync constructor
     *
     * @param UserValidator $userValidator
     * @param ResponsableRepository $responsableRepository
     * @param ResponsableValidator $responsableValidator
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        UserValidator $userValidator,
        ResponsableRepository $responsableRepository,
        ResponsableValidator $responsableValidator,
        PuntoAtencionRepository $puntoAtencionRepository
    ) {
        $this->userValidator = $userValidator;
        $this->responsableRepository = $responsableRepository;
        $this->responsableValidator = $responsableValidator;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * Edita un responsable
     *
     * @param integer $id Identificador único de responsable
     * @param array $params arreglo con los datos para editar
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function edit($id, $params)
    {
        $validateResultado = $this->responsableValidator->validarParams($id, $params);

        if (! $validateResultado->hasError()) {
            $responsable = $this->responsableRepository->findOneByUser($id);
            $user = $responsable->getUser();

            $responsable->setNombre($params['nombre']);
            $responsable->setApellido($params['apellido']);

            $puntoAtencion = $this->puntoAtencionRepository->find($params['puntoAtencion']);
            $validateResultadoPA = $this->responsableValidator->validarPuntoAtencion($puntoAtencion);

            if ($validateResultadoPA->hasError()) {
                return $validateResultadoPA;
            }
            $responsable->setPuntoAtencion($puntoAtencion);

            if (isset($params['username'])) {
                $user->setUsername($params['username']);
            }

            if (isset($params['password'])) {
                $user->setPassword($params['password']);
            }

            $validateResultado->setEntity($responsable);
        }

        return $validateResultado;
    }

    /**
     * Borra un responsable
     *
     * @param integer $id Identificador único del responsable     *
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($id)
    {
        $responsable = $this->responsableRepository->findOneByUser($id);

        $validateResultado = $this->userValidator->validarUsuario($responsable);

        if (! $validateResultado->hasError()) {
            $validateResultado->setEntity($responsable);
        }

        return $validateResultado;
    }
}
