<?php
namespace ApiV1Bundle\Helper;

/**
 * Class ServicesHelper
 * @package ApiV1Bundle\Helper
 */
class ServicesHelper
{
    /**
     * Convierte a arreglo
     *
     * @param $data
     * @return mixed|null
     */
    public static function toArray($data)
    {
        if (is_array($data)) {
            return $data;
        }
        if (json_decode($data)) {
            return json_decode($data, true);
        }
        return null;
    }

    /**
     * Pasa un código único a sus primeros 8 caracters
     *
     * @param string $code
     * @return string
     */
    public static function obtenerCodigoSimple($code)
    {
        $parts = explode('-', $code);
        return $parts[0];
    }

    /**
     * Generar contraseña al azar
     *
     * @param integer $len
     * @return string
     */
    public static function randomPassword($len = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($chars) - 1;
        for ($i = 0; $i < $len; $i ++) {
            $n = rand(0, $alphaLength);
            $pass[] = $chars[$n];
        }
        return implode($pass);
    }
}