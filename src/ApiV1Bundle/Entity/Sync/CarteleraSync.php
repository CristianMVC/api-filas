<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\CarteleraRepository;
use ApiV1Bundle\Entity\Validator\CarteleraValidator;


/**
 * Class CarteleraSync
 * @package ApiV1Bundle\Entity\Sync
 */

class CarteleraSync
{
    /** @var PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var ColaRepository $colaRepository */
    private $colaRepository;

    /** @var CarteleraRepository $carteleraRepository */
    private $carteleraRepository;

    /** @var CarteleraValidator $carteleraValidator */
    private $carteleraValidator;

    /**
     * CarteleraSync constructor
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
     * Editar una cartelera
     *
     * @param integer $id id de cartelera
     * @param array $params datos de la cartelera
     * @param object $puntoAtencion Punto de atención
     * @return ValidateResultado
     */
    public function edit($id, $params, $puntoAtencion)
    {
        $cartelera = $this->carteleraRepository->find($id);
        $validateResultado = $this->carteleraValidator->validarEdit($cartelera, $params, $puntoAtencion);
        if (! $validateResultado->hasError()) {
            $cartelera->setNombre($params['nombre']);
            $cartelera->clearColas();
            foreach ($params['colas'] as $colaId) {
                $cola = $this->colaRepository->find($colaId);
                if ($cola) {
                    $cartelera->addCola($cola);
                }
            }
            $validateResultado->setEntity($cartelera);
        }
        return $validateResultado;
    }

    /**
     * Asignar colas a la cartelera
     *
     * @param $cartelera | Cartelera a asignar colas
     * @param $colas | Colas a ser asignadas
     * @return ValidateResultado
     */
    private function asignarColas($cartelera, $colas)
    {
        $errors = [];
        foreach ($colas as $colaId) {
            $cola = $this->colaRepository->find($colaId);
            if ($cola) {
                if ($cola->getPuntoAtencion()!= $cartelera->getPuntoAtencion()){
                    $errors[] = 'La cola con ID: ' . $colaId . ' no pertenece al punto de atención con ID:' . $cartelera->getPuntoAtencion()->getId();
                }else {
                    $cartelera->addCola($cola);
                }
            }else{
                $errors[] = 'La cola con ID: ' . $colaId . ' no existe';
            }
        }
        if (count($errors)) {
            return new ValidateResultado(null, $errors);
        }
        return new ValidateResultado($cartelera, []);
    }

    /**
     * Borra una cartelera
     *
     * @param integer $id Identificador único de la cartelera
     * @param object $puntoAtencionLogueado El punto de atención del usuario logueado
     * @return ValidateResultado
     */
    public function delete($id, $puntoAtencionLogueado)
    {
        $cartelera = $this->carteleraRepository->find($id);
        $validateResultado = $this->carteleraValidator->validarCartelera($cartelera);
        if (! $validateResultado->hasError()) {
            $validateResultado = $this->carteleraValidator->validarPermiso($cartelera, $puntoAtencionLogueado);
            if (! $validateResultado->hasError()) {
                $cartelera->clearColas();
                $validateResultado->setEntity($cartelera);
            }
        }
        return $validateResultado;
    }
}
