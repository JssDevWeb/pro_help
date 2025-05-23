# Scripts de Utilidad para ShelterConnect

Este directorio contiene scripts útiles para el desarrollo y mantenimiento del proyecto ShelterConnect.

## Scripts Disponibles

### `test_completo.ps1`

Script de prueba completo para la API de ShelterConnect. Ejecuta una serie de pruebas que verifican todas las funcionalidades principales de la API.

**Uso:**
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\test_completo.ps1
```

**Pruebas que realiza:**
1. Autenticación de usuario admin
2. Obtener información del usuario autenticado
3. Listar organizaciones
4. Crear nueva organización
5. Obtener organización específica
6. Listar servicios
7. Listar beneficiarios
8. Listar intervenciones
9. Actualizar organización
10. Eliminar organización de prueba

### `simple_test.ps1`

Script de prueba simplificado que verifica funcionalidad básica de la API.

**Uso:**
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\simple_test.ps1
```

### `reset_migrations.bat`

Script para reiniciar completamente las migraciones de la base de datos. Útil durante el desarrollo cuando necesitas "empezar de cero".

**Uso:**
```cmd
.\scripts\reset_migrations.bat
```

## Notas Importantes

- Asegúrate de que el servidor Laravel esté ejecutándose antes de usar los scripts de prueba
- Los scripts de prueba requieren que la base de datos PostgreSQL esté configurada correctamente con la extensión PostGIS
- Estos scripts son compatibles con Windows y requieren PowerShell para los archivos .ps1

## Fecha de Última Actualización

23 de mayo de 2025
