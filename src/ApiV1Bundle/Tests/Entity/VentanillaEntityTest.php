<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Ventanilla;

/**
 * Class VentanillaEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class VentanillaEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepo */
    private $ventanillaRepo;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

    public function setUp()
    {
        parent::setUp();
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
        $this->ventanillaRepo = $this->em->getRepository('ApiV1Bundle:Ventanilla');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoatencionRepo->find(1);
        $ventanilla = new Ventanilla('TestCreate',$puntoAtencion);
        $this->em->persist($ventanilla);
        // test
        $this->assertEquals('TestCreate', trim($ventanilla->getIdentificador()));
        // save
        $this->em->flush();
        // return
        return $ventanilla->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $ventanilla = $this->ventanillaRepo->find($id);
        // test
        $this->assertEquals('TestCreate', trim($ventanilla->getIdentificador()));
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
        $ventanilla = $this->ventanillaRepo->find($id);
        $ventanilla->setIdentificador('TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $ventanilla = $this->ventanillaRepo->find($id);
        // test
        $this->assertEquals('TestUpdate', trim($ventanilla->getIdentificador()));
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
        $ventanilla = $this->ventanillaRepo->find($id);
        $this->em->remove($ventanilla);
        // save
        $this->em->flush();
        // recover again
        $ventanilla = $this->ventanillaRepo->find($id);
        //$this->assertEquals(null, $ventanilla);
        $this->assertNotEquals(null, $ventanilla->getFechaBorrado());
    }

}