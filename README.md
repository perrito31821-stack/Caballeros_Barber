# Caballeros Barber — Sistema de citas, administración y tienda

Tecnologías: PHP 8+, MySQL/MariaDB, Bootstrap 5, JavaScript y PWA.

## Instalación rápida
1. Copie la carpeta del proyecto dentro de `htdocs` (XAMPP), `www` o su servidor PHP.
2. Cree la base de datos `salon_bello`.
3. Importe **`sql_bd/caballeros_barber.sql`** desde phpMyAdmin.
4. Revise `config.php` por si necesita cambiar usuario/clave de MySQL o el WhatsApp administrativo.
5. Abra `http://localhost/Caballeros_Barber/index.php` (ajuste el nombre de la carpeta si usa otro).

## Administradores incluidos
- Administrador 1: `admin@salon.com` / `admin123`
- Administrador 2: `admin2@salon.com` / `admin123`

> Recomendado: cambie ambas contraseñas antes de publicar el sistema en Internet.

## Cambios implementados
- Marca actualizada a **Caballeros Barber**.
- Dirección: **Carrera 57a # 35a - 97 (Frente a la Iglesia de San Juan Bosco - Bello)**.
- Diseño visual renovado en negro, dorado y crema, adaptable a celular.
- Dos usuarios con rol administrador.
- 6 agendas de barbería: Barbero 1 a Barbero 6.
- 1 agenda independiente de Belleza.
- Los horarios se validan por agenda: distintos barberos y Belleza pueden reservar exactamente a la misma hora.
- La agenda de Belleza filtra servicios de belleza; las agendas de barberos filtran servicios de barbería.
- El panel administrativo muestra las 7 agendas por separado y conserva pagos, estados, recibos y resumen de ingresos.
- Se mantienen tienda, productos, carrito, login, registro, recuperación, recibos y demás funciones existentes.

## Actualización de proyección de citas
- La pantalla **Proyección de Citas** permite seleccionar una fecha exacta.
- Muestra las citas pendientes y confirmadas de Barbero 1 a Barbero 6 y Belleza correspondientes a esa fecha.
- La pantalla se actualiza automáticamente cada **5 segundos** y conserva la fecha seleccionada.
- Las agendas continúan siendo independientes, por lo que distintos profesionales pueden tener turnos a la misma hora.

## Actualización: ingresos por profesional y disponibilidad inteligente
- En **Administración de turnos > Historial > Servicios realizados** se conserva el resumen general de ingresos.
- Se agregó un desglose independiente para **Barbero 1 a Barbero 6 y Belleza**, con total de hoy, semana actual, últimos 15 días y mes actual.
- Los valores por profesional se calculan a partir de los pagos realmente registrados para sus citas.
- Si el cliente intenta reservar una hora ocupada con un barbero específico, el sistema muestra primero los **otros barberos libres exactamente a la misma fecha y hora**.
- También muestra los **próximos horarios disponibles del barbero originalmente elegido**, para quienes desean ser atendidos por una persona específica.
- La disponibilidad respeta la **duración configurada de cada servicio**, evitando cruces parciales entre citas.
- La agenda de Belleza mantiene su independencia; si su hora está ocupada, ofrece próximos espacios disponibles en esa misma agenda.

## Actualización: agendas en tiempo real y horario de barbería
- La sección **Administración de turnos / Agendas por profesional** consulta cambios cada 5 segundos para los administradores, sin recargar toda la página.
- Las agendas de **Barbero 1 a Barbero 6** aceptan reservas todos los días (lunes a domingo) únicamente dentro de la jornada de **09:00 a 22:00**. La duración completa del servicio debe finalizar máximo a las 22:00.
- Las sugerencias de próximos turnos para barberos respetan esa misma jornada.
- La sección **Belleza** conserva su comportamiento y horario previo, sin aplicar la restricción de barbería.

## Actualización: ventas de productos e ingresos generales
- Las compras realizadas desde la tienda quedan registradas en **Administración de turnos > Ventas de productos**.
- El **Resumen de ingresos** ahora suma automáticamente los pagos de servicios y las órdenes de productos que estén en estado **pagado**.
- Se muestran totales de productos vendidos para hoy, semana actual, últimos 15 días y mes actual.
- El historial de compras muestra número de compra, cliente, productos adquiridos, unidades, total, estado y fecha.
- Se conservan por separado los ingresos de servicios por profesional; las ventas de productos no se asignan a ningún barbero.


## Acceso de profesionales

Se incorporaron 7 cuentas independientes: Barbero1 a Barbero6 y Belleza. Cada profesional entra desde `Ingresar` con su usuario y contraseña. Su panel permite únicamente confirmar o cancelar reservas asignadas a su propia agenda. No existe opción de transferir turnos.

Las credenciales iniciales están en `CREDENCIALES_PROFESIONALES.txt`.

La aplicación ejecuta una migración automática al conectarse para agregar las columnas `username` y `password_hash` a `staff` si todavía no existen. Por ello, al actualizar una instalación existente no es necesario modificar manualmente la estructura antes de probarla.
