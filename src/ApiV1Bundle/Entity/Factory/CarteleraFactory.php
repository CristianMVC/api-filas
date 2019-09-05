<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Entity\Cartelera;
use ApiV1Bundle\Repository\CarteleraRepository;
use ApiV1Bundle\Entity\Validator\CarteleraValidator;
use ApiV1Bundle\Helper\ServicesHelper;

/**
 * Class CarteleraFactory
 * @package ApiV1Bundle\Entity\Factory
 */

class CarteleraFactory
{
    private $puntoAtencionRepository;
    private $colaRepository;
    private $carteleraRepository;
    private $carteleraValidator;

    /**
     * CarteleraFactory constructor
     *
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param ColaRepository $colaRepository
     * @param CarteleraRepository $carteleraRepository
     * @param CarteleraValidator $carteleraValidator
     */
    public function __construct(
        PuntoAtencionRepository $puntoAtencionRepository,
        ColaRepository $colaRepository,
        CarteleraRepository $carteleraRepository,
        CarteleraValidator $carteleraValidator
    ) {
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->colaRepository = $colaRepository;
        $this->carteleraRepository = $carteleraRepository;
        $this->carteleraValidator = $carteleraValidator;
    }

    /**
     * Crear una cartelera
     *
     * @param array $params array con los datos
     * @param object $puntoAtencion Punto de atención
     *
     * @return ValidateResultado
     */
    public function create($params, $puntoAtencion)
    {
        $validateResultado = $this->carteleraValidator->validarParams($params, $puntoAtencion);
        if(! $validateResultado->hasError()) {
            $cartelera = new Cartelera($puntoAtencion, $params['nombre']);
            foreach ($params['colas'] as $colaId) {
                $cola = $this->colaRepository->find($colaId);
                if ($cola) {
                    $cartelera->addCola($cola);
                }
            }
            if (! $validateResultado->hasError()) {
                return new ValidateResultado($cartelera, []);
            }
        }
        return $validateResultado;
    }

}
