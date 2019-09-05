<?php
namespace ApiV1Bundle\Entity\Interfaces;

interface UsuarioSyncInterface
{
    /**
     * Edita un usuario
     *
     * @param integer $id Identificador único de usuario
     * @param array $params arreglo con los datos para editar
     */
    public function edit($id, $params);

    /**
     * Elimina un usuario
     *
     * @param integer $id Identificador único de usuario
     */
    public function delete($id);
}
