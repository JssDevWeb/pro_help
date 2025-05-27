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
