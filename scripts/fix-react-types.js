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
