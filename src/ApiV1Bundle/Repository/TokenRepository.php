<?php
namespace ApiV1Bundle\Repository;

/**
 * TokenRepository
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TokenRepository extends ApiRepository
{

    /**
     * Obtiene el repositorio
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->getEntityManager()->getRepository('ApiV1Bundle:Token');
    }
}
