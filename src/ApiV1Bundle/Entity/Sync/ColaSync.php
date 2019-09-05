<?php
namespace ApiV1Bundle\Entity\Sync;


use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\Validator\ColaValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\ColaRepository;

/**
 * Class ColaSync
 * @package ApiV1Bundle\Entity\Sync
 */
class ColaSync
{
    /** @var \ApiV1Bundle\Entity\Validator\ColaValidator $colaValidator  */
    private $colaValidator;

    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepository  */
    private $colaRepository;

    /**
     * ColaSync constructor
     *
     * @param ColaValidator $colaValidator
     * @param ColaRepository $colaRepository
     */
    public function __construct(ColaValidator $colaValidator, ColaRepository $colaRepository)
    {
        $this->colaValidator = $colaValidator;
        $this->colaRepository = $colaRepository;
    }

    /**
     * Edita una cola
     *
     * @param integer $id Identificador único de cola
     * @param array $params arreglo con los datos para editar
     * @param $tipo El tipo de cola
     * @return ValidateResultado
     */
    public function edit($id, $params, $tipo)
    {
        if ($tipo == Cola::TIPO_GRUPO_TRAMITE) {
            $cola = $this->colaRepository->findOneBy(['grupoTramiteSNTId' => $id]);
        } else {
            $cola = $this->colaRepository->find($id);
        }

        $validateResultado = $this->colaValidator->validarEdit($params, $cola);

        if (! $validateResultado->hasError()) {
            $cola->setNombre($params['nombre']);
            $validateResultado->setEntity($cola);
        }

        return $validateResultado;
    }

    /**
     * Borra una cola grupo tramite
     *
     * @param integer $id Identificador único
     * @param integer $tipo Tipo de cola a borrar
     * @return ValidateResultado
     * @throws \Exception
     */
    public function delete($id, $tipo)
    {
        if ($tipo == Cola::TIPO_GRUPO_TRAMITE) {
            $cola = $this->colaRepository->findOneBy(['grupoTramiteSNTId' => $id]);
        } else {
            $cola = $this->colaRepository->find($id);
        }

        $validateResultado = $this->colaValidator->validarDelete($cola);

        if (! $validateResultado->hasError()) {
            $validateResultado->setEntity($cola);
        }

        return $validateResultado;
    }
}