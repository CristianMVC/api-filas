<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\Cola;

/**
 * Class ColaEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class ColaEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\ColaRepository $colaRepo */
    private $colaRepo;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

    public function setUp()
    {
        parent::setUp();
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
        $this->colaRepo = $this->em->getRepository('ApiV1Bundle:Cola');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoatencionRepo->find(1);
        $cola = new Cola('Cola TestCreate',$puntoAtencion,1);
        $cola->setGrupoTramiteSNTId(1);
        $this->em->persist($cola);
        // test
        $this->assertEquals('Cola TestCreate', trim($cola->getNombre()  ));
        $this->assertEquals(1, trim($cola->getGrupoTramiteSNTId()));
        // save
        $this->em->flush();
        // return
        return $cola->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $cola = $this->colaRepo->find($id);
        // test
        $this->assertEquals('Cola TestCreate', trim($cola->getNombre()));
        $this->assertEquals(1, trim($cola->getGrupoTramiteSNTId()));
        //test total colas por punto de atención
        $this->assertGreaterThan(0,$this->colaRepo->getTotal(1) );
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
        $cola = $this->colaRepo->find($id);
        $cola->setNombre('Cola TestUpdate');
        $cola->setGrupoTramiteSNTId(2);
        // save
        $this->em->flush();
        // recover again
        $cola = $this->colaRepo->find($id);
        $this->assertEquals('Cola TestUpdate', trim($cola->getNombre()));
        $this->assertEquals(2, trim($cola->getGrupoTramiteSNTId()));
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
        $cola = $this->colaRepo->find($id);
        $this->em->remove($cola);
        // save
        $this->em->flush();
        // recover again
        $cola = $this->colaRepo->find($id);
        $this->assertNotEquals(null, $cola->getFechaBorrado());
    }

}