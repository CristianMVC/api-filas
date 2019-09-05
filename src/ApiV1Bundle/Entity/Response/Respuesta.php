<?php
/**
 * Created by PhpStorm.
 * User: jtibi
 * Date: 19/10/2017
 * Time: 2:51 PM
 */

namespace ApiV1Bundle\Entity\Response;


/**
 * Class Respuesta
 * @package ApiV1Bundle\Entity
 */

class Respuesta
{
    private $metadata;
    private $result;

    /**
     * Respuesta constructor.
     *
     * @param array $metadata
     * @param array $result
     */

    public function __construct($metadata, $result)
    {
        $this->metadata = $metadata;
        $this->result = $result;
    }

    /**
     * Obtiene metadata
     *
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Setea metadata
     *
     * @param mixed $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Obtiene un resultado
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Setea un resultado
     *
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
