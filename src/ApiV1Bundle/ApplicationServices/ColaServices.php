<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\Factory\ColaFactory;
use ApiV1Bundle\Entity\Sync\ColaSync;
use ApiV1Bundle\Entity\Validator\ColaValidator;
use ApiV1Bundle\Entity\Validator\SNCValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\Validator\CommunicationValidator;

/**
 * Class ColaServices
 * @package ApiV1Bundle\ApplicationServices
 */
class ColaServices extends SNCServices
{
    /** @var \ApiV1Bundle\Entity\Validator\ColaValidator */
    private $colaValidator;

    /** @var \ApiV1Bundle\Repository\ColaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var CommunicationValidator  */
    private $communicationValidator;

    /** @var \ApiV1Bundle\ApplicationServices\RedisServices */
    private $redisServices;

    /**
     * ColaServices constructor
     *
     * @param Container $container
     * @param ColaValidator $colaValidator
     * @param ColaRepository $colaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param CommunicationValidator $communicationValidator
     * @param RedisServices $redisServices
     */
    public function __construct(
        Container $container,
        ColaValidator $colaValidator,
        ColaRepository $colaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        CommunicationValidator $communicationValidator,
        RedisServices $redisServices
    ) {
        parent::__construct($container);
        $this->colaValidator = $colaValidator;
        $this->colaRepository = $colaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->communicationValidator = $communicationValidator;
        $this->redisServices = $redisServices;
    }

    /**
     * Listado de colas por punto de antención con paginación
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @param null $tipo
     * @return object
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllPaginate($puntoAtencionId, $limit, $offset, $tipo = null)
    {
        $resultset = [];
        $response = [];
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($puntoAtencionId);
        $validateResultado = $this->colaValidator->validarParamsGet($puntoAtencion);
        if (! $validateResultado->hasError()) {
            $response = $this->colaRepository->findAllPaginate($puntoAtencion->getId(), $offset, $limit, $tipo);
            $resultset = [
                'resultset' => [
                    'count' => $this->colaRepository->getTotal($puntoAtencion->getId(), $tipo),
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ];
        }
        return  $this->respuestaData($resultset, $response);
    }

    /**
     * Listar las colas posta
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @return object
     */
    public function findAllPostas($puntoAtencionId)
    {
        $res = [];
        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($puntoAtencionId);
        $validateResultado = $this->colaValidator->validarPuntoAtencion($puntoAtencion);
        if (!$validateResultado->hasError()) {
            $postas = $this->colaRepository->findAllPostas($puntoAtencion->getId());
            foreach ($postas as $posta) {
                /** @var Cola $posta */
                if ($posta->getVentanillas()->count() > 0) {
                    $res[] = [
                        'id' => $posta->getId(),
                        'nombre' => $posta->getNombre()
                    ];
                }
            }
        }
        return $this->respuestaData([], $res);
    }

    /**
     * Obtiene una cola
     *
     * @param integer $id Identificador único de la cola
     * @param callback $onError Callback para devolver respuesta fallida
     * @return object
     */
    public function get($id, $onError)
    {
        $result = array();
        $cola = $this->colaRepository->find($id);
        $validateResultado = $this->colaValidator->validarCola($cola);
        if (! $validateResultado->hasError()) {
            $ventanillas = [];
            foreach ($cola->getVentanillas() as $ventanilla) {
                $ventanillas[] = [
                    'id' => $ventanilla->getId(),
                    'identificador' => $ventanilla->getIdentificador()
                ];
            }
            $result = [
                'id' => $cola->getId(),
                'nombre' => $cola->getNombre(),
                'grupoTramite' => $cola->getGrupoTramiteSNTId(),
                'ventanillas' => $ventanillas
            ];
        }
        return $this->processError(
            $validateResultado,
            function () use ( $result) {
                return $this->respuestaData([], $result);
            },
            $onError
        );
    }

