<?php

namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Cartelera
 * @package ApiV1Bundle\Entity
 *
 * @ORM\Table(name="cartelera")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\CarteleraRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class Cartelera
{
    /**
     * Identificador único de un grupo de la cartelera
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Nombre de la cartelera
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @Assert\Type(
     *     type="string",
     *     message="Este campo debe contener solo caracteres."
     * )
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * Fecha de creación de la cartelera
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_creado", type="datetimetz")
     */
    private $fechaCreado;

    /**
     * Fecha de modificación de la cartelera
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_modificado", type="datetimetz")
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
     * Campo para referenciar una cartelera con un punto de antención
     *
     * @ORM\ManyToOne(targetEntity="PuntoAtencion")
     * @ORM\JoinColumn(name="puntoAtencion_id", referencedColumnName="id")
     **/
    private $puntoAtencion;

    /**
     * Campo para referenciar una cartelera con una cola
     *
     * @ORM\ManyToMany(targetEntity="Cola")
     * @ORM\JoinTable(name="cartelera_cola")
     **/
    private $colas;

    /**
     * Cartelera constructor
     *
     * @param object $puntoAtencion
     * @param string $nombre
     */
    public function __construct($puntoAtencion, $nombre)
    {
        $this->setPuntoAtencion($puntoAtencion);
        $this->setNombre($nombre);
        $this->colas = new ArrayCollection();
    }

    /**
     * Obtiene el identificador de la cartelera
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setea el nombre de la cartelera
     *
     * @param string $nombre
     * @return Cartelera
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Obtiene el nombre de la cartelera
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Obtiene el punto de atención para una cartelera
     *
     * @return mixed
     */
    public function getPuntoAtencion()
    {
        return $this->puntoAtencion;
    }

    /**
     *  asigna el punto de atención para una cartelera
     *
     * @param mixed $puntoAtencion
     */
    public function setPuntoAtencion($puntoAtencion)
    {
        $this->puntoAtencion = $puntoAtencion;
    }

    /**
     * Obtiene el listado de colas
     *
     * @return $colas
     */
    public function getColas()
    {
        return $this->colas;
    }

    /**
     * elimina todas las colas asociadas
     *
     */
    public function clearColas()
    {
        $this->colas->clear();
    }

    /**
     * Agrega una cola a la cartelera
     * @param  Cola $cola
     * @return $this
     */
    public function addCola(Cola $cola)
    {
        $this->colas[] = $cola;
        return $this->getColas();
    }

    /**
     * Borra una cola de la cartelera
     *
     * @param  Cola $cola
     */
    public function removeCola(Cola $cola)
    {
        $this->colas->removeElement($cola);
        return $this->getColas();
    }

    /**
     * Genera las fechas de creación y modificación de la cartelera
     *
     * @ORM\PrePersist
     */
    public function setFechas()
    {
        $this->fechaCreado = new \DateTime();
        $this->fechaModificado = new \DateTime();
    }

    /**
     * Actualiza la fecha modificación de la cartelera
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