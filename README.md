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

---

## 📄 Modelo de Usuario

### **Estructura y diseño**

El modelo de usuario (`users`) centraliza la gestión de todos los tipos de usuarios de la aplicación: administradores, técnicos y clientes. Se ha optado por un **modelo único** con un campo `role` para diferenciar los tipos, y campos opcionales (`nullable`) para los atributos específicos de cada rol. Esta decisión simplifica la gestión, la autenticación y el mantenimiento del sistema.

---

### **Campos de la tabla `users`**

| Campo             | Tipo      | Descripción                                                       |
|-------------------|-----------|-------------------------------------------------------------------|
| id                | bigint    | Identificador único (autoincremental)                             |
| name              | string    | Nombre completo del usuario                                       |
| email             | string    | Correo electrónico (único)                                        |
| email_verified_at | timestamp | Fecha de verificación del correo (opcional, para futuras mejoras) |
| password          | string    | Contraseña cifrada                                                |
| role              | enum      | Rol del usuario: `admin`, `technician` o `client`                 |
| password_changed  | boolean   | Indica si el usuario ha cambiado la contraseña inicial            |
| dni               | string    | Documento de identidad (único, opcional para técnicos y clientes) |
| address           | string    | Dirección (opcional para técnicos y clientes)                     |
| phone             | string    | Teléfono (opcional para técnicos y clientes)                      |
| profile_photo     | string    | Ruta de la foto de perfil (opcional, solo para técnicos)          |
| rating            | float     | Valoración media (opcional, solo para técnicos)                   |
| repairs_count     | integer   | Número de reparaciones realizadas (opcional, solo para técnicos)  |
| remember_token    | string    | Token de sesión (gestión interna de Laravel)                      |
| created_at        | timestamp | Fecha de creación                                                 |
| updated_at        | timestamp | Fecha de última actualización                                     |

---

### **Decisiones de diseño**

- **Modelo único:** Todos los usuarios comparten la misma tabla. Los campos específicos de técnicos o clientes se dejan vacíos (`nullable`) para los demás roles.
- **Campo `role`:** Permite distinguir fácilmente el tipo de usuario y controlar el acceso a funcionalidades específicas.
- **Campos opcionales:** Los campos como `dni`, `address`, `phone`, `profile_photo`, `rating` y `repairs_count` solo se usan según el rol del usuario.
- **Contraseña inicial:** El campo `password_changed` permite forzar el cambio de contraseña en el primer inicio de sesión, aumentando la seguridad.
- **Foto de perfil:** Solo los técnicos pueden tener foto de perfil, almacenada como ruta/URL.
- **Valoración y contador de reparaciones:** Permiten llevar un control de calidad y actividad sobre los técnicos.

---

### **Ejemplo de estructura de la tabla**

| id | name    | email               | role       | dni       | address         | phone     | profile_photo         | rating | repairs_count | password_changed |
|----|---------|---------------------|------------|-----------|-----------------|-----------|-----------------------|--------|---------------|------------------|
| 1  | Admin   | admin@empresa.com   | admin      |           |                 |           |                       |        |               | true             |
| 2  | Técnico | tecnico@empresa.com | technician | 12345678A | Calle Falsa 123 | 600000001 | profile-photos/t1.jpg | 4.5    | 12            | false            |
| 3  | Cliente | cliente@empresa.com | client     | 87654321B | Avda. Real 456  | 600000002 |                       |        |               | true             |

---

### **Ventajas de este enfoque**

- **Simplicidad:** Un único modelo y tabla para todos los usuarios.
- **Facilidad de mantenimiento:** Menos relaciones y lógica condicional.
- **Escalabilidad:** Fácil de añadir nuevos campos o roles en el futuro.
- **Integración sencilla:** Compatible con paquetes de roles y permisos como Spatie.

---

## 🛠️ Modelo de Reparaciones

### **Estructura y diseño**

El modelo de reparaciones (`repairs`) gestiona todas las incidencias y servicios realizados en la aplicación. Cada reparación está asociada a un cliente y a un técnico, y puede estar vinculada a una tienda (store) si se implementa. El modelo permite llevar un control detallado del proceso, los costes, el estado y la valoración del servicio.

---

### **Campos de la tabla `repairs`**

| Campo         | Tipo      | Descripción                                                                 |
|---------------|-----------|-----------------------------------------------------------------------------|
| id            | bigint    | Identificador único (autoincremental)                                       |
| ticket_number | string    | Código único de ticket generado automáticamente (ej: REP-1234CAR)           |
| status        | enum      | Estado de la reparación: `pending`, `in_progress`, `completed`              |
| client_id     | foreignId | Usuario cliente asociado (clave foránea a `users`)                          |
| technician_id | foreignId | Usuario técnico asignado (clave foránea a `users`)                          |
| store_id      | foreignId | Tienda asociada (clave foránea a `stores`, opcional según rama)             |
| hours         | integer   | Horas de mano de obra estimadas/realizadas                                  |
| labor_cost    | decimal   | Coste total de mano de obra (horas * 30 €)                                  |
| parts_cost    | decimal   | Coste total de piezas asociadas                                             |
| total_cost    | decimal   | Coste final editable (mano de obra + piezas, editable por técnico/admin)    |
| is_warranty   | boolean   | Indica si la reparación está en garantía                                    |
| rating        | integer   | Valoración del cliente (1-5 estrellas, solo cuando la reparación finaliza)  |
| description   | text      | Descripción de la reparación                                                |
| finished_at   | timestamp | Fecha de finalización (opcional)                                            |
| created_at    | timestamp | Fecha de creación                                                           |
| updated_at    | timestamp | Fecha de última actualización                                               |

---

### **Decisiones de diseño**

- **Ticket único:** El campo `ticket_number` se genera automáticamente con un formato identificativo y único.
- **Estado controlado:** El campo `status` permite seguir el ciclo de vida de la reparación.
- **Relaciones claras:** Cada reparación está asociada a un cliente y un técnico (ambos usuarios), y opcionalmente a una tienda.
- **Costes desglosados:** Se separan los costes de mano de obra, piezas y el coste total editable.
- **Garantía:** El campo `is_warranty` permite distinguir reparaciones cubiertas por garantía.
- **Valoración:** El cliente puede valorar la reparación una vez finalizada.
- **Escalabilidad:** Preparado para añadir relación con tienda (`store_id`) y piezas (`parts`) en futuras ramas.

---

### **Ejemplo de estructura de la tabla**

| id | ticket_number | status      | client_id | technician_id | store_id | hours | labor_cost | parts_cost | total_cost | is_warranty | rating | description        | finished_at         |
|----|---------------|-------------|-----------|---------------|----------|-------|------------|------------|------------|-------------|--------|--------------------|---------------------|
| 1  | REP-1234JUA   | completed   | 1         | 2             | 1        | 2     | 60.00      | 25.00      | 85.00      | false       | 5      | Cambio de pantalla | 2025-06-01 12:00:00 |
| 2  | REP-5678MAR   | in_progress | 3         | 2             | 1        | 1     | 30.00      | 0.00       | 30.00      | false       |        | Revisión general   |                     |

---

### **Ventajas de este enfoque**

- **Trazabilidad:** Cada reparación tiene un identificador único y está asociada a los usuarios implicados.
- **Control de costes:** Permite un desglose y control preciso de los costes de mano de obra y piezas.
- **Gestión de garantías y valoraciones:** Facilita el seguimiento de reparaciones cubiertas por garantía y la calidad del servicio.
- **Preparado para crecer:** Fácil de ampliar con piezas, tiendas y otros módulos en el futuro.

---
