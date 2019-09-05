<?php
namespace ApiV1Bundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

/**
 * Class ApiRepository
 * @package ApiV1Bundle\Repository
 */
class ApiRepository extends EntityRepository
{
    /** @var EntityManager $em */
    protected $em;

    /**
     * ApiRepository constructor.
     *
     * @param EntityManager $em
     * @param Mapping\ClassMetadata $class
     */

    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->setEm($em);
    }

    /**
     * Retorna un entity manager para área
     *
     * @return EntityManager
     */
    protected function getEm()
    {
        return $this->em;
    }

    /**
     * Setea un handler para un entity manager
     *
     * @param EntityManager $em
     */
    protected function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Persiste los datos y luego hace flush
     *
     * @param $entity
     * @return Object
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save($entity)
    {
        $this->persist($entity);
        $this->flush();

        return $entity;
    }

    /**
     * Agrega la entidad para luego persistir los datos
     *
     * @param $entity
     */
    public function persist($entity)
    {
        $this->getEm()->persist($entity);
    }

    /**
     * Remueve una entidad
     *
     * @param $entity
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove($entity)
    {
        $this->getEm()->remove($entity);
        $this->flush();
    }

    /**
     * Ejecuta un flush
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush()
    {
        $this->getEm()->flush();
    }

    /**
     * Begin Transaction
     */
    public function beginTransaction()
    {
        $this->getEm()->getConnection()->beginTransaction();
    }

    /**
     * Commit
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit()
    {
        $this->getEm()->getConnection()->commit();
    }

    /**
     * Rollback
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollback()
    {
        $this->getEm()->getConnection()->rollBack();
    }
}