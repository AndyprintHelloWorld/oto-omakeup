# Otoño Makeup

Tienda web en PHP y MySQL.

## Subir a GitHub

1. Crea un repositorio nuevo en GitHub.
2. Sube el contenido de esta carpeta `otono-makeup` a la raíz del repositorio. No subas la carpeta contenedora `OTOÑO`.
3. No agregues archivos `.env` ni contraseñas. `.gitignore` excluye configuraciones locales.

## Ejecutar localmente con XAMPP

1. Copia esta carpeta a `C:\xampp\htdocs\otono-makeup` e inicia Apache y MySQL.
2. Importa `database/otono_makeup.sql` desde phpMyAdmin.
3. Abre `http://localhost/otono-makeup/`.

La configuración local predeterminada usa `localhost`, base `otono_makeup`, usuario `root` y contraseña vacía. En producción, define `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` como variables de entorno en el proveedor de hosting.

## Crear el administrador

Ejecuta desde PowerShell en la carpeta del proyecto, usando una contraseña propia de al menos 12 caracteres:

```powershell
$env:ADMIN_EMAIL = "tu-correo@example.com"
$env:ADMIN_PASSWORD = "elige-una-clave-larga-y-unica"
php .\tools_create_admin.php
Remove-Item Env:ADMIN_EMAIL, Env:ADMIN_PASSWORD
```

El script solo se ejecuta por consola y no funciona desde el navegador.

## Publicación

Este proyecto requiere un servidor que ejecute PHP y una base de datos MySQL; Vercel no ejecuta este backend PHP de forma nativa. Puedes guardar el código en GitHub y desplegarlo en un hosting compatible con PHP/MySQL. Para usar Vercel habría que migrar el backend a una tecnología compatible y configurar una base de datos externa.

Las rutas del sitio actualmente esperan que la aplicación viva bajo `/otono-makeup/`, como en XAMPP. Si el hosting la publica en la raíz de un dominio, habrá que adaptar esas rutas antes de lanzarla.