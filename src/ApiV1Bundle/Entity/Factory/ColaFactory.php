<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\Validator\ColaValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;

/**
 * Class ColaFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class ColaFactory
{
    /** @var \ApiV1Bundle\Entity\Validator\ColaValidator $colaValidator  */
    private $colaValidator;

    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /**
     * ColaFactory constructor.
     *
     * @param ColaValidator $colaValidator
     * @param ColaRepository $colaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        ColaValidator $colaValidator,
        ColaRepository $colaRepository,
        PuntoAtencionRepository $puntoAtencionRepository
    )
    {
        $this->colaValidator = $colaValidator;
        $this->colaRepository = $colaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     *  Crea una cola tipo tramite
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($params)
    {
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoAtencion']);
        $validateResultado = $this->colaValidator->validarCreateByGrupoTramite($params, $puntoAtencion);

        return $this->createCola($params, $puntoAtencion, Cola::TIPO_GRUPO_TRAMITE, $validateResultado);
    }

    /**
     *  Crea una cola tipo posta
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function createPosta($params)
    {
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoAtencion']);
        $validateResultado = $this->colaValidator->validarCreateByPosta($params, $puntoAtencion);

        return $this->createCola($params, $puntoAtencion, Cola::TIPO_POSTA, $validateResultado);
    }

    /**
     *  Crea una cola
     *
     * @param array $params arreglo con los datos
     * @param \ApiV1Bundle\Entity\PuntoAtencion $puntoAtencion
     * @param integer $tipo TIPO_GRUPO_TRAMITE = 1 | TIPO_POSTA = 2
     * @param \ApiV1Bundle\Entity\Validator\ValidateResultado $validateResultado
     * @return ValidateResultado
     */
    private function createCola($params, $puntoAtencion, $tipo, $validateResultado)
    {
        if (! $validateResultado->hasError()) {

            $cola = new Cola(
                $params['nombre'],
                $puntoAtencion,
                $tipo
            );

            if ($tipo == Cola::TIPO_GRUPO_TRAMITE) {
                $cola->setGrupoTramiteSNTId($params['grupoTramite']);
            }

            $validateResultado->setEntity($cola);
        }

        return $validateResultado;
    }
}