<?php
namespace ApiV1Bundle\Entity\Validator;

/**
 * Class ValidateResultado
 * @package ApiV1Bundle\Entity\Validator
 */
class ValidateResultado
{
    /** @var \ApiV1Bundle\Entity\object $entity  */
    private $entity;

    /** @var array $errors */
    private $errors;

    /**
     * ValidateResultado constructor.
     *
     * @param object $entity
     * @param array $errors
     */
    public function __construct($entity, $errors)
    {
        $this->entity = $entity;
        $this->errors['errors'] = $errors;
    }

    /**
     * Obtiene un Entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Setea un Entity
     *
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Obtiene un error de validación de resultado
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Retorna verdadero si el numero de errores fue mayor a cero en otro caso retorna falso
     *
     * @return bool
     */

    public function hasError()
    {
        return (count($this->errors['errors']) > 0);
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

}