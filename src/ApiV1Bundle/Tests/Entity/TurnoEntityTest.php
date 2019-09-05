<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\DatosTurno;
use ApiV1Bundle\Entity\Turno;
use ApiV1Bundle\Entity\PuntoAtencion;

/**
 * Class TurnoEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class TurnoEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\DatosTurnoRepository $datosTurnoRepo */
    private $datosTurnoRepo;

    /** @var \ApiV1Bundle\Repository\TurnoRepository $turnoRepo*/
    private $turnoRepo;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepo */
    private $puntoAtencionRepo;

    public function setUp()
    {
        parent::setUp();
        $this->datosTurnoRepo = $this->em->getRepository('ApiV1Bundle:DatosTurno');
        $this->turnoRepo = $this->em->getRepository('ApiV1Bundle:Turno');
        $this->puntoAtencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoAtencionRepo->find(1);
        $datosTurno = $this->datosTurnoRepo->find(1);
        $turno = new Turno($puntoAtencion,$datosTurno,1,new \DateTime('2017-11-29T03:00:00.000Z'),new \DateTime('09:00'),'3','Tramite TestCreate','sDGFERvx6',1);
        $this->em->persist($turno);
        // test
        $this->assertEquals('sDGFERvx6', trim($turno->getCodigo()));
        $this->assertEquals(1, $turno->getPrioridad());
        // save
        $this->em->flush();
        // return
        return $turno->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $turno = $this->turnoRepo->find($id);
        // test
        $this->assertEquals('sDGFERvx6', trim($turno->getCodigo()));
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
        $turno = $this->turnoRepo->find($id);
        $turno->setMotivoTerminado('TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $turno = $this->turnoRepo->find($id);
        $this->assertEquals('TestUpdate', trim($turno->getMotivoTerminado()));
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
        $turno = $this->turnoRepo->find($id);
        $this->em->remove($turno);
        // save
        $this->em->flush();
        // recover again
        $turno = $this->turnoRepo->find($id);
        $this->assertNotEquals(null, $turno->getFechaBorrado());
    }

}