<?php
namespace ApiV1Bundle\Entity\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use ApiV1Bundle\Repository\VentanillaRepository;

/**
 * Class AgenteValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class AgenteValidator extends SNCValidator
{
    /** @var \ApiV1Bundle\Repository\VentanillaRepository VentanillaRepository  */
    private $ventanillaRepository;

    /**
     * AgenteValidator constructor
     *
     * @param VentanillaRepository $ventanillaRepository
     */
    public function __construct(VentanillaRepository $ventanillaRepository)
    {
        $this->ventanillaRepository = $ventanillaRepository;
    }

    /**
     * Valida Parámetros
     *
     * @param array $params arreglo con los datos a validar
     * @return ValidateResultado
     */
    public function validarParams($params)
    {
        $errors = $this->validar($params, [
            'nombre' => 'required',
            'apellido' => 'required',
            'puntoAtencion' => 'required:integer',
            'ventanillas' => 'matriz'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Validar que un arreglo de ventanillas pertenezca al mismo punto de atención
     *
     * @param array $params arreglo con ventanillas
     * @param object $puntoAtencion El punto de atención
     * @return ValidateResultado
     */
    public function validarVentanillasPuntoAtencion($params, $puntoAtencion)
    {
        $errors = [];
        if (isset($params['ventanillas'])) {
            foreach ($params['ventanillas'] as $idVentanilla) {
                $ventanilla = $this->ventanillaRepository->find($idVentanilla);
                if (!$ventanilla) {
                    $errors['Ventanilla'][] = 'La ventanilla con ID: ' . $idVentanilla . ' no fue encontrada.';
                }else{
                    $validateVentanillaPuntoAtencion = $this->validarVentanillaPuntoAtencion($ventanilla,$puntoAtencion->getId());
                    if ($validateVentanillaPuntoAtencion->hasError() ){
                        return $validateVentanillaPuntoAtencion;
                    }
                }
            }
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida asignar ventanilla
     *
     * @param \ApiV1Bundle\Entity\Agente $agente
     * @param integer $cantidadAgentes
     * @param \ApiV1Bundle\Entity\Ventanilla $ventanilla
     * @return ValidateResultado
     */
    public function validarAsignarVentanilla($agente, $cantidadAgentes, $ventanilla)
    {
        $validateResultadoAgente = $this->validarAgente($agente);
        if ($validateResultadoAgente->hasError()) {
            return $validateResultadoAgente;
        }

        $validateResultadoAgenteVentanilla = $this->validarAgenteVentanilla($cantidadAgentes);
        if ($validateResultadoAgenteVentanilla->hasError()) {
            return $validateResultadoAgenteVentanilla;
        }

        $validateResultadoVentanilla = $this->validarVentanilla($ventanilla);
        if ($validateResultadoVentanilla->hasError()) {
            return $validateResultadoVentanilla;
        }

        $validateVentanillaPuntoAtencion = $this->validarVentanillaPuntoAtencion($ventanilla,$agente->getPuntoAtencionId());
        if ($validateVentanillaPuntoAtencion->hasError() ){
            return $validateVentanillaPuntoAtencion;
        }

        $validateVentanillaEnAgente = $this->validarVentanillaEnAgente($ventanilla,$agente);
        if ($validateVentanillaEnAgente->hasError()){
            return $validateVentanillaEnAgente;
        }

        return new ValidateResultado(null, []);
    }

    /**
     * Validamos la asociación agente - ventanilla
     *
     * @param integer $cantidadAgentes
     * @return ValidateResultado
     */
    private function validarAgenteVentanilla($cantidadAgentes)
    {
        $errors = [];
        if ($cantidadAgentes > 0) {
            $errors['AgenteVentanilla'] = "Ya existe un usuario asociado a la ventanilla.";
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos que la ventanilla pertenezca a la lista de ventanillas del agente
     *
     * @param object $ventanilla
     * @param object $agente
     * @return ValidateResultado
     */
    private function validarVentanillaEnAgente($ventanilla,$agente)
    {
        $errors[] = 'La ventanilla con ID: ' . $ventanilla->getId(). ' no pertenece a la lista de ventanillas del agente';
        foreach ($agente->getVentanillas() as $ventanillaAgente){
            if ($ventanillaAgente == $ventanilla){
                $errors = [];
                return new ValidateResultado(null, $errors);
            }
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos que en las ventanillas a eliminar (de la lista de ventanillas) no este la ventanilla actual del agente
     *
     * @param integer $ventanillasId El id de la ventanilla
     * @param object $agente
     * @return ValidateResultado
     */
    public function validarVentanillasEliminar($ventanillasId, $agente)
    {
        $errors = [];
        $ventanillaId = $agente->getVentanillaActualId();
        if ($ventanillaId && !in_array($ventanillaId,$ventanillasId)){
            $errors[] = 'No se puede eliminar la ventanilla con ID: ' . $ventanillaId. ' ya que es la ventanilla actual del agente.';
            return new ValidateResultado(null, $errors);
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos que la ventanilla pertenezca al punto de atención
     *
     * @param object $ventanilla
     * @param integer $puntoAtendionId
     * @return ValidateResultado
     */
    private function validarVentanillaPuntoAtencion($ventanilla,$puntoAtendionId)
    {
        $errors = [];
        if ($ventanilla->getPuntoAtencion()->getId()!= $puntoAtendionId ){
            $errors[] = 'La ventanilla con ID: ' . $ventanilla->getId(). ' no pertenece al Punto de Atención con ID: ' . $puntoAtendionId;
        }
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos los parámetros de la busqueda de agentes por punto de atención
     *
     * @param array $params
     * @return ValidateResultado
     */
    public function validarAgentesPuntoatencion($params)
    {
        $errors = $this->validar($params, [
            'puntosatencion' => 'matriz:required'
        ]);
        return new ValidateResultado(null, $errors);
    }
}
