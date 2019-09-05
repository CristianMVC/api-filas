<?php
namespace ApiV1Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DatosTurno
 * @package ApiV1Bundle\Entity
 *
 * DatosTurno
 *
 * @ORM\Table(name="datos_turno")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\DatosTurnoRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */

class DatosTurno
{
    /**
     * Identificador único de datos de un turno, autoincremental
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Propiedad para la relación entre los datos del turno y el turno
     * Los datos del tramite le corresponden a un solo turno
     *
     * @var Turno
     * @ORM\OneToMany(targetEntity="Turno", mappedBy="datosTurno",  cascade={"persist"})
     */
    private $turno;

    /**
     * Nombre del ciudadano
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @Assert\Email(
     *     message = "El nombre del ciudadano es obligatorio.",
     * )
     * @ORM\Column(name="nombre", type="string", length=80)
     */
    private $nombre;

    /**
     * Apellido del ciudadano
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @Assert\Email(
     *     message = "El apellido del ciudadano es obligatorio.",
     * )
     * @ORM\Column(name="apellido", type="string", length=80)
     */
    private $apellido;

    /**
     * Número de CUIL / CUIT del ciudadano
     *
     * @var integer
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @ORM\Column(name="cuil", type="string", length=50)
     */
    private $cuil;

    /**
     * Email del ciudadano
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @Assert\Email(
     *     message = "Debe ingresar un email válido ej. juan@gmail.com.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * Teléfono del ciudadano
     *
     * @var string
     * @Assert\Type(
     *     type="string",
     *     message="Este campo debe contener solo números."
     * )
     * @ORM\Column(name="telefono", type="string", length=255, nullable=true)
     */
    private $telefono;

    /**
     * JSON conteniedo datos del ciudadano
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @Assert\Type(
     *     type="object",
     *     message="Este campo debe contener solo caracteres."
     * )
     * @ORM\Column(name="campos", type="json_array")
     */
    private $campos;

    /**
     * Fecha de creación
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_creado", type="datetime")
     */
    private $fechaCreado;

    /**
     * Fecha de modificación
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_modificado", type="datetime")
     */
    private $fechaModificado;

    /**
     * Fecha de borrado
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;

    /**
     * DatosTurno constructor.
     *
     * @param integer $cuil CUIT o CUIL del ciudadano
     * @param string $email email del ciudadano
     * @param string $telefono teléfono del ciudadano
     * @param array $campos colección de datos del ciudadano
     */
    public function __construct($nombre, $apellido, $cuil, $email, $telefono, $campos)
    {
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setCuil($cuil);
        $this->setEmail($email);
        $this->setTelefono($telefono);
        $this->setCampos($campos);
    }

    /**
     * Obtiene el Identificador único de datos del turno
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtiene el nombre del ciudadano
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Setea el nombre del ciudadano
     *
     * @param string $nombre
     * @return DatosTurno
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        return $this;
    }

    /**
     * Obtiene el apellido del ciudadano
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Setea el apellido del ciudadano
     *
     * @param string $apellido
     * @return DatosTurno
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
        return $this;
    }

    /**
     * Setea el CUIL o CUIT del ciudadano
     *
     * @param integer $cuil
     * @return DatosTurno
     */
    public function setCuil($cuil)
    {
        $this->cuil = $cuil;

        return $this;
    }

    /**
     * Obtiene el CUIL o CUIT del ciudadano
     *
     * @return integer
     */
    public function getCuil()
    {
        return $this->cuil;
    }

    /**
     * Setea el email del ciudadano
     *
     * @param string $email
     * @return DatosTurno
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Obtiene el email del ciudadano
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Setea el teléfono del ciudadano
     *
     * @param string $telefono
     * @return DatosTurno
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Obtiene el teléfono del ciudadano
     *
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Setea la colección de datos del ciudadano
     *
     * @param array $campos
     * @return DatosTurno
     */
    public function setCampos($campos)
    {
        $this->campos = $campos;

        return $this;
    }

    /**
     * Obtiene una colección de datos del ciudadano
     *
     * @return string
     */
    public function getCampos()
    {
        return $this->campos;
    }

    /**
     * Setea la fecha de creación
     *
     * @param \DateTime $fechaCreado
     * @return DatosTurno
     */
    public function setFechaCreado($fechaCreado)
    {
        $this->fechaCreado = $fechaCreado;

        return $this;
    }

    /**
     * Obtiene la fecha de creación
     *
     * @return \DateTime
     */
    public function getFechaCreado()
    {
        return $this->fechaCreado;
    }

    /**
     * Setea la fecha de modificación
     *
     * @param \DateTime $fechaModificado
     * @return DatosTurno
     */
    public function setFechaModificado($fechaModificado)
    {
        $this->fechaModificado = $fechaModificado;

        return $this;
    }

    /**
     * Obtiene la fecha de modificación
     *
     * Get fechaModificado
     * @return \DateTime
     */
    public function getFechaModificado()
    {
        return $this->fechaModificado;
    }

    /**
     * Setea el turno
     *
     * @param Turno $turno
     * @return DatosTurno
     */
    public function setTurno(Turno $turno = null)
    {
        $this->turno = $turno;

        return $this;
    }

    /**
     * Obtiene el turno
     *
     * @return Turno
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Genera las fechas de creación y modificación del turno
     *
     * @ORM\PrePersist
     */
    public function setFechas()
    {
        $this->fechaCreado = new \DateTime();
        $this->fechaModificado = new \DateTime();
    }

    /**
     * Actualiza la fecha de modificación del turno
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
