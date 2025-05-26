# Backend Repasoft
#### Trabajo de Final de Grado: Adrián Ucha Sousa

## Descripición del proyecto.
[Pendiente agregar]

---

## Instalación y configuración del entorno con Laravel Sail y PostgresSQL
### Requisitos previos:
- Docker instalado y funcional en nuestro ordenador.
- Composer para la gestión de dependencias.
- PHP 8.2 (Necesario para que funcione, ya que requiere de PHP).
- IDE (Opcional para visualización cómoda de código y entorno de trabajo).

Una vez cumplimos los requisitos

### Clonación el repositorio


````shell
    # Comando de clonación de proyecto
    git clone https://github.com/Uchaa03/Repasoft_Backend.git

    # Vamos al directorio una vez clonado
    cd Repasoft_Backend
````

### Instalación de Sail en el proyecto

Seleccionamos el servicio psql(PostgresSQL), al instalar sail.

````shell
    # Instalación de dependencia sail en el proyecto
    composer require laravel/sail --dev
    
    # Comando de configuración de la dependencia instalada
    php artisan sail:install
````

**Importante: Revisar que `.env` sea como él `.env.example` se debe de configurar con la instalación de sail, de lo
contrario debemos hacerlo manualmente**

Una vez realizado esto, se configurarán los contenedores en docker para que los podamos lanzar con sail.
````shell
    # Comando para aplicar alias a sail
    alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'

    # Comando de inicio
    sail up -d
    
    # Parar contenedores
    sail down
    
    # Borrar contenedores para poder lanzarlos limpios
    sail down -v

````

### Ejecutar migraciones y seeders
Debemos de ejecutar las migraciones y lanzar los seeders para que la aplicación funcione correctamente.
````shell
    # Comando para migraciones
    sail artisan migrate
    
    # Comando para seeders(roles)
    sail artisan db:seed
````
Una vez realizado todo ya tendremos listo nuestro backend para realizar las pruebas necesarias.


## Diseño del modelo de usuario

### Opciones consideradas

1. **Modelo único `User` con campos opcionales y roles**
    - Todos los tipos de usuario (administrador, técnico, cliente) comparten la misma tabla.
    - Los campos específicos de cada tipo se definen como opcionales (`nullable`).
    - Un campo `role` diferencia el tipo de usuario.
    - Sencillez en autenticación y gestión de permisos.

2. **Modelo principal `User` + modelos extendidos (`Technician`, `Client`)**
    - Tabla `users` para datos comunes.
    - Tablas `technicians` y `clients` para los datos específicos, enlazadas por `user_id`.
    - Mayor normalización, pero más complejidad en consultas y relaciones.

3. **Single Table Inheritance (STI)**
    - Un campo `type` o `role` en la tabla `users` y lógica de herencia a nivel de modelo.
    - No es nativo en Laravel y puede complicar el mantenimiento.

### Opción elegida y justificación

**Se ha optado por un único modelo `User` con campos opcionales y un campo `role`.**

- Permite gestionar todos los usuarios desde una sola tabla y modelo.
- Facilita la integración con paquetes de roles y permisos como [spatie/laravel-permission](https://spatie.be/docs/laravel-permission).
- Simplifica la autenticación y el control de acceso.
- Es fácilmente escalable y suficientemente flexible para los requisitos actuales del proyecto.

Los campos específicos de técnicos o clientes (por ejemplo, `dni`, `address`, `phone`, `rating`, `repairs_count`, `profile_photo`, `password_changed`) se definen como opcionales y solo se utilizan según el tipo de usuario.

