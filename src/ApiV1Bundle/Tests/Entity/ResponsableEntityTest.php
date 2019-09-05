<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\Responsable;
use ApiV1Bundle\Entity\User;

/**
 * Class ResponsableEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class ResponsableEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository $responsableRepo*/
    private $responsableRepo;

    /** @var \ApiV1Bundle\Repository\UserRepository $userRepo*/
    private $userRepo;

    /** @var string $randomName */
    private $randomUsername;

    /**
     * generateRandomString
     * función que se utiliza para generar strings aleatorios para usarlos como parte del nombre de usuario.
     * @param int $length
     *
     * @return string
     */
    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function setUp()
    {
        parent::setUp();
        $this->responsableRepo = $this->em->getRepository('ApiV1Bundle:Responsable');
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
        $this->userRepo = $this->em->getRepository('ApiV1Bundle:User');
        $this->randomUsername = $this->generateRandomString(10).'@test.create';
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoatencionRepo->find(3);
        $user = new User($this->randomUsername, 1);
        $responsable = new Responsable('Nombre TestCreate', 'Apellido TestCreate', $puntoAtencion, $user);
        $this->em->persist($responsable);
        // test
        $this->assertEquals('Nombre TestCreate', trim($responsable->getNombre()));

        // save
        $this->em->flush();
        // return
        return $responsable->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $responsable = $this->responsableRepo->find($id);
        // test
        $this->assertEquals('Nombre TestCreate', trim($responsable->getNombre()));
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
        $responsable = $this->responsableRepo->find($id);
        $responsable->setNombre('Nombre TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $responsable = $this->responsableRepo->find($id);
        $this->assertEquals('Nombre TestUpdate', trim($responsable->getNombre()));
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
        $responsable = $this->responsableRepo->find($id);
        $this->em->remove($responsable);
        // save
        $this->em->flush();
        // recover again - debe fallar
        $responsable = $this->responsableRepo->find($id);
        $this->assertNotEquals(null, $responsable->getFechaBorrado());
    }
}
