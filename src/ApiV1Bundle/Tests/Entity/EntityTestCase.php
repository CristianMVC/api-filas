<?php
namespace ApiV1Bundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Ramsey\Uuid\Uuid;
use ApiV1Bundle\Entity\PuntoAtencion;

/**
 * Class EntityTestCase
 * @package ApiV1Bundle\Tests\Entity
 */
class EntityTestCase extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager $em ORM Doctrine
     */
    protected $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $kernel = static::$kernel;
        // entity manager
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        // close entity manager
        $this->em->close();
        // avoid memory leaks
        $this->em = null;
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass()
    {
        // kernel
        self::bootKernel();
    }
}