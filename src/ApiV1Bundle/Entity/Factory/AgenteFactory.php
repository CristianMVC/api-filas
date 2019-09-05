<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Agente;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Validator\AgenteValidator;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Ventanilla;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Entity\Interfaces\UsuarioFactoryInterface;

/**
 * Class AgenteFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class AgenteFactory extends UsuarioFactory implements UsuarioFactoryInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /**
     * AgenteFactory constructor.
     *
     * @param UserValidator $userValidator
     * @param VentanillaRepository $ventanillaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        UserValidator $userValidator,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository)
    {
        $this->userValidator = $userValidator;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     *  Crea un agente
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoAtencion']);
        $validateResultado = $this->userValidator->validarParamsAgente($params, $puntoAtencion);

        if (! $validateResultado->hasError()) {

            $user = new User(
                $params['username'],
                $params['rol']
            );

            $agente = new Agente(
                $params['nombre'],
                $params['apellido'],
                $puntoAtencion,
                $user
            );
            if (isset($params['ventanillas'])) {
                foreach ($params['ventanillas'] as $idVentanilla) {
                    $ventanilla = $this->ventanillaRepository->find($idVentanilla);
                    $agente->addVentanilla($ventanilla);
                }
            }
            $validateResultado->setEntity($agente);
        }

        return $validateResultado;
    }
}
