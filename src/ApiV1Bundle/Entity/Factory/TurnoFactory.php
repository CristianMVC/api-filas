<?php
/**
 * Created by PhpStorm.
 * User: Javier
 * Date: 12/11/2017
 * Time: 7:26 PM
 */
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\DatosTurno;
use ApiV1Bundle\Entity\Turno;
use ApiV1Bundle\Entity\Validator\TurnoValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\TurnoRepository;
use ApiV1Bundle\Repository\VentanillaRepository;

/**
 * Class TurnoFactory
 * @package ApiV1Bundle\Entity
 */
class TurnoFactory
{
    /** @var \ApiV1Bundle\Repository\TurnoRepository $turnoRepository  */
    private $turnoRepository;

    /** @var \ApiV1Bundle\Entity\Validator\TurnoValidator $turnoValidator  */
    private $turnoValidator;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository  */
    private $agenteRepository;

    /** @var string */
    private $totemEmail;

    /**
     * TurnoServices constructor
     *
     * @param TurnoRepository $turnoRepository
     * @param TurnoValidator $turnoValidator
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param VentanillaRepository $ventanillaRepository
     * @param AgenteRepository $agenteRepository
     * @param string $totemEmail
     */
    public function __construct(
        TurnoRepository $turnoRepository,
        TurnoValidator $turnoValidator,
        PuntoAtencionRepository $puntoAtencionRepository,
        VentanillaRepository $ventanillaRepository,
        AgenteRepository $agenteRepository,
        $totemEmail = null
    ) {
        $this->turnoRepository = $turnoRepository;
        $this->turnoValidator = $turnoValidator;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->agenteRepository = $agenteRepository;
        $this->totemEmail = $totemEmail;
    }

    /**
     * Crear turno
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     */
    public function create($params)
    {
        $validateResultado = $this->turnoValidator->validarCreate($params);

        if (! $validateResultado->hasError()) {
            $documento = $params['datosTurno']['documento'];
            $params['datosTurno']['cuil'] = $documento;
            $params['datosTurno']['campos']['cuil'] = $documento;
            unset($params['datosTurno']['documento']);
            unset($params['datosTurno']['campos']['documento']);

            $puntoAtencion = $this->puntoAtencionRepository->findOneBy([
                'puntoAtencionIdSnt' => $params['puntoAtencion']
            ]);

            $validatePuntoAtencion = $this->turnoValidator->validarPuntoAtencion($puntoAtencion);

            if (!$validatePuntoAtencion->hasError()) {
                $fecha = new \DateTime($params['fecha']);
                $hora = new \DateTime($params['hora']);

                $datosTurno = new DatosTurno(
                    $params['datosTurno']['nombre'],
                    $params['datosTurno']['apellido'],
                    $params['datosTurno']['cuil'],
                    $params['datosTurno']['email'],
                    $params['datosTurno']['telefono'],
                    $params['datosTurno']['campos']
                );

                $this->turnoRepository->persist($datosTurno);

                $turno = new Turno(
                    $puntoAtencion,
                    $datosTurno,
                    $params['grupoTramite'],
                    $fecha,
                    $hora,
                    $params['estado'],
                    $params['tramite'],
                    $params['codigo'],
                    $params['prioridad']
                );

                if (isset($params['motivo'])) {
                    $turno->setMotivoTerminado($params['motivo']);
                }

                return new ValidateResultado($turno, []);
            } else {
                return $validatePuntoAtencion;
            }
        }
        return $validateResultado;
    }

    /**
     * Crear turno desde SNT :: TOD
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     */
    public function integrationCreate($params)
    {
        $validateResultado = $this->turnoValidator->validarCreateIntegration($params);

        if (! $validateResultado->hasError()) {
            $puntoAtencion = $this->puntoAtencionRepository->findOneBy([
                'puntoAtencionIdSnt' => $params['puntoatencion']
            ]);

            $validatePuntoAtencion = $this->turnoValidator->validarPuntoAtencion($puntoAtencion);

            if (! $validatePuntoAtencion->hasError()) {
                $fecha = new \DateTime();
                $hora = new \DateTime();

                $datosTurno = new DatosTurno(
                    $params['nombre'],
                    $params['apellido'],
                    $params['cuil'],
                    $this->totemEmail,
                    null,
                    $params['campos']
                );

                $this->turnoRepository->persist($datosTurno);

                $turno = new Turno(
                    $puntoAtencion,
                    $datosTurno,
                    $params['grupo_tramite'],
                    $fecha,
                    $hora,
                    Turno::ESTADO_RECEPCIONADO,
                    $params['tramite'],
                    $params['codigo'],
                    $params['prioridad']
                );

                return new ValidateResultado($turno, []);
            } else {
                return $validatePuntoAtencion;
            }
        }
        return $validateResultado;
    }


    /**
     * Cambiar estado del turno
     *
     * @param array $params arreglo con los datos
     * @return ValidateResultado
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function changeStatus($params)
    {
        $ventanilla = (isset($params['ventanilla']))
            ? $this->ventanillaRepository->find($params['ventanilla'])
            : null;

        $validateResultado = $this->turnoValidator->validarChangeStatus($params, $ventanilla);
        if (! $validateResultado->hasError()) {
            $turno = $this->turnoRepository->search($params['cuil'], $params['codigo']);
            $validateResultado = $this->turnoValidator->validarTurno($turno);
            if (! $validateResultado->hasError()) {
                $motivo = isset($params['motivo']) ? $params['motivo'] : null;
                $newTurno = new Turno(
                    $turno->getPuntoAtencion(),
                    $turno->getDatosTurno(),
                    $turno->getGrupoTramiteIdSNT(),
                    new \DateTime($turno->getFecha()),
                    new \DateTime($turno->getHora()),
                    $params['estado'],
                    $turno->getTramite(),
                    $params['codigo'],
                    $params['prioridad'],
                    $motivo
                );

                if (isset($params['motivo'])) {
                    $newTurno->setMotivoTerminado($params['motivo']);
                }

                $newTurno->setVentanilla($ventanilla);
                $agente = $this->agenteRepository->getAgenteVentanilla($ventanilla);
                if ($agente) {
                    $newTurno->setAgente($agente);
                }
                return new ValidateResultado($newTurno, []);
            }
        }
        return $validateResultado;
    }
}
