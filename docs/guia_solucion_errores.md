# Guía para Solución de Errores en ShelterConnect

Esta guía documenta errores comunes en el proyecto ShelterConnect y sus soluciones.

## Errores de Carga de Recursos Estáticos (404)

### Problema
Los archivos JS y CSS generados por Vite no se cargan correctamente, generando errores 404.

```
Failed to load resource: the server responded with a status of 404 (Not Found) (/build/assets/app-B8Hs5iJ8.js)
```

### Solución

1. **Actualizar la URL Base en .env**
   ```
   APP_URL=http://localhost/laravel/pro_help-master
   ```

2. **Modificar vite.config.ts**
   ```typescript
   export default defineConfig({
       base: '/laravel/pro_help-master/public/build/',
       plugins: [
           laravel({
               input: ['resources/css/app.css', 'resources/js/app.tsx'],
               ssr: 'resources/js/ssr.tsx',
               refresh: true,
               buildDirectory: 'build',
               publicDirectory: 'public',
           }),
           // ...
       ],
   });
   ```

3. **Actualizar .htaccess**
   ```
   # Asegurar que los archivos de build se sirven correctamente
   RewriteCond %{REQUEST_URI} ^/build/(.*)$
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^build/(.*)$ /laravel/pro_help-master/public/build/$1 [L]
   ```

4. **Actualizar API Service**
   ```typescript
   // Obtener la URL base desde .env o usar la URL inferida
   const baseAppUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || 
                     window.location.origin + '/laravel/pro_help-master';

   const api = axios.create({
     baseURL: `${baseAppUrl}/api`,
     // ...
   });
   ```

5. **Agregar meta tag de URL base**
   ```html
   <meta name="base-url" content="{{ url('/') }}">
   ```

6. **Usar helpers url() para assets**
   ```html
   <link rel="icon" href="{{ url('/favicon.ico') }}" sizes="any">
   ```

## Errores de TypeScript con tipos implícitos 'any'

### Problema
TypeScript muestra advertencias sobre tipos implícitos `any` que pueden causar errores.

```
El parámetro 'prev' tiene un tipo 'any' implícitamente.ts(7006)
El parámetro 'org' tiene un tipo 'any' implícitamente.ts(7006)
El parámetro 'service' tiene un tipo 'any' implícitamente.ts(7006)
```

### Solución

1. **Especificar tipos explícitamente en `map`**
   ```typescript
   organizations.map((org: Organization) => ({
     // ...
   }))
   ```

2. **Agregar tipos a callbacks de useState**
   ```typescript
   setStatsRefreshCount((prev: number) => prev + 1);
   ```

3. **Configurar `tsconfig.json` con typeRoots**
   ```json
   "typeRoots": ["./node_modules/@types", "./resources/js/types"],
   "types": ["react", "react-dom", "node"]
   ```

4. **Ejecutar script de corrección de tipos**
   ```bash
   node scripts/fix-react-types.js
   ```

Para una solución detallada, consulte `docs/react_typescript_solucion.md`.

## Errores API 404 para rutas como `/api/geospatial-stats`

### Problema
Las rutas de API devuelven 404 aunque estén correctamente definidas.

### Solución

1. **Verificar autenticación**: Asegurarse que las rutas protegidas tengan token válido
2. **Usar rutas absolutas**: Configurar axios para usar rutas completas
3. **Revisar meta tag de URL base**:
   ```html
   <meta name="base-url" content="{{ url('/') }}">
   ```
4. **Verificar implementación del cliente API**:
   ```typescript
   const baseAppUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || 
                     window.location.origin + '/laravel/pro_help-master';

   const api = axios.create({
     baseURL: `${baseAppUrl}/api`,
     headers: {
       'Content-Type': 'application/json',
       'Accept': 'application/json',
       'X-Requested-With': 'XMLHttpRequest'
     }
   });
   ```

## Errores de visualización del mapa React-Leaflet

### Problema
El mapa Leaflet no se muestra correctamente, con marcadores desplazados o elementos que no aparecen.

### Solución

1. **Verificar orden de importación CSS**:
   ```typescript
   // Orden correcto - primero Leaflet base, luego plugins
   import 'leaflet/dist/leaflet.css';
   import 'react-leaflet-markercluster/dist/styles.min.css';
   // Otros estilos después
   ```

2. **Corregir rutas de imágenes para marcadores**:
   ```typescript
   // En el componente de mapa
   const icon = new L.Icon({
     iconUrl: '/images/marker-icon.png',
     iconRetinaUrl: '/images/marker-icon-2x.png',
     shadowUrl: '/images/marker-shadow.png',
     iconSize: [25, 41],
     iconAnchor: [12, 41]
   });
   ```

3. **Copiar archivos de imágenes a public/images/**:
   ```bash
   mkdir -p public/images
   cp node_modules/leaflet/dist/images/* public/images/
   ```

4. **Agregar CSS personalizado para corregir z-index**:
   ```css
   .leaflet-container {
     z-index: 1;
   }
   .leaflet-marker-pane,
   .leaflet-overlay-pane {
     z-index: 500;
   }
   ```

## Instrucciones para Actualizar Build

1. Limpiar caché previa:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
npm cache clean --force
```

2. Reconstruir assets:
```bash
npm run build
```

3. Reiniciar servidor:
```bash
php artisan serve --host=localhost
```