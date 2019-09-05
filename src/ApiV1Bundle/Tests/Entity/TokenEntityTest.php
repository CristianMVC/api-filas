<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\DatosTurno;
use ApiV1Bundle\Entity\Token;
use ApiV1Bundle\Entity\Turno;

/**
 * Class TokenEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class TokenEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\TokenRepository $tokenRepo  */
    private $tokenRepo;

    /** @var string $tokenString token de ejemplo */
    private $tokenString;

    public function setUp()
    {
        parent::setUp();
        $this->tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1';
        $this->tokenRepo = $this->em->getRepository('ApiV1Bundle:Token');
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $token = new Token($this->tokenString);

        $this->em->persist($token);
        // test
        $this->assertEquals($this->tokenString, trim($token->getToken()));

        // save
        $this->em->flush();
        // return
        return $token->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $token = $this->tokenRepo->find($id);
        // test
        $this->assertEquals($this->tokenString, trim($token->getToken()));
        $this->assertNotEquals(null, $token->getToken());
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
        $token = $this->tokenRepo->find($id);
        $token->setToken('TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $token = $this->tokenRepo->find($id);
        $this->assertEquals('TestUpdate', trim($token->getToken()));
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
        $token = $this->tokenRepo->find($id);
        $this->em->remove($token);
        // save
        $this->em->flush();
        // recover again
        $token = $this->tokenRepo->find($id);
        $this->assertEquals(null, $token);
    }

}