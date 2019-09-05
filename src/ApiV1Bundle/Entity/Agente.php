<?php
namespace ApiV1Bundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Agente
 * @package ApiV1Bundle\Entity\
 *
 * @ORM\Table(name="user_agente")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\AgenteRepository")
 */
class Agente extends Usuario
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PuntoAtencion", inversedBy="agentes")
     * @ORM\JoinColumn(name="punto_atencion_id", referencedColumnName="id")
     */
    private $puntoAtencion;

    /**
     * @var Ventanilla
     * @ORM\OneToOne(targetEntity="Ventanilla")
     * @ORM\JoinColumn(name="ventanilla_id", referencedColumnName="id", nullable = true)
     */
    private $ventanillaActual;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Ventanilla", inversedBy="agentes")
     * @ORM\JoinTable(name="agente_ventanilla")
     **/
    private $ventanillas;

    /**
     * Agente constructor
     *
     * @param string $nombre
     * @param string $apellido
     * @param integer $puntoAtencion
     * @param User $user
     */
    public function __construct($nombre, $apellido, $puntoAtencion, User $user)
    {
        parent::__construct($nombre, $apellido, $user);
        $this->puntoAtencion = $puntoAtencion;
        $this->ventanillas = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Devuelve el objeto punto de atención
     *
     * @return PuntoAtencion
     */
    public function getPuntoAtencion()
    {
        return $this->puntoAtencion;
    }

    /**
     * Devuelve el Id del punto de atención
     *
     * @return int
     */
    public function getPuntoAtencionId()
    {
        return $this->getPuntoAtencion() ? $this->getPuntoAtencion()->getId() : null;
    }

    /**
     * Get ventanillaId
     *
     * @return Ventanilla
     */
    public function getVentanillaActual()
    {
        return $this->ventanillaActual;
    }

    /**
     * Setea la ventanilla
     *
     * @param Ventanilla $ventanillaActual
     */
    public function setVentanillaActual($ventanillaActual)
    {
        $this->ventanillaActual = $ventanillaActual;
    }

    /**
     * Obtiene lista de ventanillas
     *
     * @return ArrayCollection
     */
    public function getVentanillas()
    {
        return $this->ventanillas;
    }

    /**
     * Agrega una ventanilla a un Agente
     *
     * @param Ventanilla $ventanilla
     * @return ArrayCollection
     */
    public function addVentanilla(Ventanilla $ventanilla)
    {
        $this->ventanillas[] = $ventanilla;
        return $this->getVentanillas();
    }

    /**
     * Elimina una ventanilla a un Agente
     *
     * @param Ventanilla $ventanilla
     * @return ArrayCollection
     */
    public function removeVentanilla(Ventanilla $ventanilla)
    {
        $this->ventanillas->removeElement($ventanilla);
        return $this->getVentanillas();
    }

    /**
     * Elimina todas las ventanillas
     */
    public function removeAllVentanilla()
    {
        $this->ventanillas->clear();
    }

    /**
     * Setear el punto de atención por id
     *
     * @param int $puntoAtencionID
     */
    public function setPuntoAtencion($puntoAtencionID)
    {
        $this->puntoAtencion = $puntoAtencionID;
    }

    /**
     * Obtiene el nombre del punto de atención
     *
     * @return string
     */
    public function getNombrePuntoAtencion()
    {
        return $this->getPuntoAtencion() ? $this->getPuntoAtencion()->getNombre() : null;
    }

    /**
     * Obtiene Id de la ventanilla actual
     *
     * @return NULL|number
     */
    public function getVentanillaActualId()
    {
        return $this->getVentanillaActual() ? $this->getVentanillaActual()->getId() : null;
    }

    /**
     * Obtiene Identificador de la ventanilla actual
     *
     * @return NULL|number
     */
    public function getVentanillaActualIdentificador()
    {
        return $this->getVentanillaActual() ? $this->getVentanillaActual()->getIdentificador() : null;
    }

    /**
     * Obtiene id del punto de atención de SNT
     *
     * @return int|null
     */
    public function getPuntoAtencionIdSnt()
    {
        return $this->getPuntoAtencion() ? $this->getPuntoAtencion()->getPuntoAtencionIdSnt() : null;
    }

    /**
     * Obtiene username
     *
     * @return string|null
     */
    public function getUsername()
    {
        $user=parent::getUser();
        return $user ? $user->getUsername() : null;
    }

    /**
     * Obtiene userId
     *
     * @return integer |null
     */
    public function getUserId()
    {
        $user=parent::getUser();
        return $user ? $user->getId() : null;
    }
}
