<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Turno;

/**
 * Class RedisServices
 * @package ApiV1Bundle\ApplicationServices
 */
class RedisServices extends SNCServices
{
    /**
     * Agrega un elemento a la cola
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param integer $colaId Identificador único de cola
     * @param integer $prioridad Prioridad del turno
     * @param object $turno Objeto turno
     * @return ValidateResultado
     * @throws \Exception
     */
    public function zaddCola($puntoAtencionId, $colaId, $prioridad, $turno)
    {
        $errors = [];
        $fecha = new \DateTime();
        $hora = new \DateTime($turno->getHora());

        $tramiteArray = [
            'tramite' => $turno->getTramite(),
            'codigo' => $turno->getCodigo(),
            'horario' => $hora->format('H:i'),
            'cuil' => $turno->getDatosTurno()->getCuil(),
            'nombre' => $turno->getDatosTurno()->getNombre(),
            'apellido' => $turno->getDatosTurno()->getApellido(),
            'cola' => $colaId
        ];

        // agregamos el turno a la cola solo si está marcado como recepcionado o en transcurso
        if ($turno->getEstado() != Turno::ESTADO_TERMINADO ) {
            $val = $this->getContainerRedis()->zadd(
                'puntoAtencion:' . $puntoAtencionId . ':cola:' . $colaId,
                $prioridad . $fecha->getTimestamp(),
                json_encode($tramiteArray)
            );

            if ($val == 0) {
                $errors['errors'] = 'El turno ya se encuentra en el Sistema de Colas.';
                return new ValidateResultado(null, $errors);
            }
        }

        return new ValidateResultado($turno, []);
    }

