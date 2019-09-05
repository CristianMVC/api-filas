<?php
namespace ApiV1Bundle\Tests\Entity;

use ApiV1Bundle\Entity\Agente;
use ApiV1Bundle\Entity\User;

/**
 * Class AgenteEntityTest
 * @package ApiV1Bundle\Tests\Entity
 */
class AgenteEntityTest extends EntityTestCase
{
    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepo */
    private $agenteRepo;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoatencionRepo */
    private $puntoatencionRepo;

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
        $this->puntoatencionRepo = $this->em->getRepository('ApiV1Bundle:PuntoAtencion');
        $this->agenteRepo = $this->em->getRepository('ApiV1Bundle:Agente');
        $this->userRepo = $this->em->getRepository('ApiV1Bundle:User');
        $this->randomUsername = $this->generateRandomString(10).'@test.create';
    }

    /**
     * Test CREATE
     * @return number
     */
    public function testCreate()
    {
        $puntoAtencion = $this->puntoatencionRepo->find(1);
        $user = new User($this->randomUsername, 5);
        $agente = new Agente('Nombre TestCreate', 'Apellido TestCreate', $puntoAtencion, $user);
        $this->em->persist($agente);
        // test
        $this->assertEquals('Nombre TestCreate', trim($agente->getNombre()));
        // save
        $this->em->flush();
        // return
        return $agente->getId();
    }

    /**
     * Test READ
     * param Integer $Id identificador único que corresponde al ID que devuelve testCreate
     * @depends testCreate
     */
    public function testRead($id)
    {
        $agente = $this->agenteRepo->find($id);
        // test
        $this->assertEquals('Nombre TestCreate', trim($agente->getNombre()));
        //test total agentes por punto de atención
        $this->assertGreaterThan(0, $this->agenteRepo->getTotal(1));
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
        $agente = $this->agenteRepo->find($id);
        $agente->setNombre('Nombre TestUpdate');
        // save
        $this->em->flush();
        // recover again
        $agente = $this->agenteRepo->find($id);
        $this->assertEquals('Nombre TestUpdate', trim($agente->getNombre()));
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
        $agente = $this->agenteRepo->find($id);
        $this->em->remove($agente);
        // save
        $this->em->flush();
        // recover again - debe fallar
        $agente = $this->agenteRepo->find($id);
        $this->assertNotEquals(null, $agente->getFechaBorrado());
    }
}
