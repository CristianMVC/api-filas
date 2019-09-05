<?php
namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiV1Bundle\Entity\User;

/**
 * Class Responsable
 * @package ApiV1Bundle\Entity
 *
 * Responsable
 *
 * @ORM\Table(name="user_responsable")
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\ResponsableRepository")
 */
class Responsable extends Usuario
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
     * @ORM\ManyToOne(targetEntity="PuntoAtencion", inversedBy="responsables")
     * @ORM\JoinColumn(name="punto_atencion_id", referencedColumnName="id")
     */
    protected $puntoAtencion;


    /**
     * Responsable constructor.
     * @param $nombre
     * @param $apellido
     * @param $puntoAtencion
     * @param User $user
     */
    public function __construct($nombre, $apellido, $puntoAtencion, User $user)
    {
        parent::__construct($nombre, $apellido, $user);
        $this->puntoAtencion = $puntoAtencion;
    }

    /**
     * Obtiene el id del responsable
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
     * Obtiene el id del punto de atención
     *
     * @return int
     */
    public function getPuntoAtencionId()
    {
        return $this->getPuntoAtencion() ? $this->getPuntoAtencion()->getId() : null;
    }

    /**
     * Asigna el punto de atención
     *
     * @param type $puntoAtencionID
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
     * Obtiene el id del punto de atención SNT
     * @return int|null
     */
    public function getPuntoAtencionIdSnt()
    {
        return $this->getPuntoAtencion() ? $this->getPuntoAtencion()->getPuntoAtencionIdSnt() : null;
    }
}