    /**
     * Unifica las colas de una ventanilla
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param integer $ventanilla Identificador único de ventanilla
     * @param $tx | transacción
     * @return ValidateResultado
     * @throws \Exception
     */
    public function unionColas($puntoAtencionId, $ventanilla, $tx = null)
    {
        $errors = [];
        $keys = [];

        if ($tx) {
            $redis = $tx;
        } else {
            $redis = $this->getContainerRedis();
        }

        foreach ($ventanilla->getColas() as $cola) {
            if ($this->exists('puntoAtencion:' . $puntoAtencionId . ':cola:' . $cola->getId(), $tx) > 0) {
                $keys[] = 'puntoAtencion:' . $puntoAtencionId . ':cola:' . $cola->getId();
            }
        }

        if(count($keys) > 0) {
            $val = $redis->zunionstore('puntoAtencion:' . $puntoAtencionId . ':ventanilla:' . $ventanilla->getId(), $keys);

            if ($val == 0) {
                $errors['errors'] = 'No se ha podido crear la cola';
            }
        } else {
            $errors['errors'] = 'No hay colas para generar la union.';
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Trae los sets de una cola, con paginación
     *
     * @param $key | key
     * @param integer $offset Inicio de la búsqueda
     * @param integer $limit Cantidad de resultados
     * @return mixed
     * @throws \Exception
     */
    private function zrangeCola($key, $offset, $limit)
    {
        return $this->getContainerRedis()->zrange($key, $offset, $limit);
    }

    /**
     * Obtiene el númereo de elementos de un set en una key
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    private function zcardCola($key)
    {
        return $this->getContainerRedis()->zcard($key);
    }

    /**
     * Obtiene los elementos de una cola con offset y limit
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param integer $colaId Identificador único de una cola
     * @param integer $offset Inicio de la búsqueda
     * @param integer $limit Cantidad de resultados
     * @return mixed
     * @throws \Exception
     */
    public function getCola($puntoAtencionId, $colaId, $offset, $limit)
    {
        return $this->zrangeCola('puntoAtencion:' . $puntoAtencionId . ':cola:' . $colaId, $offset, $limit);
    }

    /**
     * Obtiene todos los elementos de una cola
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param integer $colaId Identificador único de una cola
     * @return mixed
     *
     * @throws \Exception
     */
    public function getTotalCola($puntoAtencionId, $colaId)
    {
        return $this->zrangeCola('puntoAtencion:' . $puntoAtencionId . ':cola:' . $colaId, 0, -1);
    }

    /**
     * Obtiene la cantidad de elementos de una cola
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param integer $colaId Identificador único de una cola
     * @return mixed
     *
     * @throws \Exception
     */
    public function getCantidadCola($puntoAtencionId, $colaId)
    {
        return $this->zcardCola('puntoAtencion:' . $puntoAtencionId . ':cola:' . $colaId);
    }

    /**
     * Obtiene los elementos de todos las colas de una ventanilla
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param object $ventanilla ventanilla
     * @param integer $offset Inicio de la búsqueda
     * @param integer $limit Cantidad de resultados
     * @return mixed
     * @throws \Exception
     */
    public function getColaVentanilla($puntoAtencionId, $ventanilla, $offset, $limit)
    {
        if ($this->exists('puntoAtencion:' . $puntoAtencionId . ':ventanilla:' . $ventanilla->getId())) {
            $limit = ($offset + $limit) - 1;
            return $this->zrangeCola('puntoAtencion:' . $puntoAtencionId . ':ventanilla:' . $ventanilla->getId(), $offset, $limit);
        }

        return [];
    }

    /**
     * Quita el primer elemento de la cola y lo retorna
     *
     * @param integer $puntoAtencionId Identificador único de un punto de atención
     * @param object $ventanilla ventanilla
     * @return mixed
     * @throws \Exception
     */
    public function getProximoTurno($puntoAtencionId, $ventanilla)
    {
        $validateResult = $this->unionColas($puntoAtencionId, $ventanilla);

        if (! $validateResult->hasError()) {
            $cola = 'puntoAtencion:' . $puntoAtencionId . ':ventanilla:' . $ventanilla->getId();

            if ($this->exists($cola)) {
                $result = null;
                $options = array(
                    'cas' => true,      // Initialize with support for CAS operations
                    'watch' => $cola,    // Key that needs to be WATCHed to detect changes
                    'retry' => 3,       // Number of retries on aborted transactions, after
                    // which the client bails out with an exception.
                );

                // Executes a transaction inside the given callable block:
                $this->getContainerRedis()->transaction($options, function ($tx) use ($cola, $puntoAtencionId, $ventanilla, &$result) {
                    $turno = null;
                    $tx->multi();   // With CAS, MULTI *must* be explicitly invoked.

                    $validateResult = $this->unionColas($puntoAtencionId, $ventanilla, $tx);

                    if (! $validateResult->hasError()) {
                        $turno = $this->getFirstElementTransaction($cola, $tx);
                        $colaOriginal = 'puntoAtencion:' . $puntoAtencionId . ':cola:' . json_decode($turno)->cola;
                        $this->removeTransaction($colaOriginal, $turno, $tx);
                        $this->removeTransaction($cola, $turno, $tx);
                    } else {
                        $turnoRecepcionado = $this->getFirstElementTransaction($cola, $tx);
                        $this->removeTransaction($cola, $turnoRecepcionado, $tx);
                    }
                    $result = $turno;
                });

                return $result;
            }
        }

        $this->deleteKey('puntoAtencion:' . $puntoAtencionId . ':ventanilla:' . $ventanilla->getId());
    }

    /**
     * Remueve un miembro específico de una transacción
     * @param $cola
     * @param $value
     * @return mixed
     */
    private function removeTransaction($cola, $value, $tx)
    {
        return $tx->zrem($cola, $value);
    }

    /**
     * Elimina una Key específica
     *
     * @param $key | key
     * @throws \Exception
     */
    private function deleteKey($key)
    {
        $this->getContainerRedis()->del($key);
    }


    /**
     * Obtiene el primer elemento de uan transacción
     *
     * @param $cola | cola
     * @return mixed
     */
    private function getFirstElementTransaction($cola, $tx)
    {
        $turnos = $tx->zrange($cola, 0, -1);
        return $turnos[0];
    }

    /**
     * Obtiene la posicion de un turno en la oola
     * @param object $turno Turno
     * @param object $cola Cola
     * @return int
     * @throws \Exception
     */
    public function getPosicion($turno, $cola)
    {
        $turnos = $this->getCola(
            $turno->getPuntoAtencion()->getId(),
            $cola->getId(),
            0,
            -1
        );

        for ($i = 0; $i < count($turnos); $i++) {
            if (json_decode($turnos[$i])->codigo == $turno->getCodigo()) {
               return $i;
            }
        }

        return -1;
    }

    /**
     * verifica si existe una Key
     *
     * @param $key | key
     * @param null|object $tx Transacción
     * @return mixed
     * @throws \Exception
     */
    private function exists($key, $tx = null)
    {
        if ($tx) {
            return $tx->exists($key);
        }
        return $this->getContainerRedis()->exists($key);
    }

}