<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\Cartelera;

/**
 * Class CarteleraEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class CarteleraEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\CarteleraRepository*/
    private $carteleraRepo;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

    /** @var \ApiV1Bundle\Repository\ColaRepository */
    private $colaRepo;

    public function setUp()
    {
        parent::setUp();
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
        $this->carteleraRepo = $this->em->getRepository('ApiV1Bundle:Cartelera');
        $this->colaRepo = $this->em->getRepository('ApiV1Bundle:Cola');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoatencionRepo->find(1);
        $cartelera = new Cartelera($puntoAtencion, 'TestCreate');
        $this->em->persist($cartelera);
        // test
        $this->assertEquals('TestCreate', trim($cartelera->getNombre()));
        // save
        $this->em->flush();
        // return
        return $cartelera->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $cartelera = $this->carteleraRepo->find($id);
        // test
        $this->assertEquals('TestCreate', trim($cartelera->getNombre()));
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
        $cartelera = $this->carteleraRepo->find($id);
        $cola = $this->colaRepo->find(1);
        $cartelera->setNombre('TestUpdate');
        $cartelera->addCola($cola);
        // save
        $this->em->flush();
        // recover again
        $cartelera = $this->carteleraRepo->find($id);
        // test
        $this->assertEquals('TestUpdate', trim($cartelera->getNombre()));
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
        $cartelera = $this->carteleraRepo->find($id);
        $this->em->remove($cartelera);
        // save
        $this->em->flush();
        // recover again
        $cartelera = $this->carteleraRepo->find($id);
        $this->assertNotEquals(null, $cartelera->getFechaBorrado());
    }

}