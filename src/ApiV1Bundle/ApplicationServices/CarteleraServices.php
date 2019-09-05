<?php
namespace ApiV1Bundle\ApplicationServices;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Entity\Factory\CarteleraFactory;
use ApiV1Bundle\Repository\CarteleraRepository;
use ApiV1Bundle\Entity\Validator\CarteleraValidator;
use ApiV1Bundle\Entity\Sync\CarteleraSync;

/**
 * Class CarteleraServices
 * @package ApiV1Bundle\ApplicationServices
 */

class CarteleraServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository  */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Repository\ColaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\Repository\CarteleraRepository  */
    private $carteleraRepository;

     /** @var \ApiV1Bundle\Entity\Validator\CarteleraValidator  */
    private $carteleraValidator;

    /** @var \ApiV1Bundle\ApplicationServices\RolesServices  */
    private $rolesServices;

    /**
     * CarteleraServices constructor
     *
     * @param Container $container
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param ColaRepository $colaRepository
     * @param CarteleraRepository $carteleraRepository
     * @param CarteleraValidator $carteleraValidator
     * @param RolesServices $rolesServices
     */
    public function __construct(
        Container $container,
        PuntoAtencionRepository $puntoAtencionRepository,
        ColaRepository $colaRepository,
        CarteleraRepository $carteleraRepository,
        CarteleraValidator $carteleraValidator,
        RolesServices $rolesServices
    ) {
        parent::__construct($container);
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->colaRepository = $colaRepository;
        $this->carteleraRepository = $carteleraRepository;
        $this->carteleraValidator = $carteleraValidator;
        $this->rolesServices = $rolesServices;
    }

    /**
     * Crear una cartelera
     *
     * @param array $params Array con nombre de cartelera y arreglo con ids de colas asociadas
     * @param string $authorization token del usuario logueado
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function create($params, $authorization, $success, $error)
    {
        $validateResultado = $this->rolesServices->getPuntoAtencion($authorization);
        if(! $validateResultado->hasError()) {
            $carteleraFactory = new CarteleraFactory(
                $this->puntoAtencionRepository,
                $this->colaRepository,
                $this->carteleraRepository,
                $this->carteleraValidator
            );
            $puntoAtencion = $validateResultado->getEntity();
            $validateResultado = $carteleraFactory->create($params, $puntoAtencion);
        }
        return $this->processResult(
            $validateResultado,
            function ($entity) use ($success) {
                return call_user_func($success, $this->carteleraRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Listado de carteleras del punto de atención del usuario logueado, con paginado.
     *
     * @param string $authorization token del usuario logueado
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @param $onError Callback para devolver respuesta fallida
     * @return object
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllPaginate($authorization, $limit, $offset, $onError)
    {
        $result = [];
        $resultset = [];
        $validateResultado = $this->rolesServices->getPuntoAtencion($authorization);
        if(! $validateResultado->hasError()) {
            $puntoAtencionId = $validateResultado->getEntity()->getId();
            $carteleras = $this->carteleraRepository->findAllPaginate($puntoAtencionId, $offset, $limit);
            foreach ($carteleras as $cartelera) {
                $result[] = [
                    'id' => $cartelera['id'],
                    'nombre' => $cartelera['nombre'],
                    'colas' => $this->carteleraRepository->findColas($cartelera['id'])
                ];
            }
            $resultset = [
                'resultset' => [
                    'count' => $this->carteleraRepository->getTotal($puntoAtencionId),
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ];
        }
        return $this->processError(
            $validateResultado,
            function () use ($resultset, $result) {
                return $this->respuestaData($resultset, $result);
            },
            $onError
        );
    }

    /**
     * Obtiene una cartelera
     *
     * @param integer $id id único de cartelera
     * @param string $authorization token del usuario logueado
     * @param $onError Callback para devolver respuesta fallida
     * @return object
     * @throws \Doctrine\ORM\ORMException
     */
    public function get($id, $authorization, $onError)
    {
        $result = [];
        $validateResultado = $this->rolesServices->getPuntoAtencion($authorization);
        if(! $validateResultado->hasError()) {
            $puntoAtencionLogueado = $validateResultado->getEntity();
            $cartelera = $this->carteleraRepository->find($id);
            $validateResultado = $this->carteleraValidator->validarCartelera($cartelera);
            if (!$validateResultado->hasError()) {
                $validateResultado = $this->carteleraValidator->validarPermiso($cartelera, $puntoAtencionLogueado);
                if (! $validateResultado->hasError()) {
                    $colas = $cartelera->getColas();
                    $listaColas = [];
                    foreach ($colas as $cola){
                        $listaColas[]= [
                            'id'=> $cola->getId(),
                            'nombre'=> $cola->getNombre()
                        ];
                    }
                    $result = [
                        'id' => $cartelera->getId(),
                        'nombre' => $cartelera->getNombre(),
                        'puntoAtencion' => [
                            'id' => $cartelera->getPuntoAtencion()->getId(),
                            'nombre' => $cartelera->getPuntoAtencion()->getNombre()
                        ],
                        'colas' => $listaColas
                    ];
                }
            }
        }
        return $this->processError(
            $validateResultado,
            function () use ($result) {
                return $this->respuestaData([], $result);
            },
            $onError
        );
    }

    /**
     * Editar una cartelera
     *
     * @param array $params arreglo con los datos de la cartelera
     * @param integer $id id de cartelera
     * @param string $authorization token del usuario logueado
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function edit($params, $id, $authorization, $success, $error)
    {
        $validateResultado = $this->rolesServices->getPuntoAtencion($authorization);
        if(! $validateResultado->hasError()) {
            $puntoAtencionLogueado = $validateResultado->getEntity();
            $carteleraSync = new CarteleraSync(
                $this->puntoAtencionRepository,
                $this->colaRepository,
                $this->carteleraRepository,
                $this->carteleraValidator
            );
            $validateResultado = $carteleraSync->edit($id, $params, $puntoAtencionLogueado);
        }
        return $this->processResult(
            $validateResultado,
            function () use ($success) {
                return call_user_func($success, $this->carteleraRepository->flush());
            },
            $error
        );
    }

    /**
     * Elimina una cartelera
     *
     * @param integer $id Identificador único
     * @param string $authorization token del usuario logueado
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function delete($id, $authorization, $success, $error)
    {
        $validateResultado = $this->rolesServices->getPuntoAtencion($authorization);
        if(! $validateResultado->hasError()) {
            $puntoAtencionLogueado = $validateResultado->getEntity();
            $carteleraSync = new CarteleraSync(
                $this->puntoAtencionRepository,
                $this->colaRepository,
                $this->carteleraRepository,
                $this->carteleraValidator
            );
            $validateResultado = $carteleraSync->delete($id, $puntoAtencionLogueado);
        }
        return $this->processResult(
            $validateResultado,
            function ($entity) use ($success) {
                return call_user_func($success, $this->carteleraRepository->remove($entity));
            },
            $error
        );
    }

}
