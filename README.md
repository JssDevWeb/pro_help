# ShelterConnect

## Descripción
ShelterConnect es una plataforma web desarrollada con Laravel y React (Inertia.js) que permite conectar a personas vulnerables con servicios sociales y organizaciones de ayuda. La plataforma ofrece visualización geoespacial de recursos, gestión de servicios y organizaciones, y herramientas para facilitar el acceso a ayuda.

## Características principales
- **Vista Geoespacial**: Visualización interactiva de organizaciones y servicios en un mapa
- **Filtrado de servicios**: Búsqueda por tipo, proximidad y más
- **Mapas de calor**: Visualización de densidad de servicios por zonas
- **Gestión de organizaciones**: CRUD completo para organizaciones
- **Gestión de servicios**: CRUD completo para servicios ofrecidos
- **Estadísticas**: Datos visuales sobre la distribución de servicios

## Tecnologías
- **Backend**: Laravel 10
- **Frontend**: React con TypeScript, Inertia.js
- **Mapas**: Leaflet
- **Estilos**: Tailwind CSS
- **Base de datos**: MySQL

## Instalación

### Requisitos previos
- PHP 8.1 o superior
- Composer
- Node.js y NPM
- MySQL
- Servidor web (recomendado: Apache)

### Pasos de instalación
1. Clonar el repositorio:
   ```bash
   git clone [url-repositorio]
   cd ShelterConnect
   ```

2. Instalar dependencias PHP:
   ```bash
   composer install
   ```

3. Instalar dependencias JavaScript:
   ```bash
   npm install
   ```

4. Copiar y configurar variables de entorno:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configurar conexión a base de datos en el archivo `.env`

6. Ejecutar migraciones y seeders:
   ```bash
   php artisan migrate --seed
   ```

7. Compilar assets:
   ```bash
   npm run dev
   ```

8. Iniciar el servidor:
   ```bash
   php artisan serve
   ```

## Uso
Visita `http://localhost:8000` para acceder a la aplicación.

## Licencia
[Tipo de licencia]
