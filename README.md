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

````shell
    # Instalación de dependencia sail en el proyecto
    composer require laravel/sail --dev
    
    # Comando de configuración de la dependencia instalada
    php artisan sail:install --with=pgsql
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

