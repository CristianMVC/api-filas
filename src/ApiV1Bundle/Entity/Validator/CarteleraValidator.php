<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Repository\ColaRepository;

/**
 * Class CarteleraValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class CarteleraValidator extends SNCValidator
{
    /** @var ColaRepository  */
    private $colaRepository;

    /**
     * CarteleraValidator constructor
     *
     * @param ColaRepository $colaRepository
     */
    public function __construct(ColaRepository $colaRepository)
    {
        $this->colaRepository= $colaRepository;
    }


    /**
     * Validación de permisos en la cartelera
     *
     * @param object $cartelera cartelera
     * @param object $puntoAtencionLogueado
     * @return ValidateResultado
     */
    public function validarPermiso($cartelera, $puntoAtencionLogueado) {
        if ( $puntoAtencionLogueado != $cartelera->getPuntoAtencion()){
            $error["403"] = "El usuario no tiene acceso a esta cartelera";
            return new ValidateResultado(null,$error);
        }
        return new ValidateResultado(null,[]);
    }

    /**
     * Validación de parametros para editar cartelera
     *
     * @param object $cartelera cartelera
     * @param array $params
     * @return ValidateResultado
     */
    public function validarEdit($cartelera, $params, $puntoAtencionLogueado) {
        $validateResultado = $this->validarCartelera($cartelera);
        if (! $validateResultado->hasError()) {
            $validateResultado = $this->validarPermiso($cartelera, $puntoAtencionLogueado);
            if (! $validateResultado->hasError()) {
                $validateResultado = $this->validarParams($params, $cartelera->getPuntoAtencion());
            }
        }
        return $validateResultado;
    }

    /**
     * Validar parámetros para crear y editar cartelera (nombre, punto de atención y lista de colas)
     *
     * @param array $params
     * @param $puntoAtencion
     * @return ValidateResultado
     */
    public function validarParams($params, $puntoAtencion)
    {
        $validarResultado = $this->validarPuntoAtencion($puntoAtencion);
        if (! $validarResultado->hasError()) {
            $errors = $this->validar($params,
                [
                    'nombre' => 'required',
                    'colas' => 'required'
                ]
            );
            if (! count($errors)>0 ) {
                foreach ($params['colas'] as $idCola) {
                    $cola = $this->colaRepository->find($idCola);
                    $validateCola = $this->validarCola($cola);
                    if ($validateCola->hasError()) {
                        return $validateCola;
                    }
                    if ($cola->getPuntoAtencion()!= $puntoAtencion){
                        $errors[] = 'La cola con ID: ' . $cola->getId() . ' no pertenece al punto de atención con ID:' . $puntoAtencion->getId();
                    }
                }
            }
            return new ValidateResultado(null, $errors);
        }
        return $validarResultado;
    }

}
