<?php

namespace ApiV1Bundle\Repository;

/**
 * Class CarteleraRepository
 * @package ApiV1Bundle\Repository
 *
 */

class CarteleraRepository extends ApiRepository
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->getEntityManager()->getRepository('ApiV1Bundle:Cartelera');
    }

    /**
     * Listado de carteleras por punto de atención
     *
     * @param integer $puntoAtencionId Identificador de punto de atención
     * @param integer $offset Cantidad de registros a saltar
     * @param integer $limit Cantidad máxima de registros a retornar
     * @return array
     */
    public function findAllPaginate($puntoAtencionId, $offset, $limit)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select([
            'c.id',
            'c.nombre'
        ]);
        $query->where('c.puntoAtencion = :puntoAtencionId');
        $query->setParameter('puntoAtencionId', $puntoAtencionId);
        //$query->join('c.colas', 't');
        $query->orderBy('c.id', 'ASC');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        return $query->getQuery()->getResult();
    }

    /**
     * Listado de colas por cartelera
     *
     * @param integer $carteleraId Identificador de cartelera
     * @return array
     */
    public function findColas($carteleraId)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select([
            'v.id',
            'v.nombre'
        ]);
        $query->join('c.colas','v');
        $query->where('c.id = :id');
        $query->setParameter('id', $carteleraId);
        $query->orderBy('v.id', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * Listado de carteleras por cola
     *
     * @param integer $colaId Identificador de cola
     * @return array
     */
    public function findAllByCola($colaId)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select(
            'c.id',
            'c.nombre'
        );
        $query->join('c.colas','v');
        $query->where('v.id = :id');
        $query->setParameter('id', $colaId);
        return $query->getQuery()->getResult();
    }

    /**
     * Total carteleras por punto de atención
     *
     * @param integer $puntoAtencionId id del punto de atención
     * @return number
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotal($puntoAtencionId)
    {
        $query = $this->getRepository()->createQueryBuilder('v');
        $query->select('count(v.id)');
        $query->where('v.puntoAtencion = :puntoAtencionId');
        $query->setParameter('puntoAtencionId', $puntoAtencionId);
        $total = $query->getQuery()->getSingleScalarResult();
        return (int) $total;
    }

}
