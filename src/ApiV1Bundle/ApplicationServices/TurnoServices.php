<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Getter\TurnoGetter;
use ApiV1Bundle\Entity\Turno;
use ApiV1Bundle\Entity\Factory\TurnoFactory;
use ApiV1Bundle\Entity\Validator\TurnoValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Ventanilla;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\ColaRepository;
use ApiV1Bundle\Repository\CarteleraRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\TurnoRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Helper\ServicesHelper;
use ApiV1Bundle\ExternalServices\TurnosIntegration;
use ApiV1Bundle\ExternalServices\CarteleraIntegration;

/**
 * Class TurnoServices
 * @package ApiV1Bundle\ApplicationServices
 */
class TurnoServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\TurnoRepository $turnoRepository */
    private $turnoRepository;

    /** @var \ApiV1Bundle\Entity\Validator\TurnoValidator $turnoValidator */
    private $turnoValidator;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepository */
    private $colaRepository;

    /** @var \ApiV1Bundle\ApplicationServices\RedisServices $redisServices */
    private $redisServices;

    /** @var \ApiV1Bundle\ExternalServices\TurnosIntegration $turnoIntegration */
    private $turnoIntegration;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository */
    private $ventanillaRepository;

    /** @var AgenteRepository $agenteRepository */
    private $agenteRepository;

    /** @var RolesServices $rolesServices */
    private $rolesServices;

    /** @var CarteleraRepository $carteleraRepository */
    private $carteleraRepository;

    /** @var CarteleraIntegration $carteleraIntegration */
    private $carteleraIntegration;

    /**
     * TurnoServices constructor.
     * @param Container $container
     * @param TurnoRepository $turnoRepository
     * @param TurnoValidator $turnoValidator
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param ColaRepository $colaRepository
     * @param RedisServices $redisServices
     * @param TurnosIntegration $turnoIntegration
     * @param VentanillaRepository $ventanillaRepository
     * @param AgenteRepository $agenteRepository
     * @param RolesServices $rolesServices
     * @param CarteleraRepository $carteleraRepository
     * @param CarteleraIntegration $carteleraIntegration
     */
    public function __construct(
        Container $container,
        TurnoRepository $turnoRepository,
        TurnoValidator $turnoValidator,
        PuntoAtencionRepository $puntoAtencionRepository,
        ColaRepository $colaRepository,
        RedisServices $redisServices,
        TurnosIntegration $turnoIntegration,
        VentanillaRepository $ventanillaRepository,
        AgenteRepository $agenteRepository,
        RolesServices $rolesServices,
        CarteleraRepository $carteleraRepository,
        CarteleraIntegration $carteleraIntegration
    ) {
        parent::__construct($container);
        $this->turnoRepository = $turnoRepository;
        $this->turnoValidator = $turnoValidator;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->colaRepository = $colaRepository;
        $this->redisServices = $redisServices;
        $this->turnoIntegration = $turnoIntegration;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->agenteRepository = $agenteRepository;
        $this->rolesServices = $rolesServices;
        $this->carteleraRepository = $carteleraRepository;
        $this->carteleraIntegration = $carteleraIntegration;
    }

    /**
     * Importa un nuevo turno del SNT
     *
     * @param array $params Arreglo con los datos a crear
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function create($params, $sucess, $error)
    {
        $turnoFactory = new TurnoFactory(
            $this->turnoRepository,
            $this->turnoValidator,
            $this->puntoAtencionRepository,
            $this->ventanillaRepository,
            $this->agenteRepository
        );

        $validateResult = $turnoFactory->create($params);

        if (! $validateResult->hasError()) {
            $turno = $validateResult->getEntity();
            $validateResult = $this->recepcionarTurno($turno);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->turnoRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Recepciona un turno desde una aplicación externa
     *
     * @param $params arreglo con los datos del turno a crear
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function integrationCreate($params, $sucess, $error)
    {
        $turnoFactory = new TurnoFactory(
            $this->turnoRepository,
            $this->turnoValidator,
            $this->puntoAtencionRepository,
            $this->ventanillaRepository,
            $this->agenteRepository,
            $this->getParameter('totem_email')
        );

        $validateResult = $turnoFactory->integrationCreate($params);

        if (! $validateResult->hasError()) {
            $turno = $validateResult->getEntity();
            $validateResult = $this->recepcionarTurno($turno);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->turnoRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Cambia el estado del Turno luego de ser atendido
     *
     * @param array $params Arreglo con los datos para modificar
     * @param $sucess | función que devuelve si tuvo éxito
     * @param $error | función que devuelve si ocurrio un error
     * @return ValidateResultado|mixed
     * @throws \Exception
     */
    public function changeStatus($params, $sucess, $error)
    {
        $turnoFactory = new TurnoFactory(
            $this->turnoRepository,
            $this->turnoValidator,
            $this->puntoAtencionRepository,
            $this->ventanillaRepository,
            $this->agenteRepository
        );

        $validateResult = $turnoFactory->changeStatus($params);
        if (! $validateResult->hasError()) {
            $turno = $validateResult->getEntity();
            if ($turno->getEstado() == Turno::ESTADO_RECEPCIONADO) {
                $validateRedis = $this->recepcionarTurno($turno);

                if ($validateRedis->hasError()) {
                    return $validateRedis;
                }
            }

            if ($turno->getEstado() == Turno::ESTADO_EN_TRANCURSO) {
                $validateRedis = $this->cambiarCola($turno, $params['cola']);

                if ($validateRedis->hasError()) {
                    return $validateRedis;
                }
            }
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess) {
                return call_user_func($sucess, $this->turnoRepository->save($entity));
            },
            $error
        );
    }

    /**
     * Recepciona un turno
     *
     * @param object Turno $turno
     * @return ValidateResultado
     * @throws \Exception
     */
    private function recepcionarTurno($turno)
    {
        $cola = $this->colaRepository->findOneBy(['grupoTramiteSNTId' => $turno->getGrupoTramiteIdSNT()]);
        return $this->redisServices->zaddCola(
            $turno->getPuntoAtencion()->getId(),
            $cola->getId(),
            $turno->getPrioridad(),
            $turno
        );
    }

    /**
     * Recepciona un turno
     *
     * @param object $turno Turno que será recepcionado
     * @return ValidateResultado
     * @throws \Exception
     */
    private function cambiarCola($turno, $colaId)
    {
        return $this->redisServices->zaddCola(
            $turno->getPuntoAtencion()->getId(),
            $colaId,
            $turno->getPrioridad(),
            $turno
        );
    }

    /**
     * Obtiene el listado de turnos del SNT
     *
     * @param array $params Arreglo con los datos
     * @param $onError Callback para devolver respuesta fallida
     * @return ValidateResultado|object
     */
    public function getListTurnosSNT($params, $onError)
    {
        $response = [];
        $validateResultado = $this->turnoValidator->validarGetSNT($params);

        if (! $validateResultado->hasError()) {
            $turnosRecepcionados = $this->buscarCodigosTurnosRecepcionados($params);

            $codigosTurnos = [];
            foreach ($turnosRecepcionados as $codigoTurno) {
                $codigosTurnos[] = $codigoTurno['codigo'];
            }

            $validateResultado = $this->turnoIntegration->getListTurnos($params, $codigosTurnos);
            if (! $validateResultado->hasError()) {
                $response = $validateResultado->getEntity();
                $response["result"] =  is_null($response["result"])? []: $response["result"];
            }
        }

        return $this->processError(
            $validateResultado,
            function () use ($response) {
                return $this->respuestaData($response["metadata"], $response["result"]);
            },
            $onError
        );
    }

    /**
     * Busca códigos de turnos recepcionados
     *
     * @param array $params Array con los datos para realizar la búsqueda (fecha, punto de atención)
     * @return ValidateResultado|object
     */
    private function buscarCodigosTurnosRecepcionados($params)
    {
        return $this->turnoRepository->turnosRecepcionados($params['fecha'], $params['puntoatencion']);
    }

    /**
     * Obtiene un turno por ID
     *
     * @param integer $id Identificador único del turno
     * @return object
     */
    public function getItemTurnoSNT($id)
    {
        $result = $this->turnoIntegration->getItemTurnoSNT($id);
        return $this->respuestaData([], $result);
    }

    /**
     * Busca turnos por código
     *
     * @param array $params Arreglo con los datos para la búsqueda
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return ValidateResultado|mixed
     */
    public function searchTurnoSNT($params, $success, $error)
    {
        $entries = [];
        $resultset = [];
        $validateResultado = $this->turnoValidator->validarSearchSNT($params);
        if (! $validateResultado->hasError()) {
            $validateResultado = $this->turnoIntegration->searchTurnoSNT($params);
            if (!$validateResultado->hasError()) {
                $response = $validateResultado->getEntity();
                $resultset = $response["metadata"];
                $entries =  is_null($response["result"])? []: $response["result"];
            }
        }
        return $this->processError(
            $validateResultado,
            function () use ($resultset, $entries) {
                return $this->respuestaData($resultset, $entries);
            },
            $error
        );
    }

    /**
     * Devuelve el listado de turnos
     *
     * @param array $params Arreglo con los datos para la búsqueda
     * @param string $authorization token del usuario logueado
     * @param callback $onError Callback para devolver respuesta fallida
     * @return ValidateResultado|object
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllPaginate($params, $authorization, $onError)
    {
        $resultset = [];
        $response = [];
        $validateResultado = $this->rolesServices->getUsuario($authorization);
        //TODO: Revisar funcionalidad de getUsuario() con token vencido. Me parece que no da error pero tampoco trae el usuario
        if (!$validateResultado->hasError()) {
            $usuario = $validateResultado->getEntity();
            $validateResultado = $this->turnoValidator->validarParamsGetRecepcionados($params);
            if (!$validateResultado->hasError()) {
                $ventanilla = $this->ventanillaRepository->find($params['ventanilla']);
                $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoatencion']);
                $validateResultado = $this->turnoValidator->validarGetRecepcionados($usuario, $ventanilla, $puntoAtencion);
                if (!$validateResultado->hasError()) {
                    $params['puntoatencion'] = $puntoAtencion->getId();
                    $turnoGetter = new TurnoGetter(
                        $this->ventanillaRepository,
                        $this->redisServices,
                        $this->turnoRepository
                    );
                    $validateResultado = $turnoGetter->getAll($params, $ventanilla);
                    if (!$validateResultado->hasError()) {
                        $response = json_decode($validateResultado->getEntity());
                        $resultset = [
                            'resultset' => [
                                'count' => $response->cantTurnos,
                                'offset' => $params['offset'],
                                'limit' => $params['limit']
                            ]
                        ];
                    }
                }
            }
        }
        return $this->processError(
            $validateResultado,
            function () use ($resultset, $response) {
                return $this->respuestaData($resultset, $this->toArray($response->turnos));
            },
            $onError
        );
    }

    /**
     * Quita el primer elemento de la cola y lo retorna
     *
     * @param array $params Arreglo con los datos para la búsqueda
     * @param string $authorization token del usuario logueado
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function getProximoTurno($params, $authorization, $onError)
    {
        $validateResultado = $this->rolesServices->getUsuario($authorization);
        $result = [];
        if (! $validateResultado->hasError()) {
            $usuario = $validateResultado->getEntity();
            $ventanilla = isset($params['ventanilla']) ? $this->ventanillaRepository->find($params['ventanilla']) : null;
            $puntoAtencion = isset($params['puntoatencion']) ? $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoatencion']) : null;
            $validateResultado = $this->turnoValidator->validarGetRecepcionados($usuario, $ventanilla, $puntoAtencion);
            if (! $validateResultado->hasError()) {
                $turnoGetter = new TurnoGetter(
                    $this->ventanillaRepository,
                    $this->redisServices,
                    $this->turnoRepository
                );
                    $validateResultado = $turnoGetter->getProximoTurno($puntoAtencion->getId(), $ventanilla);
                    if (!$validateResultado->hasError()) {
                        $turno = $validateResultado->getEntity();
                        $campos = $turno->getDatosTurno()->getCampos();
                        $campos['documento'] = $campos['cuil'];
                        unset($campos['cuil']);
                        $result = array(
                            'id' => $turno->getId(),
                            'tramite' => $turno->getTramite(),
                            'puntoAtencion' => $turno->getPuntoAtencion()->getId(),
                            'codigo' => $turno->getCodigo(),
                            'fecha' => $turno->getFecha(),
                            'hora' => $turno->getHora(),
                            'estado' => $turno->getEstado(),
                            'datosTurno' => array(
                                'nombre' => $turno->getDatosTurno()->getNombre(),
                                'apellido' => $turno->getDatosTurno()->getApellido(),
                                'documento' => $turno->getDatosTurno()->getCuil(),
                                'email' => $turno->getDatosTurno()->getEmail(),
                                'telefono' => $turno->getDatosTurno()->getTelefono(),
                                'campos' => $this->formatCampos($campos)
                            ),
                            'cartelera' => $this->enviarTurnoNodeJS($turno, $ventanilla)
                        );
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
     * Enviar información del proximo turno a nodejs, para actualizar carteleras
     *
     * @param Turno $turno
     * @param  Ventanilla $ventanilla
     * @return ValidateResultado
     */
    private function enviarTurnoNodeJS($turno, $ventanilla)
    {
        $result = array(
            'turno' => $turno->getId(),
            'nombre' => $turno->getDatosTurno()->getNombre(),
            'apellido' => $turno->getDatosTurno()->getApellido(),
            'ventanilla' => array(
                'id' => $ventanilla->getId(),
                'identificador' => $ventanilla->getIdentificador()
            ),
            'carteleras' => $this->carteleraRepository->findAllByCola($turno->getCola())
        );
        return $this->carteleraIntegration->enviarTurno($result);
    }

    /**
     * Dependiendo el valor que tiene Campos retorna siempre un objeto
     *
     * @param $campos
     * @return object
     */
    private function formatCampos($campos)
    {
        if (is_array($campos) && count($campos)>0 ) {
            return $campos;
        }
        if (is_array($campos) && count($campos)==0) {
            return new \stdClass();
        }
        $camposObj = json_decode($campos, true);
        if (empty($camposObj)) {
            return new \stdClass();
        }
        return $camposObj;
    }

    /**
     * Obtiene la posicion de un turno
     *
     * @param integer $id Identificador único del turno
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function getPosicionTurno($id, $onError)
    {
        $result = [];
        $error = [];
        $turno = $this->turnoRepository->find($id);

        $validateResultado = $this->turnoValidator->validarTurno($turno);

        if (! $validateResultado->hasError()) {
            $cola = $this->colaRepository->findOneBy(['grupoTramiteSNTId' => $turno->getGrupoTramiteIdSNT()]);
            $pos = $this->redisServices->getPosicion($turno, $cola);

            if ($pos == -1) {
                $error['Turno'] = 'Turno no encontrado en la cola';
                $validateResultado = new ValidateResultado(null, $error);
            }

            $result = [
                'id' => $turno->getId(),
                'tramite' => $turno->getTramite(),
                'codigo' => ServicesHelper::obtenerCodigoSimple($turno->getCodigo()),
                'posicion' => $pos
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