    /**
     * Agrega una cola grupo de tramite
     *
     * @param array $params arreglo con los datos para agregar la cola
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function addColaGrupoTramite($params, $sucess, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $colaFactory = new ColaFactory(
                $this->colaValidator,
                $this->colaRepository,
                $this->puntoAtencionRepository
            );
            $validateResult = $colaFactory->create($params);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->colaRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Edita una cola grupo tramite
     *
     * @param array $params arreglo con los datos para editar la cola
     * @param integer $id Identificador único de la cola
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function editColaGrupoTramite($params, $id, $success, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $colaSync = new ColaSync(
                $this->colaValidator,
                $this->colaRepository
            );
            $validateResult = $colaSync->edit($id, $params, Cola::TIPO_GRUPO_TRAMITE);
        }

        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success, $this->colaRepository->flush());
            },
            $error
        );
    }

    /**
     * Elimina una cola grupo de tramite
     *
     * @param integer $id Identificador único de la cola
     * @param array $params request
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function removeColaGrupoTramite($id, $params, $success, $error)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        if (! $validateResult->hasError()) {
            $colaSync = new ColaSync(
                $this->colaValidator,
                $this->colaRepository
            );
            $validateResult = $colaSync->delete($id, Cola::TIPO_GRUPO_TRAMITE);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($success) {
                return call_user_func($success, $this->colaRepository->remove($entity));
            },
            $error
        );
    }

    /**
     * Agrega una cola posta
     *
     * @param array $params arreglo con los datos para agregar la cola
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function addColaPosta($params, $sucess, $error)
    {
        $colaFactory = new ColaFactory(
            $this->colaValidator,
            $this->colaRepository,
            $this->puntoAtencionRepository
        );

        $validateResult = $colaFactory->createPosta($params);

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->colaRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Editar una cola de una posta
     *
     * @param array $params Parámetros para la edición de una cola posta
     * @param integer $id Identificador único de una cola posta
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function editColaPosta($params, $id, $success, $error)
    {
        $colaSync = new ColaSync(
            $this->colaValidator,
            $this->colaRepository
        );

        $validateResult = $colaSync->edit($id, $params, Cola::TIPO_POSTA);

        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success, $this->colaRepository->flush());
            },
            $error
        );
    }

    /**
     * Elimina una cola de una posta
     *
     * @param integer $id Identificador único
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function removeColaPosta($id, $success, $error)
    {
        $colaSync = new ColaSync(
            $this->colaValidator,
            $this->colaRepository
        );

        $validateResult = $colaSync->delete($id, Cola::TIPO_POSTA);

        return $this->processResult(
            $validateResult,
            function ($entity) use ($success) {
                return call_user_func($success, $this->colaRepository->remove($entity));
            },
            $error
        );
    }

    /**
     * Obtiene cantidad de personas delante en una cola, dado un grupo trámite de SNT
     *
     * @param array $params Parámetros para obtener cántidad delante
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $err Callback para devolver respuesta fallida
     * @return object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function getDelante($params, $success, $err)
    {
        $validateResultado = $this->colaValidator->validarIntegrationGetDelante($params);
        if (!$validateResultado->hasError()) {
            $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoatencion']);
            $validateResultado = $this->colaValidator->validarPuntoAtencion($puntoAtencion);
            if (!$validateResultado->hasError()) {
                $cola = $this->colaRepository->findOneByGrupoTramiteSNTId($params['grupo_tramite']);
                $validateResultado = $this->colaValidator->validarCola($cola);
                if (!$validateResultado->hasError()) {
                    $result = $this->redisServices->getCantidadCola($puntoAtencion->getId(), $cola->getId());
                    if (!is_null($result)) {
                        $validateResultado = new ValidateResultado(['porDelante' => $result], []);
                    }
                }
            }
        }

        return $this->processResult(
            $validateResultado,
            function ($entity) use ($success) {
                return call_user_func($success, $entity);
            },
            $err
        );
    }
}
