<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Factory\VentanillaFactory;
use ApiV1Bundle\Entity\Sync\VentanillaSync;
use ApiV1Bundle\Entity\Validator\VentanillaValidator;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Repository\AgenteRepository;

/**
 * Class VentanillaServices
 * @package ApiV1Bundle\ApplicationServices
 */
class VentanillaServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Entity\Validator\VentanillaValidator $ventanillaValidator */
    private $ventanillaValidator;

    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var AgenteRepository $agenteRepository */
    private $agenteRepository;

    /**
     * VentanillaServices Constructor
     *
     * @param Container $container
     * @param VentanillaRepository $ventanillaRepository
     * @param VentanillaValidator $ventanillaValidator
     * @param ColaRepository $colaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param AgenteRepository $agenteRepository
     */
    public function __construct(
        Container $container,
        VentanillaRepository $ventanillaRepository,
        VentanillaValidator $ventanillaValidator,
        ColaRepository $colaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        AgenteRepository $agenteRepository
    ) {
        parent::__construct($container);
        $this->ventanillaRepository = $ventanillaRepository;
        $this->ventanillaValidator = $ventanillaValidator;
        $this->colaRepository = $colaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->agenteRepository = $agenteRepository;
    }

    /**
     *  Crea una Ventanilla
     *
     * @param array $params Arreglo con los datos a crear
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function create($params, $sucess, $error)
    {
        $ventanillaFactory = new VentanillaFactory(
            $this->ventanillaValidator,
            $this->colaRepository,
            $this->puntoAtencionRepository
        );
        $validateResult = $ventanillaFactory->create($params);

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->ventanillaRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Edita una ventanilla
     *
     * @param array $params Arreglo con los datos a modificar
     * @param integer $id Identificador único de la ventanilla a modificar
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function edit($params, $id, $success, $error)
    {
        $ventanillaSync = new VentanillaSync(
            $this->ventanillaValidator,
            $this->ventanillaRepository,
            $this->colaRepository,
            $this->agenteRepository
        );
        $validateResult = $ventanillaSync->edit($id, $params);

        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success, $this->ventanillaRepository->flush());
            },
            $error
        );
    }

    /**
     * Elimina una ventanilla
     *
     * @param integer $id Identificador único de la ventanilla
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function delete($id, $success, $error)
    {
        $ventanillaSync = new VentanillaSync(
            $this->ventanillaValidator,
            $this->ventanillaRepository,
            $this->colaRepository,
            $this->agenteRepository
        );
        $validateResult = $ventanillaSync->delete($id);

        return $this->processResult(
            $validateResult,
            function ($entity) use ($success) {
                return call_user_func($success, $this->ventanillaRepository->remove($entity));
            },
            $error
        );
    }

    /**
     * Lista las ventanillas por punto de antención con paginación
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @return object
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllPaginate($puntoAtencionId, $limit, $offset)
    {
        $result = $this->ventanillaRepository->findAllPaginate($puntoAtencionId, $offset, $limit);
        $resultset = [
            'resultset' => [
                'count' => $this->ventanillaRepository->getTotal($puntoAtencionId),
                'offset' => $offset,
                'limit' => $limit
            ]
        ];
        return $this->respuestaData($resultset, $result);
    }

    /**
     * Obtiene una ventanilla
     *
     * @param integer $id Identificador único de la ventanilla
     * @param callback $onError Callback para devolver respuesta fallida
     * @return object
     */
    public function get($id, $onError)
    {
        $result = [];
        $ventanilla = $this->ventanillaRepository->find($id);
        $validateResultado = $this->ventanillaValidator->validarVentanilla($ventanilla);
        if (! $validateResultado->hasError()) {
            $colas = [];
            foreach ($ventanilla->getColas() as $cola) {
                $colas[] = [
                    'id' => $cola->getId(),
                    'nombre' => $cola->getNombre()
                ];
            }
            $result = [
                'id' => $ventanilla->getId(),
                'identificador' => $ventanilla->getIdentificador(),
                'colas' => $colas
            ];
        }
        return $this->processError(
            $validateResultado,
            function () use ($result) {
                return $this->respuestaData([], $result);
            },
            $onError
        );
    }
}