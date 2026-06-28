# Contexto del proyecto - Finanzas PWA

## Resumen

Finanzas PWA es una aplicacion privada para control de finanzas personales. Esta pensada para usarse desde navegador y desde iPhone como PWA instalada en pantalla de inicio, sin App Store.

## Stack actual

- Laravel 13.
- PHP 8.4.
- PostgreSQL.
- Inertia.js.
- React + TypeScript.
- Tailwind CSS.
- Vite.
- Breeze para autenticacion.
- PWA con manifest, iconos, service worker y pagina offline.

## Acceso local

Durante desarrollo local se sirve desde:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

URL local para la PC:

```text
http://127.0.0.1:8000
```

URL LAN para iPhone:

```text
http://192.168.18.5:8000
```

Para PWA completa y notificaciones push en iPhone se requiere HTTPS, idealmente en EC2 con dominio y Let's Encrypt.

## Base de datos

Base local:

```text
finanzas-app
```

Conexion configurada en `.env` con PostgreSQL local.

## Modulos implementados

- Registro, login, logout y recuperacion de contrasena.
- Bloqueo opcional de registro con `ALLOW_REGISTRATION`.
- Onboarding financiero inicial.
- Billeteras.
- Categorias base.
- Movimientos: ingreso, egreso, transferencia y ajuste.
- Prestamos recibidos.
- Pagos parciales o completos de prestamos recibidos.
- Prestamos otorgados.
- Cobros parciales o completos de prestamos otorgados.
- Presupuestos mensuales por categoria.
- Alertas internas.
- Reporte mensual.
- Exportacion CSV de movimientos.
- Metas de ahorro.
- Movimientos recurrentes.
- Logo propio e iconos PWA.

## Comandos utiles

Instalar dependencias:

```bash
composer install
npm install
```

Migrar base de datos:

```bash
php artisan migrate
```

Compilar frontend:

```bash
npm run build
```

Ejecutar tests:

```bash
php artisan test
```

Generar alertas internas:

```bash
php artisan finance:generate-alerts
```

Generar movimientos recurrentes vencidos:

```bash
php artisan finance:generate-recurring
```

## Archivos clave

- `app/Services/FinanceService.php`: logica contable principal.
- `app/Http/Controllers/*`: controladores de modulos.
- `app/Models/*`: modelos financieros.
- `database/migrations/*`: estructura de tablas.
- `resources/js/Pages/*`: pantallas Inertia React.
- `resources/js/lib/format.ts`: formatos de dinero, fechas y etiquetas visibles.
- `public/brand/logo.png`: logo principal.
- `public/manifest.webmanifest`: manifest PWA.
- `public/sw.js`: service worker.

## Regla contable

El saldo de una billetera se recalcula desde:

```text
saldo inicial
+ ingresos
+ prestamos recibidos
+ cobros de prestamos otorgados
- egresos
- pagos de prestamos recibidos
- prestamos otorgados
+/- ajustes
```

El backend usa nombres tecnicos en ingles. La interfaz de usuario debe mantenerse en espanol.
