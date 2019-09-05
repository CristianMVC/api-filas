<?php
namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Admin
 * @package ApiV1Bundle\Entity\
 *
 * @ORM\Table(name="user_admin")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\AdminRepository")
 */
class Admin extends Usuario
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /*
     * Admin construct
     *
     * @param string $nombre
     * @param string $apellido
     * @param User $user
     */
    public function __construct($nombre, $apellido, $user)
    {
        parent::__construct($nombre, $apellido, $user);
    }

    /**
     * Obtiene el id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null
     */
    public function getPuntoAtencionId()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getNombrePuntoAtencion()
    {
        return '';
    }

    /**
     * @return null
     */
    public function getPuntoAtencionIdSnt()
    {
        return null;
    }
}
