<?php

namespace ApiV1Bundle\Repository;
use ApiV1Bundle\Entity\Cola;

/**
 * ColaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ColaRepository extends ApiRepository
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->getEntityManager()->getRepository('ApiV1Bundle:Cola');
    }

    /**
     * Obtiene todas las colas para un punto de atención
     *
     * @param integer $puntoAtencionId Identificador de punto de atención
     * @param integer $offset Cantidad de registros a saltar
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param null $tipo Tipo de cola
     * @return array
     */
    public function findAllPaginate($puntoAtencionId, $offset, $limit, $tipo = null)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select([
            'c.id',
            'c.nombre',
            'c.tipo'
        ]);
        $query->where('c.puntoAtencion = :puntoAtencionId');
        $query->setParameter('puntoAtencionId', $puntoAtencionId);

        if ($tipo) {
            $query->andWhere('c.tipo = :tipo')->setParameter('tipo', $tipo);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('c.id', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * Devuelve todas las colas postas
     *
     * @param $puntoAtencionId
     * @return array
     */
    public function findAllPostas($puntoAtencionId)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->join('c.ventanillas', 'v');
        $query->where('c.puntoAtencion = :puntoAtencionId');
        $query->andWhere('c.tipo = :tipo');

        $query
            ->setParameter('puntoAtencionId', $puntoAtencionId)
            ->setParameter('tipo', Cola::TIPO_POSTA);

        $query->orderBy('c.id', 'ASC');
        return $query->getQuery()->getResult();
    }

    /**
     * Total colas por punto de atención
     *
     * @param integer $puntoAtencionId
     * @param null $tipo
     * @return number
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotal($puntoAtencionId, $tipo = null)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->where('c.puntoAtencion = :puntoAtencionId');
        $query->setParameter('puntoAtencionId', $puntoAtencionId);

        if ($tipo) {
            $query->andWhere('c.tipo = :tipo')->setParameter('tipo', $tipo);
        }

        $total = $query->getQuery()->getSingleScalarResult();
        return (int) $total;
    }

    /**
     * Colas por grupo de trámite
     *
     * @param integer $grupoTramiteSNTId El identificador del grupo de trámite para SNT
     * @return integer
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function GetByGrupotramite($grupoTramiteSNTId)
    {
        $query = $this->getRepository()->createQueryBuilder('c');
        $query->select('c.id');
        $query->where('c.grupoTramiteSNTId = :grupoTramiteSNTId');
        $query->setParameter('grupoTramiteSNTId', $grupoTramiteSNTId);
        return $query->getQuery()->getOneOrNullResult();
    }
}
