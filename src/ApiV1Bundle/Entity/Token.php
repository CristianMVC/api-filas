<?php

namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Class Token
 * @package ApiV1Bundle\Entity
 *
 * Token
 *
 * @ORM\Table(name="token", indexes={@Index(name="search_idx", columns={"token"})})
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\TokenRepository")
 */
class Token
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=32)
     */
    private $token;

    /*
     * token construct
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Obtener el id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * asignar el token
     *
     * @param string $token
     * @return token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Obtener el token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
