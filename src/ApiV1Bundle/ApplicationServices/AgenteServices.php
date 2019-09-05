<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Sync\AgenteSync;
use ApiV1Bundle\Entity\Validator\AgenteValidator;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\UsuarioRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Entity\Validator\VentanillaValidator;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class AgenteServices
 * @package ApiV1Bundle\ApplicationServices
 */
class AgenteServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Entity\Validator\AgenteValidator $agenteValidator */
    private $agenteValidator;

    /** @var \ApiV1Bundle\Repository\UsuarioRepository $usuarioRepository */
    private $usuarioRepository;

    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\ApplicationServices\RolesServices $rolesServices */
    private $rolesServices;

    /** @var \ApiV1Bundle\Entity\Validator\VentanillaValidator $ventanillaValidator */
    private $ventanillaValidator;

    /**
     * AgenteServices constructor
     *
     * @param Container $container
     * @param AgenteRepository $agenteRepository
     * @param AgenteValidator $agenteValidator
     * @param UsuarioRepository $usuarioRepository
     * @param UserValidator $userValidator
     * @param VentanillaRepository $ventanillaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param RolesServices $rolesServices
     * @param VentanillaValidator $ventanillaValidator
     */
    public function __construct(
        Container $container,
        AgenteRepository $agenteRepository,
        AgenteValidator $agenteValidator,
        UsuarioRepository $usuarioRepository,
        UserValidator $userValidator,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        RolesServices $rolesServices,
        VentanillaValidator $ventanillaValidator
    ) {
        parent::__construct($container);
        $this->agenteRepository = $agenteRepository;
        $this->agenteValidator = $agenteValidator;
        $this->usuarioRepository = $usuarioRepository;
        $this->userValidator = $userValidator;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->rolesServices = $rolesServices;
        $this->ventanillaValidator = $ventanillaValidator;
    }

    /**
     * Listado de Agentes por punto de antención con paginación
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @return object
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllPaginate($puntoAtencionId, $limit, $offset,$params = [])
    {
        $result = [];
        $ventanillas = [];

        $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($puntoAtencionId);
        $puntoAtencionId = $puntoAtencion ? $puntoAtencion->getId() : null;
        $agentes = $this->agenteRepository->findAllPaginate($puntoAtencionId, $offset, $limit,$params);

        foreach ($agentes as $item) {
            $agente = $this->agenteRepository->find($item['agente_id']);

            if (count($agente->getVentanillas())) {
                foreach ($agente->getVentanillas() as $ventanilla) {
                    $ventanillas['ventanillas'][] = $ventanilla->getIdentificador();
                }
            } else {
                $ventanillas['ventanillas'] = [];
            }

            // @Todo este unset está muy mal, algún día en el futuro hay que arreglarlo
            unset($item['agente_id']);
            $result[] = array_merge($item, $ventanillas);
            $ventanillas = [];
        }

        $resultset = [
            'resultset' => [
                'count' => $this->agenteRepository->getTotal($puntoAtencionId,$params),
                'offset' => $offset,
                'limit' => $limit
            ]
        ];

        return $this->respuestaData($resultset, $result);
    }

    /**
     * Obtener id de ventanilla actual del agente logueado
     *
     * @param string $authorization token del usuario logueado
     * @param $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function findVentanillaActual($authorization, $onError){

        $result = [];
        $resultset = [];
        $validateResultado = $this->rolesServices->getUsuario($authorization);
        if (! $validateResultado->hasError()) {
            $agente = $validateResultado->getEntity();
            $ventanilla = $agente->getVentanillaActual();
            if ($ventanilla){ //para evitar "code": 400, "status": "FATAL", "userMessage": {"errors": {"Ventanilla": "Ventanilla inexistente."}}
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
            }
        }

        return $this->processError(
            $validateResultado,
            function () use ($result, $resultset) {
                return $this->respuestaData($resultset, $result);
            },
            $onError
        );
    }

    /**
     * Obtiene un agente
     *
     * @param integer $id Identificador único del agente
     * @param callback $onError Callback para devolver respuesta fallida
     * @return object
     */
    public function get($id, $onError)
    {
        $agenteData = [];
        $agente = $this->agenteRepository->find($id);
        $validateResultado = $this->agenteValidator->validarAgente($agente);

        if (! $validateResultado->hasError()) {
            $agenteData = [
                'id' => $agente->getId(),
                'nombre' => $agente->getNombre(),
                'apellido' => $agente->getApellido(),
                'ventanillaActual' => $agente->getVentanillaActualId(),
                'ventanillaActualIdentificador' => $agente->getVentanillaActualIdentificador(),
                'user' => [
                    'id' =>  $agente->getUserId(),
                    'username' => $agente->getUsername()
                ],
                'puntoAtencion' => [
                    'id' =>  $agente->getPuntoAtencionId(),
                    'nombre' => $agente->getNombrePuntoAtencion()
                ],
                'ventanillas' => []
            ];
            foreach ($agente->getVentanillas() as $ventanilla){
                $agenteData['ventanillas'][] = [
                    'id' => $ventanilla->getId(),
                    'identificador' => $ventanilla->getIdentificador()
                ];
            }
        }
        return $this->processError(
            $validateResultado,
            function () use ($agenteData) {
                return $this->respuestaData([], $agenteData);
            },
            $onError
        );
    }

    /**
     * Asigna una ventanilla a un agente
     *
     * @param integer $idUsuario Identificador único del agente
     * @param integer $idVentanilla Id de la ventanilla
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function asignarVentanilla($idUsuario, $idVentanilla, $success, $error)
    {
        $agenteSync = new AgenteSync(
            $this->agenteValidator,
            $this->agenteRepository,
            $this->ventanillaRepository,
            $this->puntoAtencionRepository
        );
        $validateResult = $agenteSync->asignarVentanilla($idUsuario, $idVentanilla);

        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success, $this->agenteRepository->flush());
            },
            $error
        );
    }

    /**
     * Desasigna una ventanilla a un Agente
     *
     * @param integer $uid Identificador único del agente
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return ValidateResultado
     * @throws \Exception
     */
    public function desasignarVentanilla($uid, $success, $error)
    {
        $user = $this->usuarioRepository->findOneByUser($uid);

        $agenteSync = new AgenteSync(
            $this->agenteValidator,
            $this->agenteRepository,
            $this->ventanillaRepository,
            $this->puntoAtencionRepository
        );
        $validateResult = $agenteSync->desasignarVentanilla($user);

        return $this->processResult(
            $validateResult,
            function () use ($success) {
                return call_user_func($success, $this->agenteRepository->flush());
            },
            $error
        );
    }

    /**
     * Listado de ventanillas disponibles para el agente
     *
     * @param integer $id Identificador único del agente
     * @return object
     */
    public function findVentanillasAgente($id)
    {
        $response = [];
        $usuario = $this->usuarioRepository->findOneByUser($id);
        $agente = $this->agenteRepository->find($usuario->getId());
        $validateResultado = $this->agenteValidator->validarAgente($agente);
        if (! $validateResultado->hasError()) {
            // listado de ventanillas actualmente en uso
            $listaAgentes = $this->agenteRepository->findByPuntoAtencion($agente->getPuntoAtencion()->getId());
            $ventanillasEnUso = [];
            foreach ($listaAgentes as $agentePuntoAtencion) {
                if ($agentePuntoAtencion->getId() != $agente->getId()) {
                    $ventanillaActual = $agentePuntoAtencion->getventanillaActual();
                    if ($ventanillaActual) {
                        $ventanillasEnUso[] = $ventanillaActual->getId();
                    }
                }
            }
            // listado de ventanillas del agente
            foreach ($agente->getVentanillas() as $ventanilla) {
                if (! in_array($ventanilla->getId(), $ventanillasEnUso)) {
                    $response[] = [
                        'id' => $ventanilla->getId(),
                        'identificador' => $ventanilla->getIdentificador()
                    ];
                }
            }
            return $this->respuestaData([], $response);
        }
        return $this->respuestaData([], null);
    }

    /**
     * Devuelve todos los agentes que pertenecen a un punto de atención
     * @param string $authorization token del usuario logueado
     * @param array $params Arreglo con los parámetros necesarios para ejecutar el endpoint
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function findAllAgentes($authorization, $params, $onError)
    {
        $resultset = [];
        $agentes = [];

        $validateResultado = $this->rolesServices->getUsuario($authorization);

        if (! $validateResultado->hasError()) {
            $validateResultado = $this->agenteValidator->validarAgentesPuntoatencion($params);
            if (! $validateResultado->hasError()) {
                $agentes = $this->agenteRepository->findAllByPuntoAtencion($params['puntosatencion'],$params);

                foreach ($agentes as &$agente) {
                    $agente['organismo'] = [
                        'id' => null,
                        'nombre' => null
                    ];
                    $agente['area'] = [
                        'id' => null,
                        'nombre' => null
                    ];
                    $agente['puntoAtencion'] = [
                        'id' => $agente['pda_id_snt'],
                        'nombre' => $agente['pda']
                    ];
                    unset($agente['pda']);
                    unset($agente['pda_id']);
                    unset($agente['pda_id_snt']);
                }

            }
        }

        return $this->processError(
            $validateResultado,
            function () use ($agentes, $resultset) {
                return $this->respuestaData($resultset, $agentes);
            },
            $onError
        );
    }
}