<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\PuntoAtencion;

/**
 * Class PuntoAtencionEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class PuntoAtencionEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

    public function setUp()
    {
        parent::setUp();
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        // punto atencion
        $puntoAtencion = new PuntoAtencion();
        $puntoAtencion->setPuntoAtencionIdSnt(rand(1, 25000));
        $puntoAtencion->setNombre('Punto de atención TestCreate');
        $this->em->persist($puntoAtencion);
        // test
        $this->assertEquals('Punto de atención TestCreate', trim($puntoAtencion->getNombre()));
        // save
        $this->em->flush();
        // return
        return $puntoAtencion->getId();
    }
    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $puntoAtencion = $this->puntoatencionRepo->find($id);
        // test
        $this->assertEquals('Punto de atención TestCreate', trim($puntoAtencion->getNombre()));
        //test total puntos de atención
        $this->assertGreaterThan(0, $this->puntoatencionRepo->getTotal());
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
        $puntoAtencion = $this->puntoatencionRepo->find($id);
        $puntoAtencion->setNombre('Punto de atención TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $puntoAtencion = $this->puntoatencionRepo->find($id);
        $this->assertEquals('Punto de atención TestUpdate', trim($puntoAtencion->getNombre()));
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
        $puntoAtencion = $this->puntoatencionRepo->find($id);
        $this->em->remove($puntoAtencion);
        // save
        $this->em->flush();
        // recover again
        $puntoAtencion = $this->puntoatencionRepo->find($id);
        $this->assertNotEquals(null, $puntoAtencion->getFechaBorrado());
    }
}
