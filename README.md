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

# 📚 Modelos y Estructura de Datos

## 📄 Modelo de Usuario (`users`)

### **Estructura y diseño**
El modelo de usuario centraliza la gestión de todos los tipos de usuarios de la aplicación: **administradores, técnicos y clientes**. Se utiliza un único modelo con un campo `role` para diferenciar los tipos de usuario, y campos opcionales (`nullable`) para los atributos específicos de cada rol.

### **Campos de la tabla `users`**

| Campo             | Tipo      | Descripción                                                     |
|-------------------|-----------|-----------------------------------------------------------------|
| id                | bigint    | Identificador único (autoincremental)                           |
| name              | string    | Nombre completo del usuario                                     |
| email             | string    | Correo electrónico (único)                                      |
| email_verified_at | timestamp | Fecha de verificación del correo (opcional)                     |
| password          | string    | Contraseña cifrada                                              |
| role              | enum      | Rol del usuario: `admin`, `technician` o `client`               |
| password_changed  | boolean   | Indica si el usuario ha cambiado la contraseña inicial          |
| dni               | string    | Documento de identidad (opcional, único para técnicos/clientes) |
| address           | string    | Dirección (opcional)                                            |
| phone             | string    | Teléfono (opcional)                                             |
| profile_photo     | string    | Ruta de la foto de perfil (opcional, técnicos)                  |
| rating            | float     | Valoración media (opcional, técnicos)                           |
| repairs_count     | integer   | Número de reparaciones realizadas (opcional, técnicos)          |
| store_id          | foreignId | Tienda asociada (opcional, técnicos)                            |
| remember_token    | string    | Token de sesión (Laravel)                                       |
| created_at        | timestamp | Fecha de creación                                               |
| updated_at        | timestamp | Fecha de última actualización                                   |

---

## 🏬 Modelo de Tienda (`stores`)

### **Estructura y diseño**
El modelo de tienda centraliza la información de cada sucursal o punto de servicio. Cada tienda puede tener varios técnicos asociados y múltiples reparaciones.

### **Campos de la tabla `stores`**

| Campo      | Tipo      | Descripción                         |
|------------|-----------|-------------------------------------|
| id         | bigint    | Identificador único                 |
| name       | string    | Nombre de la tienda                 |
| address    | string    | Dirección física de la tienda       |
| created_at | timestamp | Fecha de creación                   |
| updated_at | timestamp | Fecha de última actualización       |

#### **Relaciones y métricas**
- **Técnicos asociados:** Relación 1:N (`store` tiene muchos `users` con `role = technician`)
- **Reparaciones:** Relación 1:N (`store` tiene muchas `repairs`)
- **Ganancias y pérdidas:** Calculadas dinámicamente sumando los costes de las reparaciones asociadas (ver métodos en el modelo).
- **Rating medio:** Promedio de las valoraciones de las reparaciones asociadas.

---

## 🛠️ Modelo de Reparaciones (`repairs`)

### **Estructura y diseño**
El modelo de reparaciones gestiona todas las incidencias y servicios realizados. Cada reparación está asociada a un cliente, un técnico, una tienda y puede tener múltiples piezas asociadas.

### **Campos de la tabla `repairs`**

| Campo         | Tipo      | Descripción                                                    |
|---------------|-----------|----------------------------------------------------------------|
| id            | bigint    | Identificador único                                            |
| ticket_number | string    | Código único de ticket generado automáticamente                |
| status        | enum      | Estado: `pending`, `in_progress`, `completed`                  |
| store_id      | foreignId | Tienda asociada                                                |
| client_id     | foreignId | Usuario cliente asociado                                       |
| technician_id | foreignId | Usuario técnico asignado (nullable)                            |
| hours         | integer   | Horas de mano de obra                                          |
| labor_cost    | decimal   | Coste total de mano de obra (horas x 30 €)                     |
| parts_cost    | decimal   | Coste total de piezas asociadas                                |
| total_cost    | decimal   | Coste final editable (mano de obra + piezas)                   |
| is_warranty   | boolean   | Indica si la reparación está en garantía                       |
| rating        | float     | Valoración del cliente (1-5 estrellas, nullable)               |
| description   | text      | Descripción de la reparación                                   |
| finished_at   | timestamp | Fecha de finalización (opcional)                               |
| created_at    | timestamp | Fecha de creación                                              |
| updated_at    | timestamp | Fecha de última actualización                                  |

#### **Relaciones**
- Pertenece a un cliente (`users`)
- Pertenece a un técnico (`users`)
- Pertenece a una tienda (`stores`)
- Tiene muchas piezas asociadas (relación muchos a muchos con `parts`)

---

## 🧩 Modelo de Piezas (`parts`)

### **Estructura y diseño**
El modelo de piezas gestiona el inventario de componentes disponibles para las reparaciones.

### **Campos de la tabla `parts`**

| Campo         | Tipo      | Descripción                                       |
|---------------|-----------|---------------------------------------------------|
| id            | bigint    | Identificador único                               |
| name          | string    | Nombre de la pieza                                |
| serial_number | string    | Número de serie (opcional, único)                 |
| stock         | integer   | Cantidad disponible en inventario                 |
| cost          | decimal   | Coste de compra para la tienda                    |
| price         | decimal   | Precio de venta al cliente                        |
| created_at    | timestamp | Fecha de creación                                 |
| updated_at    | timestamp | Fecha de última actualización                     |

#### **Relaciones**
- Puede estar asociada a muchas reparaciones (relación muchos a muchos con `repairs`)

---

## 🔗 Tabla pivote `part_repair`

| Campo      | Tipo      | Descripción                                       |
|------------|-----------|---------------------------------------------------|
| id         | bigint    | Identificador único                               |
| part_id    | foreignId | ID de la pieza                                    |
| repair_id  | foreignId | ID de la reparación                               |
| quantity   | integer   | Cantidad de piezas usadas en esa reparación       |
| created_at | timestamp | Fecha de creación                                 |
| updated_at | timestamp | Fecha de última actualización                     |

---

## **Decisiones de diseño y ventajas**

- **Modelo único para usuarios:** Simplifica la gestión y autenticación.
- **Relaciones claras y normalizadas:** Uso de claves foráneas y tabla pivote para integridad y consultas eficientes.
- **Métricas y estadísticas:** Ganancias, pérdidas y rating medio calculados dinámicamente.
- **Escalabilidad:** Estructura preparada para añadir nuevos roles, módulos o funcionalidades fácilmente.
- **Facilidad de mantenimiento:** Todas las relaciones y dependencias están bien definidas y documentadas.

---

## **Ejemplo de relaciones Eloquent**

```php
// Obtener todas las reparaciones de una tienda
$store->repairs;

// Obtener técnicos de una tienda
$store->technicians;

// Obtener piezas usadas en una reparación
$repair->parts;

// Obtener reparaciones donde se usó una pieza
$part->repairs;

// Calcular ganancias de una tienda
$store->total_earnings;

// Calcular pérdidas por garantías de una tienda
$store->total_losses;
```

---
