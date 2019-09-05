<?php
namespace ApiV1Bundle\Entity\Interfaces;

interface UsuarioFactoryInterface
{
    /**
     * Crear un usuario
     *
     * @param array $params arreglo con los datos para crear el usuario
     */
    public function create($params);
}
