# Solución de problemas de integración React con TypeScript

## Descripción del Problema

ShelterConnect utiliza React con TypeScript para el frontend, pero esta integración presentaba varios errores comunes:

1. **Módulos no encontrados**: TypeScript no podía encontrar los módulos de React y React DOM.
2. **Tipos implícitos Any**: Errores de compilación por no especificar tipos.
3. **Importaciones CSS para Leaflet**: Problemas al importar los estilos CSS para React-Leaflet.
4. **URL base incorrecta**: La detección de la URL base fallaba en diferentes entornos.

## Solución Implementada

### 1. Script de corrección de tipos React

Se creó el script `scripts/fix-react-types.js` que modifica el `package.json` de React para incluir correctamente los tipos:

```javascript
const fs = require('fs');
const path = require('path');

// Ruta al archivo package.json de react
const reactPackagePath = path.join(__dirname, '../node_modules/react/package.json');

try {
  if (fs.existsSync(reactPackagePath)) {
    const packageJson = require(reactPackagePath);
    
    // Verificar si existe la propiedad types
    if (!packageJson.types) {
      console.log('Agregando campo "types" a react/package.json');
      packageJson.types = './index.d.ts';
      
      // Guardar el archivo modificado
      fs.writeFileSync(
        reactPackagePath,
        JSON.stringify(packageJson, null, 2),
        'utf8'
      );
      console.log('✓ React package.json modificado correctamente');
    }
  }
  
  // Agregar un archivo index.d.ts si no existe
  const reactTypesPath = path.join(__dirname, '../node_modules/react/index.d.ts');
  if (!fs.existsSync(reactTypesPath)) {
    console.log('Creando archivo index.d.ts para React');
    
    const typesContent = `// Re-export from @types/react
import * as React from './node_modules/@types/react';
export = React;
export as namespace React;
`;
    
    fs.writeFileSync(reactTypesPath, typesContent, 'utf8');
    console.log('✓ React index.d.ts creado correctamente');
  }
  
  console.log('✓ Corrección de tipos de React completada');
} catch (error) {
  console.error('Error al corregir tipos de React:', error);
}
```

### 2. Archivos de declaración de tipos personalizados

Se crearon dos archivos principales:

#### resources/js/types/react.d.ts
```typescript
// Hacer referencia a los tipos de React
/// <reference types="react" />
/// <reference types="react-dom" />

// Extender window
interface Window {
  // Properties
}

// Módulos globales
declare module '*.svg' {
  import * as React from 'react';
  export const ReactComponent: React.FunctionComponent<React.SVGProps<SVGSVGElement>>;
  const src: string;
  export default src;
}

declare module '*.jpg' {
  const content: string;
  export default content;
}

declare module '*.png' {
  const content: string;
  export default content;
}

declare module '*.json' {
  const content: any;
  export default content;
}

// Para funciones específicas con problemas de importación
declare module 'react' {
  export = React;
}

declare module 'react-dom' {
  export = ReactDOM;
}

declare module '@inertiajs/react' {
  export const Link: any;
  export const Head: any;
  // Añadir otras exportaciones de inertia según sea necesario
}
```

#### resources/js/types/environment.d.ts
```typescript
/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_APP_NAME: string;
  // más variables de entorno...
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
```

### 3. Configuración de TypeScript

Se actualizó `tsconfig.json` para incluir:
```json
{
  "compilerOptions": {
    "moduleResolution": "node",
    "typeRoots": ["./node_modules/@types", "./resources/js/types"],
    "types": ["node", "react", "react-dom"]
  }
}
```

### 4. Solución para importaciones CSS

Se modificaron las importaciones en componentes React-Leaflet para garantizar que los estilos se carguen correctamente:

```typescript
// Orden correcto de importaciones
import 'leaflet/dist/leaflet.css';
import 'react-leaflet-markercluster/dist/styles.min.css';
// Código del componente...
```

### 5. Detección de URL base para API

Se implementó una solución robusta para detectar la URL base en diferentes entornos:

```typescript
// Obtener la URL base desde .env o usar la URL inferida
const baseAppUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || 
                 window.location.origin + '/laravel/pro_help-master';

const api = axios.create({
  baseURL: `${baseAppUrl}/api`,
  // ...
});
```

## Cómo aplicar la solución

1. **Ejecutar script de corrección**: 
   ```bash
   node scripts/fix-react-types.js
   ```

2. **Verificar archivos de tipos**:
   Asegurarse de que existen los archivos en `resources/js/types/`

3. **Ejecutar compilación**:
   ```bash
   npm run dev
   ```
   
4. **Verificar ausencia de errores** en la consola del navegador

## Recomendaciones

1. Ejecutar el script de corrección de tipos después de cada `npm install` que actualice React
2. Mantener los archivos de declaración de tipos actualizados cuando se importen nuevas dependencias
3. Usar anotaciones de tipos explícitas en componentes React y funciones auxiliares
4. Verificar siempre la URL base cuando se despliegue en diferentes entornos

## Referencias

- [TypeScript and React Documentation](https://www.typescriptlang.org/docs/handbook/react.html)
- [Declaration Files in TypeScript](https://www.typescriptlang.org/docs/handbook/declaration-files/introduction.html)
- [React-Leaflet Documentation](https://react-leaflet.js.org/docs/start-installation/)
