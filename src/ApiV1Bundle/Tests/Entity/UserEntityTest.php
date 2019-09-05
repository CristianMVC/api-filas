<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\User;

/**
 * Class UserEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class UserEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\UserRepository $userRepo */
    private $userRepo;

    public function setUp()
    {
        parent::setUp();
        $this->userRepo = $this->em->getRepository('ApiV1Bundle:User');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $user = new User('usernameTestCreate', 1);
        $this->em->persist($user);
        // test
        $this->assertEquals('usernameTestCreate', trim($user->getUsername()));
        $this->assertEquals(1, $user->getRol());
        // save
        $this->em->flush();
        // return
        return $user->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $user = $this->userRepo->find($id);
        // test
        $this->assertEquals('usernameTestCreate', trim($user->getUsername()));
        $this->assertEquals(1, $user->getRol());
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
        $user = $this->userRepo->find($id);
        $user->setUsername('usernameTestUpdate');
        // save
        $this->em->flush();
        // recover again
        $user = $this->userRepo->find($id);
        $this->assertEquals('usernameTestUpdate', trim($user->getUsername()));
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
        $user = $this->userRepo->find($id);
        $this->em->remove($user);
        // save
        $this->em->flush();
        // recover again
        $user = $this->userRepo->find($id);
        $this->assertNotNull($user->getFechaBorrado());
    }
}
