# Guía de Desarrollo para ShelterConnect

Esta guía está destinada a desarrolladores que deseen contribuir al proyecto ShelterConnect o entender su estructura para mantenimiento y extensión.

## Configuración del entorno de desarrollo

### Requisitos previos
- PHP 8.1+
- Composer
- Node.js 16+
- PostgreSQL 14+ con extensión PostGIS
- Git
- Editor de código (recomendado: VS Code)
- Servidor local (WAMP, XAMPP, Laragon o similar)

### Instalación del entorno de desarrollo

1. **Clonar el repositorio**:
   ```bash
   git clone [url-repositorio]
   cd ShelterConnect
   ```

2. **Instalar dependencias PHP**:
   ```bash
   composer install
   ```

3. **Instalar dependencias JavaScript**:
   ```bash
   npm install
   ```

4. **Configurar entorno**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**:
   Edita el archivo `.env` con tus credenciales de PostgreSQL:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pro_help
   DB_USERNAME=postgres
   DB_PASSWORD=tu_contraseña
   ```

6. **Ejecutar migraciones y seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Iniciar el servidor de desarrollo**:
   ```bash
   php artisan serve
   ```

8. **Compilar recursos en modo desarrollo**:
   ```bash
   npm run dev
   ```

9. **Corregir tipos de React** (si es necesario):
   ```bash
   node scripts/fix-react-types.js
   ```

## Estructura del proyecto

### Frontend (React + TypeScript + Inertia)

La estructura de archivos del frontend se encuentra principalmente en:

```
resources/
├── js/
│   ├── components/       # Componentes React reutilizables
│   │   ├── maps/         # Componentes específicos para mapas
│   │   │   ├── FilterPanel.tsx    # Panel de filtros para mapas
│   │   │   ├── GeoStats.tsx       # Componente de estadísticas
│   │   │   └── LeafletMap.tsx     # Componente de mapa
│   ├── layouts/          # Componentes de diseño (plantillas)
│   ├── pages/            # Componentes de página
│   │   ├── GeoView.tsx   # Vista geoespacial
│   │   └── ...
│   ├── services/         # Servicios para comunicación con el backend
│   │   ├── organizationService.ts
│   │   ├── serviceService.ts
│   │   └── statsService.ts
│   └── ...
```

### Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/       # Controladores
│   └── ...
├── Models/                # Modelos Eloquent
│   ├── Organization.php   # Modelo de organización
│   ├── Service.php        # Modelo de servicio
│   └── ...
├── Services/              # Servicios de lógica de negocio
└── ...
database/
├── migrations/            # Migraciones de base de datos
├── seeders/               # Seeders para datos iniciales
└── ...
routes/
├── api.php                # Definición de rutas API
├── web.php                # Definición de rutas web
└── ...
```

## Convenciones de código

### Frontend (TypeScript)

- Usa interfaces para tipos de datos (define tipos en `resources/js/types/`)
- Componentes en PascalCase
- Servicios y utilidades en camelCase
- Usa React Hooks para el estado y efectos
- Utiliza componentes funcionales (no clases)
- Usa comentarios `// @ts-ignore` solo cuando sea absolutamente necesario

### Backend (PHP)

- Sigue las convenciones PSR-12
- Modelos en singular, PascalCase
- Controladores en plural, PascalCase
- Usa tipos de retorno y parámetros cuando sea posible
- Documenta métodos públicos

## Flujo de trabajo de desarrollo

1. **Crear una rama para nuevas características**:
   ```bash
   git checkout -b feature/nombre-de-la-caracteristica
   ```

2. **Desarrollo y pruebas locales**:
   - Desarrolla la funcionalidad
   - Escribe pruebas (si aplica)
   - Verifica el formato del código

3. **Enviar cambios**:
   ```bash
   git add .
   git commit -m "Descripción de los cambios"
   git push origin feature/nombre-de-la-caracteristica
   ```

4. **Crear Pull Request**:
   - Describe los cambios realizados
   - Referencia cualquier issue relacionado
   - Solicita revisores

## Directrices para componentes React

### Estructura recomendada para componentes
```tsx
// Importaciones
import { useState, useEffect } from 'react';
import { OtroComponente } from './OtroComponente';

// Interfaces
interface MiComponenteProps {
  propA: string;
  propB?: number;
}

// Componente
export default function MiComponente({ propA, propB = 0 }: MiComponenteProps) {
  // Estado
  const [estado, setEstado] = useState<string>('');
  
  // Efectos
  useEffect(() => {
    // Lógica del efecto
    return () => {
      // Limpieza (si es necesaria)
    };
  }, [propA]);
  
  // Funciones auxiliares
  const handleClick = () => {
    setEstado('Nuevo valor');
  };
  
  // Renderizado
  return (
    <div>
      <h1>{propA}</h1>
      <button onClick={handleClick}>Cambiar estado</button>
    </div>
  );
}
```

## Solución de problemas de TypeScript

### Errores de tipos en React

Si encuentras errores relacionados con los tipos de React como:

```
Cannot find module 'react' or its corresponding type declarations
```

Ejecuta el script de corrección de tipos:

```bash
node scripts/fix-react-types.js
```

Este script:
1. Modifica el archivo `package.json` de React para incluir la propiedad `types`
2. Crea un archivo `index.d.ts` en el módulo de React si no existe

### Declaración de tipos personalizados

Los tipos personalizados deben colocarse en la carpeta `resources/js/types/`. Los archivos principales son:

- `resources/js/types/react.d.ts`: Declaraciones para módulos React y extensiones
- `resources/js/types/environment.d.ts`: Tipos para variables de entorno de Vite

### Configuración de TypeScript

El proyecto utiliza la siguiente configuración en `tsconfig.json`:

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "jsx": "react-jsx",
    "moduleResolution": "node",
    "typeRoots": ["./node_modules/@types", "./resources/js/types"],
    "types": ["node", "react", "react-dom"]
    // otras opciones...
  }
}
```

## Depuración

### Frontend
- Usa la extensión React Developer Tools para Chrome o Firefox
- Usa `console.log()` o `console.debug()` para depuración durante desarrollo
- Verifica la consola del navegador para errores

### Backend
- Usa `dd()` o `dump()` para inspeccionar variables
- Revisa los logs en `storage/logs/laravel.log`
- Configura Xdebug para depuración paso a paso

## Recursos útiles

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de React](https://react.dev/reference/react)
- [Documentación de Inertia.js](https://inertiajs.com/)
- [Documentación de Leaflet](https://leafletjs.com/reference.html)
- [Documentación de Tailwind CSS](https://tailwindcss.com/docs)
