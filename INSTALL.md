## Pasos de instalación del servidor

(*Todos los comandos se ejecutan desde la raíz del proyecto*)

- Crear el archivo `app/config/parameters.yml` usando como referencia el archivo `app/config/parameters.yml.dist`
- Editar el archivo `app/config/parameters.yml` y establecer los parámetros de configuración (conexión a la base de datos, URLs de las demás APIs, clave secreta para la generación de JWT, etc).
- En la raíz del proyecto ejecutar `composer install --no-dev --no-scripts` para instalar las dependencias. Las instrucciones para instalar composer las puede conseguir 
[acá](https://getcomposer.org/download/).
- Ejecutar los siguientes comandos:

```bash
# permisos de escritura para el cache y logs
chmod -R 777 app/{cache,logs}
# limpiar el cache
php app/console cache:clear --env=prod
# generar el cache
php app/console cache:warmup --env=prod
# permisos de escritura para el cache y logs
sudo chmod -R 777 app/{cache,logs}
```

- Crear la estructura de la base de datos ejecutando: `php app/console doctrine:schema:create` (La primera vez que se instala la aplicación)
    - Actualizar la estructura de la base de datos ejecutando: `php app/console doctrine:schema:update --force` (Cuando ya exista la estructura de base de datos)
- Ejecutar las migraciones necesarias ejecutando: `php app/console doctrine:migrations:migrate`
- Eliminar el código de pruebas con el siguiente comando: `rm -rf src/*/Tests/*`

### Importante:
 
El parámetro para la creación y firmado de JWT (`jwt_key_pass_phrase`) y el parámetro (`secret`) **debe ser igual en las tres APIs** (turnos, colas y usuarios); de lo contrario, estás no funcionaran.
