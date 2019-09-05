<?php

namespace ApiV1Bundle\Repository;
use ApiV1Bundle\ApplicationServices\TurnoServices;
use ApiV1Bundle\Entity\Turno;

/**
 * TurnoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TurnoRepository extends ApiRepository
{

    /**
     * Obtiene el repositorio
     *
     * @return \Doctrine\ORM\EntityRepository
     */

    private function getRepository()
    {
        return $this->getEntityManager()->getRepository('ApiV1Bundle:Turno');
    }

    /**
     * Busqueda de turnos por cuil y código
     *
     * @param string $cuil El CUIL del ciudadano
     * @param string $codigo El código del turno
     * @param integer $puntoAtencionId El identificador único del punto de atención
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function search($cuil, $codigo, $puntoAtencionId = null)
    {
        $query = $this->getRepository()->createQueryBuilder('t');
        $query->join('t.datosTurno', 'd');
        $query->where('d.cuil = :cuil')->setParameter('cuil', $cuil);
        $query->andWhere('lower(t.codigo) LIKE :codigo')->setParameter('codigo', strtolower($codigo) . '%');

        if (! is_null($puntoAtencionId)) {
            $query->andWhere('t.puntoAtencion = :pa')->setParameter('pa', $puntoAtencionId);
        }
        return $query->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * Busqueda de turnos ya recepcionados
     * @param string $fecha La fecha de los turnos
     * @param integer $puntoAtencionId El identificador único del punto de atención
     * @return mixed
     */
    public function turnosRecepcionados($fecha, $puntoAtencionId)
    {
        $fecha = new \DateTime($fecha);
        $fechaFormat = $fecha->format('Y-m-d');

        $query = $this->getRepository()->createQueryBuilder('t');
         $query->select([
            't.codigo'
        ]);

        $query->join('t.puntoAtencion', 'p');
        $query->where('p.puntoAtencionIdSnt = :puntoAtencionId')->setParameter('puntoAtencionId', $puntoAtencionId);
        $query->andWhere('t.fecha = :fecha')->setParameter('fecha', $fechaFormat);
        $query->orderBy('t.id', 'DESC');
        return $query->getQuery()->getResult();
    }
}