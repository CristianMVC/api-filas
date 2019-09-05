<?php

namespace ApiV1Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Turno
 * @package ApiV1Bundle\Entity
 *
 * Turno
 *
 * @ORM\Table(name="turno")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\TurnoRepository")
 * @Gedmo\SoftDeleteable(fieldName="fechaBorrado")
 * @ORM\HasLifecycleCallbacks()
 */
class Turno
{
    const ESTADO_RECEPCIONADO = 3;
    const ESTADO_EN_TRANCURSO = 4;
    const ESTADO_TERMINADO = 5;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var PuntoAtencion
     *
     * @ORM\ManyToOne(targetEntity="PuntoAtencion")
     * @ORM\JoinColumn(name="punto_atencion_id", referencedColumnName="id")
     */
    private $puntoAtencion;

    /**
     * ID Grupo Tramite del SNT
     *
     * @var int
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @ORM\Column(name="grupo_tramite_snt_id")
     */
    private $grupoTramiteIdSNT;

    /**
     * Campo de relación con los datos del turno
     * A un turno le corresponde un solo grupo de datos
     *
     * @var DatosTurno
     * @ORM\ManyToOne(targetEntity="DatosTurno", inversedBy="turno")
     * @ORM\JoinColumn(name="datos_turno_id", referencedColumnName="id")
     */
    private $datosTurno;

    /**
     * @var Agente
     *
     * @ORM\ManyToOne(targetEntity="Agente")
     * @ORM\JoinColumn(name="user_agente_id", referencedColumnName="id", nullable = true)
     */
    private $agente;

    /**
     * @var Ventanilla
     *
     * @ORM\ManyToOne(targetEntity="Ventanilla")
     * @ORM\JoinColumn(name="ventanilla_id", referencedColumnName="id")
     */
    private $ventanilla;

    /**
     * Fecha del turno
     *
     * @var \DateTime
     *
     * @Assert\DateTime()
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /**
     * Hora del turno
     *
     * @var \DateTime
     *
     * @Assert\DateTime()
     * @ORM\Column(name="hora", type="time")
     */
    private $hora;

    /**
     * Estados que puede tener el turno:
     * [3 => recepcionado, 4 => en transcurso, 5 => terminado]
     *
     * @var int
     * @Assert\Type(
     *     type="integer",
     *     message="Este campo no puede estar vacío y debe ser numérico."
     * )
     * @Assert\Range(min = 3, max = 5)
     * @ORM\Column(name="estado", type="smallint")
     */
    private $estado;

   /**
    * @var string
    *
    * @ORM\Column(name="motivo", type="string", length=255, nullable=true)
    */
    private $motivoTerminado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hora_estado", type="time")
     */
    private $horaEstado;

    /**
     * @var string
     *
     * @ORM\Column(name="tramite", type="string", length=255, nullable=false)
     */
    private $tramite;

    /**
     * Clave hash de cada turno
     *
     * @var string
     * @Assert\NotNull(
     *     message="Este campo no puede estar vacío."
     * )
     * @ORM\Column(name="codigo", type="string", length=64)
     */
    private $codigo;

    /**
     * Estados que puede tener el turno:
     * [0 => sin prioridad, 1 => con prioridad]
     *
     * @var int
     * @Assert\Type(
     *     type="integer",
     *     message="Este campo no puede estar vacío y debe ser numérico."
     * )
     * @Assert\Range(min = 1, max = 2)
     * @ORM\Column(name="prioridad", type="smallint")
     */
    private $prioridad;

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

    /** @var Cola */
    private $cola;

    /**
     * Turno constructor
     *
     * @param PuntoAtencion $puntoAtencion
     * @param DatosTurno $datosTurno
     * @param int $grupoTramite
     * @param $fecha
     * @param $hora
     * @param string $estado
     * @param string $tramite
     * @param string $codigo
     * @param $prioridad
     */
    public function __construct(
        PuntoAtencion $puntoAtencion,
        DatosTurno $datosTurno,
        $grupoTramite,
        $fecha,
        $hora,
        $estado,
        $tramite,
        $codigo,
        $prioridad
    )
    {
        $this->puntoAtencion = $puntoAtencion;
        $this->datosTurno = $datosTurno;
        $this->grupoTramiteIdSNT = $grupoTramite;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->estado = $estado;
        $this->horaEstado = new \DateTime();
        $this->tramite = $tramite;
        $this->codigo = $codigo;
        $this->prioridad = $prioridad;
    }

    /**
     * Obtiene el identificador único del turno
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtiene el punto de atención
     *
     * @return PuntoAtencion
     */
    public function getPuntoAtencion()
    {
        return $this->puntoAtencion;
    }

    /**
     * Obtiene el objeto DatosTurno
     *
     * @return DatosTurno
     */
    public function getDatosTurno()
    {
        return $this->datosTurno;
    }

    /**
     * Obtiene el objeto agente
     *
     * @return Agente
     */
    public function getAgente()
    {
        return $this->agente;
    }

    /**
     * Setea el agente del turno
     *
     * @param $agente
     */
    public function setAgente($agente)
    {
        $this->agente = $agente;
    }

    /**
     * Setea la cola
     *
     * @param $cola
     */
    public function setCola($cola)
    {
        $this->cola = $cola;
    }

    /**
     * Obtiene el objeto cola
     *
     * @return cola
     */
    public function getCola()
    {
        return $this->cola;
    }

    /**
     * Obtiene el objeto ventanilla
     *
     * @return Ventanilla
     */
    public function getVentanilla()
    {
        return $this->ventanilla;
    }

    /**
     * Setea la ventanilla
     *
     * @param $ventanilla
     */
    public function setVentanilla($ventanilla)
    {
        $this->ventanilla = $ventanilla;
    }

    /**
     * Obtiene la fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha->format('Y-m-d');
    }

    /**
     * Obtiene la hora
     *
     * @return \DateTime
     */
    public function getHora()
    {
        return $this->hora->format('H:i:s');
    }

    /**
     * Obtiene el estado
     *
     * @return int
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Obtiene la Hora del estado
     *
     * @return \DateTime
     */
    public function getHoraEstado()
    {
        return $this->horaEstado;
    }

    /**
     * Obtiene el trámite
     *
     * @return string
     */
    public function getTramite()
    {
        return $this->tramite;
    }

    /**
     * Obtiene el código
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Obtiene el grupo trámite SNT
     * @return mixed
     */
    public function getGrupoTramiteIdSNT()
    {
        return $this->grupoTramiteIdSNT;
    }

    /**
     * Obtiene la prioridad
     *
     * @return int
     */
    public function getPrioridad()
    {
        return $this->prioridad;
    }

    /**
     * Obtiene el motivo
     *
     * @return mixed
     */
    public function getMotivoTerminado()
    {
        return $this->motivoTerminado;
    }

    /**
     * asigna el motivo
     *
     * @param mixed $motivoTerminado
     */
    public function setMotivoTerminado($motivoTerminado)
    {
        $this->motivoTerminado = $motivoTerminado;
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
     * Obtiene la fecha de borrado
     *
     * @return \DateTime
     */
    public function getFechaBorrado()
    {
        return $this->fechaBorrado;
    }
}

