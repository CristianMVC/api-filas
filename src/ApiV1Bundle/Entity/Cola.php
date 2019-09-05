<?php

namespace ApiV1Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Cola
 * @package ApiV1Bundle\Entity\
 *
 * @ORM\Table(name="cola")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\ColaRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class Cola
{

    const TIPO_GRUPO_TRAMITE = 1;
    const TIPO_POSTA = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="PuntoAtencion")
     * @ORM\JoinColumn(name="punto_atencion_id", referencedColumnName="id")
     */
    private $puntoAtencion;

    /**
     * @var string
     * @Assert\NotNull(
     *     message="El campo Nombre no puede estar vacío."
     * )
     * @Assert\Type(
     *     type="string",
     *     message="Este campo Nombre solo acepta caracteres alfanumerico."
     * )
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var int
     * @ORM\Column(name="grupo_tramite_snt_id", type="integer", nullable=true)
     */
    private $grupoTramiteSNTId;

    /**
     * @var int
     * @ORM\Column(name="tipo", type="integer")
     */
    private $tipo;

    /**
     * Colección de ventanillas que tiene una cola
     * Cola puede pertenecer a N ventanillas
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Ventanilla", mappedBy="colas")
     */
    private $ventanillas;

    /**
     * Fecha de creación de la cola
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_creado", type="datetimetz")
     */
    private $fechaCreado;

    /**
     * Fecha de modificación de la cola
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_modificado", type="datetimetz")
     */
    private $fechaModificado;

    /**
     * Fecha de borrado de la cola
     *
     * @var \DateTime
     * @ORM\Column(name="fecha_borrado", type="datetimetz", nullable=true)
     */
    private $fechaBorrado;


    public function __construct($nombre, $puntoAtencion, $tipo)
    {
        $this->nombre = $nombre;
        $this->puntoAtencion = $puntoAtencion;
        $this->tipo = $tipo;
        $this->ventanillas = new ArrayCollection();
    }

    /**
     * Obtiene el id de la cola
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get puntoatencionId
     *
     * @return int
     */
    public function getPuntoAtencion()
    {
        return $this->puntoAtencion;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Asigna el nombre
     *
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Obtiene el id del grupo de trámite de SNT
     *
     * @return int
     */
    public function getGrupoTramiteSNTId()
    {
        return $this->grupoTramiteSNTId;
    }

    /**
     * Asignar el id del grupo de trámite de SNT
     *
     * @param int $grupoTramiteSNTId
     */
    public function setGrupoTramiteSNTId($grupoTramiteSNTId)
    {
        $this->grupoTramiteSNTId = $grupoTramiteSNTId;
    }

    /**
     * Obtiene el conjunto de ventanillas
     *
     * @return ArrayCollection
     */
    public function getVentanillas()
    {
        return $this->ventanillas;
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
