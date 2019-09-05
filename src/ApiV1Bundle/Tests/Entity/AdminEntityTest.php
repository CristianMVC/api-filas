<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\Admin;
use ApiV1Bundle\Entity\User;

/**
 * Class AdminEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class AdminEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\AdminRepository $adminRepo */
    private $adminRepo;

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
        $this->adminRepo = $this->em->getRepository('ApiV1Bundle:Admin');
        $this->userRepo = $this->em->getRepository('ApiV1Bundle:User');
        $this->randomUsername = $this->generateRandomString(10).'@test.create';
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $user = new User($this->randomUsername, 1);
        $admin = new Admin('Nombre Admin TestCreate', 'Apellido TestCreate', $user);
        $this->em->persist($admin);
        // test
        $this->assertEquals('Nombre Admin TestCreate', trim($admin->getNombre()));

        // save
        $this->em->flush();
        // return
        return $admin->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $admin = $this->adminRepo->find($id);
        // test
        $this->assertEquals('Nombre Admin TestCreate', trim($admin->getNombre()));
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
        $admin = $this->adminRepo->find($id);
        $admin->setNombre('Nombre Admin TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $admin = $this->adminRepo->find($id);
        $this->assertEquals('Nombre Admin TestUpdate', trim($admin->getNombre()));
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
        $admin = $this->adminRepo->find($id);
        $this->em->remove($admin);
        // save
        $this->em->flush();
        // recover again - debe fallar
        $admin = $this->adminRepo->find($id);
        $this->assertNotEquals(null, $admin->getFechaBorrado());
    }
}
