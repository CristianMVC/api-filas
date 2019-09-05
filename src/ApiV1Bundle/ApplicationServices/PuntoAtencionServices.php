<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Factory\PuntoAtencionFactory;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Entity\Validator\PuntoAtencionValidator;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\Sync\PuntoAtencionSync;
use ApiV1Bundle\Entity\Validator\CommunicationValidator;

/**
 * Class PuntoAtencionServices
 * @package ApiV1Bundle\ApplicationServices
 */
class PuntoAtencionServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Entity\Validator\PuntoAtencionValidator $puntoAtencionValidator */
    private $puntoAtencionValidator;

    /** @var CommunicationValidator $communicationValidator */
    private $communicationValidator;

    /**
     * PuntoAtencionServices construct
     * @param Container $container
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param PuntoAtencionValidator $puntoAtencionValidator
     * @param CommunicationValidator $communicationValidator
     */
    public function __construct(
        Container $container,
        PuntoAtencionRepository $puntoAtencionRepository,
        PuntoAtencionValidator $puntoAtencionValidator,
        CommunicationValidator $communicationValidator
    ) {
        parent::__construct($container);
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->puntoAtencionValidator = $puntoAtencionValidator;
        $this->communicationValidator = $communicationValidator;
    }

    /**
     * Listado de los Puntos de antención con paginación
     *
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @return object
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllPaginate($limit, $offset)
    {
        $result = $this->puntoAtencionRepository->findAllPaginate($offset, $limit);
        $resultset = [
            'resultset' => [
                'count' => $this->puntoAtencionRepository->getTotal(),
                'offset' => $offset,
                'limit' => $limit
            ]
        ];
        return $this->respuestaData($resultset, $result);
    }

    /**
     * Crea un punto de atencion
     *
     * @param array $params Arreglo con los datos para crear un punto de atención
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function create($params, $sucess, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $puntoAtencionFactory = new PuntoAtencionFactory(
                $this->puntoAtencionRepository,
                $this->puntoAtencionValidator
            );
            $validateResult = $puntoAtencionFactory->create($params);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->puntoAtencionRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Edita un punto de atencion
     *
     * @param int $id Identificador único de un punto de atención
     * @param array $params Arreglo con los datos para crear un punto de atención
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function edit($id, $params, $sucess, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $puntoAtencionSync = new PuntoAtencionSync(
                $this->puntoAtencionRepository,
                $this->puntoAtencionValidator
            );
            $validateResult = $puntoAtencionSync->edit($id, $params);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->puntoAtencionRepository->flush());
            },
            $error
        );
    }

    /**
     * Elimina un punto de atencion
     *
     * @param int $id Identificador único de un punto de atención
     * @param array $params Request
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function delete($id, $params, $sucess, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $puntoAtencionSync = new PuntoAtencionSync(
                $this->puntoAtencionRepository,
                $this->puntoAtencionValidator
            );
            $validateResult = $puntoAtencionSync->delete($id);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->puntoAtencionRepository->remove($entity));
            },
            $error
        );
    }
}
