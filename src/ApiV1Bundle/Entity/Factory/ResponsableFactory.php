<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Responsable;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Entity\Interfaces\UsuarioFactoryInterface;

/**
 * Class ResponsableFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class ResponsableFactory extends UsuarioFactory implements UsuarioFactoryInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator  */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /**
     * ResponsableFactory constructor
     *
     * @param UserValidator $userValidator
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        UserValidator $userValidator,
        PuntoAtencionRepository $puntoAtencionRepository
    )
    {
        $this->userValidator = $userValidator;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * Crear un responsable
     * @param array $params Array con los datos para crear el user responsable
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {
        $puntoAtencion = $this->puntoAtencionRepository->find($params['puntoAtencion']);
        $validateResultado = $this->userValidator->validarParamsResponsable($params, $puntoAtencion);

        if (! $validateResultado->hasError()) {

            $user = new User(
                $params['username'],
                $params['rol']
            );

            $responsable = new Responsable(
                $params['nombre'],
                $params['apellido'],
                $puntoAtencion,
                $user
            );

            $validateResultado->setEntity($responsable);
        }

        return $validateResultado;
    }
}