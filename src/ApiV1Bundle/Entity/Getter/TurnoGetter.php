<?php
namespace ApiV1Bundle\Entity\Getter;

use ApiV1Bundle\ApplicationServices\RedisServices;
use ApiV1Bundle\Entity\Turno;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\TurnoRepository;
use ApiV1Bundle\Repository\VentanillaRepository;

/*
 * Class TurnoGetter
 * @package ApiV1Bundle\Entity\Getter
 */
class TurnoGetter
{
    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\ApplicationServices\RedisServices $redisServices  */
    private $redisServices;

    /** @var \ApiV1Bundle\Repository\TurnoRepository $turnoRepository  */
    private $turnoRepository;

    /**
     * TurnoGetter constructor
     *
     * @param VentanillaRepository $ventanillaRepository
     * @param RedisServices $redisServices
     * @param TurnoRepository $turnoRepository
     */
    public function __construct(
        VentanillaRepository $ventanillaRepository,
        RedisServices $redisServices,
        TurnoRepository $turnoRepository
    )
    {
        $this->ventanillaRepository = $ventanillaRepository;
        $this->redisServices = $redisServices;
        $this->turnoRepository = $turnoRepository;
    }

    /**
     * Obtiene los turnos de una ventanilla
     *
     * @param array $params
     * @param object $ventanilla
     * @return ValidateResultado
     * @throws \Exception
     */
    public function getAll($params, $ventanilla)
    {
        $colas = $ventanilla->getColas();
        return $this->getTurnos($colas, $ventanilla, $params);
    }

    /**
     * Obtiene los turnos
     * @param object $colas Colas
     * @param object $ventanilla ventanilla
     * @param array $params
     * @return ValidateResultado
     * @throws \Exception
     */
    private function getTurnos($colas, $ventanilla, $params)
    {

        if ($colas->count() > 0) {

            $this->redisServices->unionColas($params['puntoatencion'], $ventanilla);

            $turnos = $this->redisServices->getColaVentanilla($params['puntoatencion'], $ventanilla, $params['offset'], $params['limit']);

            $cantTurnos = count($this->redisServices->getColaVentanilla($params['puntoatencion'], $ventanilla, 0, -1));

        } else {
            $errors['Cola'] = 'La ventanilla no tiene cola';
            return new ValidateResultado(null, $errors);
        }

        $response = [
            'turnos' => $this->parseTurnos($turnos),
            'cantTurnos' => $cantTurnos
        ];

        return new ValidateResultado(json_encode($response), []);
    }

    /**
     *
     * @param $list
     * @return array
     */
    private function parseTurnos($list)
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = (array) json_decode($item);
        }
        return $result;
    }

    /**
     * Obtiene un turno del SNC
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @param object $ventanilla objeto ventanilla
     * @return ValidateResultado
     * @throws \Exception
     */
    public function getProximoTurno($puntoAtencionId, $ventanilla)
    {
        $errors = [];
        $proximoTurno = $this->redisServices->getProximoTurno($puntoAtencionId, $ventanilla);
        if ($proximoTurno) {
            $turno = $this->getTurno($puntoAtencionId, json_decode($proximoTurno));

            if ($turno) {
                $turno->setCola(json_decode($proximoTurno)->cola);
                return new ValidateResultado($turno, []);
            }

            $errors[] = 'Turno no encontrado en el Sistema Nacional de Turnos.';
            return new ValidateResultado(null, $errors);

        }

        $errors[] = 'No hay más turnos.';
        return new ValidateResultado(null, $errors);
    }

    /**
     * Obtener turno
     *
     * @param integer $puntoAtencionId Identificador único del punto de atención
     * @param object $turno objeto turno
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getTurno($puntoAtencionId, $turno)
    {
        return $this->turnoRepository->search($turno->cuil, $turno->codigo, $puntoAtencionId);
    }
}
