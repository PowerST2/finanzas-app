# Mejoras del proyecto - Finanzas PWA

## Estado actual

Las mejoras locales planificadas ya estan implementadas.

## Implementado

- Edicion, duplicado y anulacion de movimientos con recalculo de saldos.
- Filtros y busqueda de movimientos.
- Adjuntos opcionales para comprobantes.
- Importacion CSV con previsualizacion y mapeo manual de columnas.
- Prestamos recibidos y otorgados con pagos/cobros parciales, progreso y linea de tiempo.
- Auditoria basica local.
- Backups locales con `php artisan finance:backup`.
- Calendario mensual en cuadricula con vencimientos, recurrentes y creacion rapida de movimientos.
- Reportes con CSV, vista imprimible/PDF, tendencia mensual y cierre mensual.
- Configuracion visual local: nombre, color y logo.
- Multi-moneda PEN/USD con tasa manual por billetera y conversion automatica en transferencias.
- Dashboard con resumen consolidado, barras de gastos y ultimo backup.
- Alertas locales para saldo bajo y prestamos proximos o vencidos.
- Panel de superusuario para gestionar usuarios, tipos de billetera, monedas y tipos de cambio.
- Recuperacion de contrasena por pregunta de seguridad sin correo.
- Respuestas de seguridad protegidas con hash y ocultas del frontend.
- Bloqueo de usuarios desactivados y proteccion para no eliminar el ultimo superusuario.
- Registro de intentos fallidos de recuperacion y bloqueo temporal tras varios intentos.
- Plantilla CSV descargable para importar movimientos.
- Reporte CSV corregido con ruta simple y columnas de moneda.
- Moneda principal por usuario con conversion automatica desde tasas centralizadas a soles.
- Monedas base PEN, USD y EUR configurables desde el panel de superusuario.

## Pendiente

No quedan mejoras locales pendientes de esta lista.

Las proximas mejoras deberian salir del uso real diario, no de agregar funciones por agregar.
