<?php
namespace ApiV1Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Ventanilla
 * @package ApiV1Bundle\Entity
 *
 * Ventanilla
 *
 * @ORM\Table(name="ventanilla")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\VentanillaRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class Ventanilla
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
     * Identificador alfanumerico de la ventanilla
     * @var string
     * @Assert\NotNull(
     *     message="El campo Identificador no puede estar vacío"
     * )
     * @Assert\Type(
     *     type="string",
     *     message="Este campo Identificador solo acepta caracteres alfanumerico."
     * )
     * @ORM\Column(name="identificador", type="string", length=15)
     */
    private $identificador;

    /**
     * Colección de colas que atiende una ventanilla
     * Ventanilla puede pertenecer a N colas
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Cola", inversedBy="ventanillas")
     * @ORM\JoinTable(name="cola_ventanilla")
     **/
    private $colas;

    /**
     * Colección de agentes que atiende una ventanilla
     * Ventanilla puede pertenecer a N agentes
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Agente", mappedBy="ventanillas")
     */
    private $agentes;

    /**
     * @var int
     * //TODO cambiar la relacion con punto de atencion
     * @ORM\ManyToOne(targetEntity="PuntoAtencion")
     * @ORM\JoinColumn(name="punto_atencion_id", referencedColumnName="id")
     */
    protected $puntoAtencion;

    /**
     * Fecha de creación de la ventanilla
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_creado", type="datetimetz")
     */
    private $fechaCreado;

    /**
     * Fecha de modificación de la ventanilla
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_modificado", type="datetimetz")
     */
    private $fechaModificado;

    /**
     * Fecha de borrado de la ventanilla
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;

    /**
     * Ventanilla constructor
     *
     * @param string $identificador
     * @param integer $puntoAtencion
     */
    public function __construct($identificador, $puntoAtencion)
    {
        $this->identificador = $identificador;
        $this->puntoAtencion = $puntoAtencion;
        $this->agentes = new ArrayCollection();
        $this->colas = new ArrayCollection();
    }

    /**
     * Obtiene el id (Identificador único)
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtener el punto de atención
     * @return int
     */
    public function getPuntoAtencion()
    {
        return $this->puntoAtencion;
    }

    /**
     * Obtiene la lista de colas
     *
     * @return ArrayCollection
     */
    public function getColas()
    {
        return $this->colas;
    }

    /**
     * Agrega una cola a una ventanilla
     *
     * @param Cola $cola
     * @return ArrayCollection
     */
    public function addCola(Cola $cola)
    {
        $this->colas[] = $cola;
        return $this->getColas();
    }

    /**
     * Remueve una cola de una ventanilla
     *
     * @param Cola $cola
     * @return ArrayCollection
     */
    public function removeCola(Cola $cola)
    {
        $this->colas->removeElement($cola);
        return $this->getColas();
    }

    /**
     * Obtiene los agentes de la ventanilla
     *
     * @return ArrayCollection
     */
    public function getAgentes()
    {
        return $this->agentes;
    }

    /**
     * Obtiene el  identificador
     *
     * @return string
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Asigna el identificador de ventanilla
     *
     * @param string $identificador
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;
    }

    /**
     * Remueve una ventanilla de la colección de ventanillas de un Agente
     */
    public function removeVentanillaAgente()
    {
        $this->getAgentes()->forAll(function ($key, $agente) {
            $agente->removeVentanilla($this);
        });
    }

    /**
     * Genera las fechas de creación y modificación de un trámite
     *
     * @ORM\PrePersist
     */
    public function setFechas()
    {
        $this->fechaCreado = new \DateTime();
        $this->fechaModificado = new \DateTime();
    }

    /**
     * Actualiza la fecha de modificación de un trámite
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
