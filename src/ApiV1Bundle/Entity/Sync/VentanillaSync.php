<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Validator\VentanillaValidator;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Repository\AgenteRepository;

/**
 * Class VentanillaSync
 * @package ApiV1Bundle\Entity\Sync
 */
class VentanillaSync
{
    /** @var \ApiV1Bundle\Entity\Validator\VentanillaValidator VentanillaValidator  */
    private $ventanillaValidator;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository VentanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\ColaRepository ColaRepository  */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\AgenteRepository AgenteRepository  */
    private $agenteRepository;

    /**
     * VentanillaSync constructor
     *
     * @param VentanillaValidator $ventanillaValidator
     * @param VentanillaRepository $ventanillaRepository
     * @param ColaRepository $colaRepository
     * @param AgenteRepository $agenteRepository
     */
    public function __construct(
        VentanillaValidator $ventanillaValidator,
        VentanillaRepository $ventanillaRepository,
        ColaRepository $colaRepository,
        AgenteRepository $agenteRepository)
    {
        $this->ventanillaValidator = $ventanillaValidator;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->colaRepository = $colaRepository;
        $this->agenteRepository = $agenteRepository;
    }

    /**
     * Editar una ventanilla
     *
     * @param integer $id Identificador único para una ventanilla
     * @param array $params array con los datos para editar
     * @return ValidateResultado
     */
    public function edit($id, $params)
    {
        $ventanilla = $this->ventanillaRepository->find($id);
        $validateResultado = $this->ventanillaValidator->validarEdit($ventanilla, $params);

        if (! $validateResultado->hasError()) {
            $ventanilla->setIdentificador($params['identificador']);

            //remove colas
            $colas = $ventanilla->getColas();
            foreach ($colas as $cola) {
                $ventanilla->removeCola($cola);
            }

            //add colas
            foreach ($params['colas'] as $colaId) {
                $cola = $this->colaRepository->find($colaId);
                $ventanilla->addCola($cola);
            }

            $validateResultado->setEntity($ventanilla);
        }

        return $validateResultado;
    }

    /**
     * Borra una ventanilla
     *
     * @param integer $id Identificador único de ventanilla
     * @return ValidateResultado
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function delete($id)
    {
        $ventanilla = $this->ventanillaRepository->find($id);
        $validateResultado = $this->ventanillaValidator->validarVentanilla($ventanilla);

        if (! $validateResultado->hasError()) {
            $cantidadAgentes = $this->agenteRepository->getCantidadDeAgentesAsociadosAVentanilla($id);
            $validateResultado = $this->ventanillaValidator->validarDelete($ventanilla,$cantidadAgentes);
            if (! $validateResultado->hasError()) {
                $validateResultado->setEntity($ventanilla);
                return $validateResultado;
            }
        }

        return $validateResultado;
    }
}