<?php
namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Usuario
 * @package ApiV1Bundle\Entity
 *
 * Usuario
 * @ORM\MappedSuperclass
 */

/**
 * Class Usuario
 * @package ApiV1Bundle\Entity
 *
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\UsuarioRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"agente" = "Agente", "responsable" = "Responsable", "admin" = "Admin"})
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 */

abstract class Usuario
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=128)
     */
    protected $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellido", type="string", length=128)
     */
    protected $apellido;

    /**
     * Un usuario tiene un User
     * @ORM\OneToOne(targetEntity="User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;

    /**
     * Usuario constructor
     *
     * @param string $nombre
     * @param string $apellido
     * @param \ApiV1Bundle\Entity\User $user
     */
    protected function __construct($nombre, $apellido, $user)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->user = $user;
    }

    /**
     * Obtiene el nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Obtiene el apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Obtiene el objeto user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     *  Asigna el nombre
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Asigna el apellido
     * @param string $apellido
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    }

    /**
     * @return \DateTime
     */
    public function getFechaBorrado()
    {
        return $this->fechaBorrado;
    }

    abstract public function getPuntoAtencionId();

    abstract public function getNombrePuntoAtencion();

    abstract public function getPuntoAtencionIdSnt();
}
