<?php
namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiV1Bundle\Helper\ServicesHelper;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class User
 * @package ApiV1Bundle\Entity
 *
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\UserRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, \Serializable
{

    const ROL_ADMIN = 1;
    const ROL_RESPONSABLE = 4;
    const ROL_AGENTE = 5;

    private $roles = [
        1 => 'ROL_ADMIN',
        4 => 'ROL_RESPONSABLE',
        5 => 'ROL_AGENTE'
    ];

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
     * @ORM\Column(name="username", type="string", length=128)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=60)
     */
    private $password;

    /**
     * @var int
     *
     * @ORM\Column(name="rol_id", type="smallint")
     */
    private $rol;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_creado", type="datetimetz")
     */
    private $fechaCreado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_modificado", type="datetimetz")
     */
    private $fechaModificado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;

    /**
     * User constructor
     *
     * @param string $username
     * @param integer $rol
     */
    public function __construct($username, $rol)
    {
        $this->username = $username;
        $this->password = ServicesHelper::randomPassword(12);
        $this->rol = $rol;
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
     * Obtiene el username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Obtiene el password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /** Asigna el password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Asigna el username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Obtiene el rol
     *
     * @return int
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * The user rol
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getRoles()
     */
    public function getRoles()
    {
        return $this->roles[$this->rol];
    }

    /**
     * Password salt
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Erase user credentials
     *
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     */
    public function eraseCredentials()
    {
    }

    /**
     * Serialize user
     *
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    /**
     * Unserialize user
     *
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list ($this->id, $this->username, $this->password) = unserialize($serialized);
    }

    /**
     * Genera las fechas de creación y modificación
     *
     * @ORM\PrePersist
     */
    public function setFechas()
    {
        $this->fechaCreado = new \DateTime();
        $this->fechaModificado = new \DateTime();
    }

    /**
     * Actualiza la fecha de modificación
     *
     * @ORM\PreUpdate
     */
    public function updatedFechas()
    {
        $this->fechaModificado = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getFechaBorrado()
    {
        return $this->fechaBorrado;
    }
}
