<?php
namespace ApiV1Bundle\Entity\Factory;


use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Validator\VentanillaValidator;
use ApiV1Bundle\Entity\Ventanilla;

/**
 * Class VentanillaFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class VentanillaFactory
{
    /** @var \ApiV1Bundle\Entity\Validator\VentanillaValidator $ventanillaValidator  */
    private $ventanillaValidator;

    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /**
     * VentanillaFactory construct
     *
     * @param ventanillaValidator $ventanillaValidator
     * @param \ApiV1Bundle\Repository\ColaRepository $colaRepository
     * @param \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(VentanillaValidator $ventanillaValidator, $colaRepository, $puntoAtencionRepository)
    {
        $this->ventanillaValidator = $ventanillaValidator;
        $this->colaRepository = $colaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * Crea ventanilla
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoAtencion']);
        $validateResultado = $this->ventanillaValidator->validarParams($params, $puntoAtencion);

        if (! $validateResultado->hasError()) {

            $ventanilla = new Ventanilla($params['identificador'], $puntoAtencion);

            if (isset($params['colas'])) {
                foreach ($params['colas'] as $colaId) {
                    $cola = $this->colaRepository->find($colaId);
                    $ventanilla->addCola($cola);
                }
            }

            return new ValidateResultado($ventanilla, []);
        }

        return $validateResultado;
    }
}