<?php

namespace ApiV1Bundle\Repository;

class UsuarioRepository extends ApiRepository
{
    /**
     * Obtiene el repositorio
     *
     * @return \Doctrine\ORM\EntityRepository
     */

    private function getRepository()
    {
        return $this->getEntityManager()->getRepository('ApiV1Bundle:Usuario');
    }

    /**
     * Obtiene todos los usuarios con un resultado paginado
     *
     * @param integer $offset Cantidad de registros a saltar
     * @param integer $limit Cantidad máxima de registros a retornar
     * @return array
     */
    public function findAllPaginate($offset, $limit)
    {
        $query = $this->getRepository()->createQueryBuilder('u');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('u.user', 'ASC');
        return  $query->getQuery()->getResult();
    }

    /**
     * Obtiene el total de usuarios
     *
     * @return integer
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotal()
    {
        $query = $this->getRepository()->createQueryBuilder('u');
        $query->select('count(u.id)');
        $total = $query->getQuery()->getSingleScalarResult();
        return (int) $total;
    }
}