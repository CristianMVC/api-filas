<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\DatosTurno;
use ApiV1Bundle\Entity\Turno;

/**
 * Class DatosTurnoEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class DatosTurnoEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\TurnoRepository $turnoRepo*/
    private $turnoRepo;

    /** @var \ApiV1Bundle\Repository\DatosTurnoRepository $datosTurnoRepo*/
    private $datosTurnoRepo;

    public function setUp()
    {
        parent::setUp();
        $this->turnoRepo = $this->em->getRepository('ApiV1Bundle:Turno');
        $this->datosTurnoRepo = $this->em->getRepository('ApiV1Bundle:DatosTurno');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {        
        $datosTurno = new DatosTurno('Nombre TestCreate','Apellido TestCreate',1,'Test@Create.com','1145621485',['lorem' => 'ipsum', 'dolor' => 'sit amet']);
        $this->em->persist($datosTurno);
        // test
        $this->assertEquals('Nombre TestCreate', trim($datosTurno->getNombre()));

        // save
        $this->em->flush();
        // return
        return $datosTurno->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $datosTurno = $this->datosTurnoRepo->find($id);
        // test
        $this->assertEquals('Nombre TestCreate', trim($datosTurno->getNombre()));
        // return
        return $id;
    }

    /**
     * Test UPDATE
     * param Integer $Id identificador único que corresponde al ID que devuelve testRead
     * @depends testRead
     */
    public function testUpdate($id)
    {
        // update
        $datosTurno = $this->datosTurnoRepo->find($id);
        $datosTurno->setNombre('Nombre TestUpdate');
        $datosTurno->setApellido('Apellido TestUpdate');
        $datosTurno->setEmail('email@TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $datosTurno = $this->datosTurnoRepo->find($id);
        $this->assertEquals('Nombre TestUpdate', trim($datosTurno->getNombre()));
        $this->assertEquals('Apellido TestUpdate', trim($datosTurno->getApellido()));
        $this->assertEquals('email@TestUpdate', trim($datosTurno->getEmail()));
        // return
        return $id;
    }

    /**
     * Test DELETE
     * param Integer $Id identificador único que corresponde al ID que devuelve testUpdate
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        $datosTurno = $this->datosTurnoRepo->find($id);
        $this->em->remove($datosTurno);
        // save
        $this->em->flush();
        // recover again
        $datosTurno = $this->datosTurnoRepo->find($id);
        $this->assertNotEquals(null, $datosTurno->getFechaBorrado());
    }

}