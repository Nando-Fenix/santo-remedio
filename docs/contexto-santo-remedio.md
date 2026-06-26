# Santo Remedio - Contexto técnico del proyecto

## 1. Objetivo principal del sistema

El sistema Santo Remedio es una aplicación web para gestión de farmacia, enfocada en:

* Velocidad en la atención al cliente.
* Velocidad en los procedimientos internos del sistema.
* Seguridad de datos sensibles.
* Interfaz intuitiva para usuarios no programadores.
* Código claro y documentado para facilitar cambios futuros.

El sistema debe permitir gestionar ventas, caja, inventario, lotes, vencimientos, compras, proveedores, clientes, reportes y movimientos importantes del negocio.

---

## 2. Principios obligatorios del proyecto

### Velocidad

El sistema debe reducir pasos innecesarios.
Los módulos principales deben evitar procesos lentos o confusos.

Prioridad alta:

* Venta rápida.
* Búsqueda por nombre, concentración o código de barras.
* Compras con buscador rápido.
* Lote automático si el usuario no ingresa lote.
* Menos pantallas para tareas frecuentes.

### Seguridad

No se deben eliminar registros importantes directamente.

En ventas, compras, caja e inventario se debe usar:

* Estados como `activo`, `inactivo`, `completada`, `anulada`, `registrada`.
* Movimientos de trazabilidad.
* Motivos obligatorios para anulaciones.
* Registro de usuario, sucursal, fecha y relación afectada.

### Intuitivo

Los usuarios de la farmacia no son programadores.

Por eso el sistema debe usar:

* Botones claros.
* Alertas visibles con SweetAlert2.
* Formularios cortos.
* Mensajes de error entendibles.
* Confirmaciones antes de acciones delicadas.

### Código documentado

Cada módulo delicado debe mantener código claro y comentado cuando sea necesario.

Módulos delicados:

* Ventas.
* Caja.
* Inventario.
* Compras.
* Anulaciones.
* Reembolsos.
* Deudas.

---

## 3. Stack técnico

* Laravel.
* MySQL.
* Laragon.
* Blade.
* CSS propio en `resources/css/app.css`.
* JavaScript en vistas Blade para carritos y búsquedas rápidas.
* SweetAlert2 para alertas y confirmaciones.
* Autenticación propia con campo `usuario`.

---

## 4. Decisiones importantes tomadas

### Productos

Un producto debe existir una sola vez.

Incorrecto:

* Paracetamol 500 mg - Proveedor A.
* Paracetamol 500 mg - Proveedor B.

Correcto:

* Producto único: Paracetamol 500 mg.
* Puede comprarse a varios proveedores mediante compras y detalle de compras.

### Proveedores

El proveedor no debe obligar a duplicar productos.

La relación real entre proveedor y producto se obtiene mediante:

```text
Proveedor → Compra → DetalleCompra → Producto
```

El campo `productos.proveedor_id`, si existe, debe interpretarse como proveedor principal o sugerido, no como historial real.

### Lotes

Regla:

* Si el producto trae lote real del laboratorio/proveedor, se registra ese lote.
* Si el usuario deja el lote vacío, el sistema genera un lote interno automático.

Formato interno usado:

```text
LC-YYYYMMDD-HHMMSS-XXX
```

Ejemplo:

```text
LC-20260609-174512-384
```

### Caja

La caja es sensible.

Una venta solo debe registrarse si existe una caja abierta para el usuario y sucursal actual.

La caja cerrada no debe modificarse desde ventas normales.

### Anulaciones

No se eliminan ventas ni compras.
Se marcan como anuladas y se registran movimientos correspondientes.

---

## 5. Módulos ya implementados

### Autenticación

* Login propio.
* Campo `usuario`.
* Campo `password`.
* Validación de estado activo.
* Actualización de `ultimo_acceso`.

Usuario inicial:

```text
Usuario: admin
Contraseña: admin12345
```

---

## 6. Usuarios, roles y sucursales

Tablas principales:

* `users`
* `roles`
* `permisos`
* `rol_permiso`
* `sucursales`
* `usuario_sucursal`

Roles actuales:

* Administrador.
* Vendedor.

La relación usuario-sucursal permite identificar la sucursal principal del usuario.

---

## 7. Productos

Tablas principales:

* `productos`
* `categorias`
* `laboratorios`
* `proveedores`
* `presentaciones`
* `producto_presentaciones`
* `codigos_barras`

Reglas:

* Un producto puede tener varias presentaciones.
* Una presentación tiene unidades equivalentes.
* Los códigos de barras pueden ser de fabricante o internos.
* El sistema está preparado para códigos internos, aunque la impresión de etiquetas queda pendiente.

---

## 8. Inventario

Tablas principales:

* `lotes`
* `inventarios`
* `movimientos_inventario`

Reglas:

* El inventario depende de producto, sucursal y lote.
* El stock se maneja por unidades base.
* Las ventas descuentan stock usando FEFO: primero el lote con vencimiento más próximo.
* Los movimientos de inventario deben registrar entradas, salidas y anulaciones.

---

## 9. Ventas

Tablas principales:

* `ventas`
* `detalle_ventas`
* `detalle_venta_lotes`
* `pagos_venta`
* `recibos`
* `clientes`
* `metodos_pago`

Reglas:

* La venta requiere caja abierta.
* La venta puede tener cliente opcional.
* Si no hay cliente, se registra como consumidor final.
* Puede aplicarse descuento por cliente o descuento manual.
* La venta descuenta inventario por lotes.
* Si una venta descuenta varios lotes, el detalle principal no se duplica.
* El desglose por lote se guarda en `detalle_venta_lotes`.

Flujo de venta:

```text
Buscar producto
Agregar al carrito
Seleccionar cliente opcional
Aplicar descuento
Seleccionar método de pago
Registrar venta
Descontar inventario
Registrar pago
Registrar movimiento de caja
Generar recibo
```

---

## 10. Caja

Tablas principales:

* `turnos`
* `cajas`
* `movimientos_caja`
* `cierres_caja`
* `arqueo_denominaciones`

Reglas:

* El usuario debe abrir caja antes de vender.
* Las ventas en efectivo aumentan `total_efectivo`.
* Las ventas por QR aumentan `total_qr`.
* El `total_final` representa efectivo físico esperado:

```text
monto_inicial + total_efectivo - total_egresos - total_reembolsos
```

* QR no debe sumarse al efectivo físico.
* El cierre de caja usa arqueo por denominaciones.

---

## 11. Clientes

Tablas principales:

* `clientes`
* `ventas`

Funciones implementadas:

* Crear cliente.
* Editar cliente.
* Desactivar cliente.
* Seleccionar cliente en venta.
* Historial de compras por cliente.
* Total comprado.
* Cantidad de compras.
* Última compra.

Reglas:

* El cliente es opcional en la venta.
* El descuento del cliente puede aplicarse automáticamente en venta.

---

## 12. Proveedores

Tablas principales:

* `proveedores`
* `compras`
* `detalle_compras`

Funciones implementadas:

* Crear proveedor.
* Editar proveedor.
* Desactivar proveedor.
* Ver proveedor.
* Ver productos asociados mediante historial de compras.
* Ver total comprado.
* Ver total pagado.
* Ver saldo pendiente.
* Ver últimas compras.

Regla importante:

```text
Proveedor no define producto duplicado.
Proveedor se relaciona con producto mediante compras.
```

---

## 13. Compras

Tablas principales:

* `compras`
* `detalle_compras`
* `pagos_compras`

Funciones implementadas:

* Registrar compra a proveedor.
* Buscar productos rápidamente por AJAX.
* Agregar productos a carrito de compra.
* Registrar lote manual o automático.
* Registrar vencimiento.
* Registrar precio compra.
* Ingresar stock automáticamente al inventario.
* Registrar movimiento de inventario tipo entrada.
* Registrar pago inicial.
* Manejar compras al contado, crédito o pago parcial.
* Calcular saldo pendiente.

Flujo de compra:

```text
Seleccionar proveedor
Buscar producto existente
Seleccionar producto
Ingresar cantidad
Ingresar precio compra
Ingresar lote o dejar vacío
Ingresar vencimiento
Agregar al carrito
Registrar monto pagado
Guardar compra
Actualizar inventario
Registrar deuda si corresponde
```

Pendiente:

* Crear producto rápido desde compras.
* Crear laboratorio rápido desde compras.
* Crear presentación rápida desde compras.

---

## 14. Pagos a proveedores

Funciones implementadas:

* Registrar pago posterior a una compra pendiente o parcial.
* Actualizar `monto_pagado`.
* Actualizar `saldo_pendiente`.
* Cambiar estado de pago:

  * `pendiente`
  * `parcial`
  * `pagado`

Reporte implementado:

* Deudas con proveedores.

---

## 15. Anulación de ventas

Reglas implementadas:

* Solo se puede anular una venta completada.
* Solo se puede anular una venta de la caja abierta actual.
* La anulación devuelve stock a los mismos lotes usados.
* Registra movimiento de inventario tipo entrada.
* Resta el ingreso de caja.
* Registra movimiento de caja.
* La venta queda con estado `anulada`.
* Se guarda motivo en observación.
* No se elimina la venta.

---

## 16. Anulación de compras

Reglas implementadas:

* Solo se puede anular una compra registrada.
* Solo se puede anular si todavía existe stock suficiente de los lotes ingresados.
* Si parte del stock ya fue vendido o movido, la anulación se bloquea.
* La anulación descuenta del inventario lo que ingresó por esa compra.
* Registra movimiento de inventario tipo salida.
* La compra queda con estado `anulada`.
* El saldo pendiente queda en 0 para que no aparezca como deuda activa.
* No se elimina la compra.

---

## 17. Alertas visuales

SweetAlert2 ya está integrado.

Usos actuales:

* Mensajes de éxito.
* Mensajes de error.
* Confirmaciones de acciones importantes.
* Confirmar cierre de caja.
* Confirmar desactivaciones.
* Confirmar pagos.
* Confirmar anulaciones.

Regla:

Usar SweetAlert2 para acciones delicadas, no `confirm()` ni `alert()` simples.

---

## 18. Reportes implementados

Reportes generales:

* Ventas del día.
* Total vendido.
* Total efectivo.
* Total QR.
* Stock bajo.
* Productos agotados.
* Productos por vencer.
* Últimas ventas.

Reporte de proveedor:

* Total comprado.
* Total pagado.
* Saldo pendiente.
* Compras recientes.

Reporte de deudas:

* Total deuda.
* Compras con deuda.
* Proveedores con deuda.
* Acceso directo a pago.

---

## 19. Dashboard

El dashboard ya muestra datos reales.

Corrección importante:

* Debe enfocarse en caja abierta actual, no en ventas de cajas pasadas.
* La fecha/hora debe usar zona horaria de Bolivia:

```php
'timezone' => 'America/La_Paz'
```

Pendiente visual:

* Simplificar dashboard.
* Reducir saturación.
* Mover alertas de stock/vencimiento a un ícono de notificaciones.

---

## 20. Pendientes principales

### Reembolsos

Crear:

* `reembolsos`
* `detalle_reembolsos`

Funciones:

* Reembolso parcial.
* Devolver stock.
* Ajustar caja.
* Registrar motivo.
* Mantener historial.

### Cambios de producto

Permitir:

* Devolver un producto.
* Entregar otro producto.
* Ajustar diferencia de precio.
* Ajustar stock de ambos productos.
* Registrar motivo.

### Creación rápida desde compras

Pendiente importante para velocidad:

* Crear producto rápido.
* Crear laboratorio rápido.
* Crear presentación rápida.
* Crear código de barras opcional.

### Notificaciones

Pendiente:

* Ícono de alertas.
* Stock bajo.
* Productos vencidos.
* Productos por vencer.
* Compras con deuda.
* Caja abierta.

### Permisos reales

Actualmente existen roles y permisos en base de datos, pero falta aplicar middleware o validación real por permisos.

Pendiente:

* Proteger rutas.
* Ocultar botones según rol.
* Separar permisos de administrador y vendedor.

### Seguridad adicional

Pendiente:

* Intentos fallidos de login.
* Registro de sesiones.
* Auditoría de acciones críticas.
* Respaldos.
* Políticas de acceso.

---

## 21. Reglas para usar otra IA en este proyecto

Este archivo debe entregarse a cualquier IA externa antes de pedirle ayuda.

La otra IA puede ayudar con:

* Formularios Blade.
* Mejoras visuales.
* Comentarios de código.
* Validaciones simples.
* Refactorización pequeña.
* CSS.
* Componentes repetitivos.

La otra IA no debe cambiar sin revisión:

* Lógica de inventario.
* Lógica de caja.
* Anulación de ventas.
* Anulación de compras.
* Descuento de stock por lotes.
* Cierre de caja.
* Pagos a proveedores.
* Relaciones críticas entre tablas.

Regla:

```text
Otra IA puede escribir código, pero las decisiones críticas deben mantenerse en este contexto.
```

---

## 22. Convenciones de desarrollo

### Nombres de estados

Usar estados claros:

```text
activo
inactivo
completada
anulada
registrada
agotado
pagado
pendiente
parcial
```

### No borrar datos críticos

No usar delete real para:

* Ventas.
* Compras.
* Caja.
* Movimientos de inventario.
* Movimientos de caja.
* Pagos.

Usar estados y observaciones.

### Mantener trazabilidad

Toda acción crítica debe guardar:

* Usuario.
* Sucursal.
* Fecha.
* Motivo.
* Referencia.
* Movimiento relacionado.

### Código

Mantener código claro y, cuando sea necesario, comentado.

Priorizar:

* Legibilidad.
* Seguridad.
* Flujo estable.
* Fácil mantenimiento.

---

## 23. Próximo módulo recomendado

Continuar con:

```text
Reembolsos parciales de ventas
```

Después:

```text
Cambios de producto
Creación rápida de producto desde compras
Dashboard visual limpio
Notificaciones
Permisos reales
```

### Mejora UX/UI de pantallas

Actualmente las pantallas funcionan, pero algunas no son suficientemente intuitivas para usuarios no técnicos.

Pendiente:
- Reducir saturación visual.
- Usar iconos para acciones frecuentes.
- Agrupar acciones por importancia.
- Diferenciar botones principales, secundarios y peligrosos.
- Mejorar pantallas de ventas, compras, caja, inventario y dashboard.
- Crear una navegación más clara para vendedor y administrador.
- Agregar notificaciones visuales para alertas en lugar de mostrar demasiadas tarjetas.

### Cambios de producto

El módulo de cambios de producto permite devolver un producto vendido y entregar otro producto nuevo en una misma operación.

Reglas:
- La venta original no se elimina.
- El producto devuelto vuelve al inventario.
- El producto nuevo descuenta inventario usando FEFO.
- Si el producto nuevo cuesta más, el cliente paga la diferencia.
- Si el producto nuevo cuesta menos, la farmacia devuelve la diferencia.
- Se debe registrar motivo, usuario, sucursal, caja y movimientos de inventario/caja.

### Consultas con JOIN

Cuando una consulta usa `join` o `leftJoin`, las columnas del `where` deben escribirse con nombre de tabla para evitar ambigüedad en MySQL.

Correcto:

//php
->where('inventarios.producto_id', $productoId)
->where('inventarios.sucursal_id', $sucursalId)
->where('inventarios.stock_actual', '>', 0):


//
### Corrección aplicada en cambios de producto

En el módulo de cambios de producto se corrigió una consulta con `leftJoin` entre `inventarios` y `lotes`.

Cuando se usa `leftJoin`, las columnas del `where` deben ir con el nombre de la tabla para evitar ambigüedad en MySQL.

Ejemplo correcto:

// php
->where('inventarios.producto_id', $presentacionNueva->producto_id)
->where('inventarios.sucursal_id', $venta->sucursal_id)
->where('inventarios.stock_actual', '>', 0) 

### Anulación de cambios de producto

Para mantener trazabilidad profesional, la tabla `cambios_producto` tiene campos separados para auditoría de anulación:

- `usuario_anulacion_id`
- `fecha_anulacion`
- `motivo_anulacion`

Regla:
- El motivo original del cambio no se sobrescribe.
- La anulación queda registrada por separado.
- Esto permite saber quién registró el cambio y quién lo anuló.

/**/
### Anulación de cambio de producto

La anulación de un cambio de producto revierte la operación original.

Reglas:
- Solo se puede anular un cambio con estado `registrado`.
- Solo se puede anular si pertenece a la caja abierta actual.
- El producto devuelto en el cambio se descuenta nuevamente del inventario.
- El producto nuevo entregado en el cambio vuelve al inventario.
- Si el cliente pagó diferencia, al anular la diferencia sale de caja.
- Si la farmacia devolvió diferencia, al anular la diferencia se revierte en caja.
- El cambio no se elimina; queda con estado `anulado`.
- Se guardan `usuario_anulacion_id`, `fecha_anulacion` y `motivo_anulacion`.

### Checkpoint funcional - ventas, reembolsos y cambios

Estado comprobado:
- Venta rápida funcional.
- Anulación de venta funcional.
- Reembolso parcial funcional.
- Cambio de producto funcional.
- Anulación de cambio de producto funcional.
- Caja se ajusta correctamente.
- Inventario se actualiza por lotes.
- Movimientos de inventario quedan registrados.
- Movimientos de caja quedan registrados.
- No se eliminan registros críticos; se usan estados y auditoría.


### Manejo de errores AJAX en desarrollo

Durante el desarrollo, los `catch` de JavaScript pueden mostrar `error.message` para facilitar la depuración.

Antes de entregar el sistema al cliente, los mensajes técnicos deben cambiarse por mensajes amigables, manteniendo `console.error()` para depuración.

### Producto rápido desde compras

El módulo de compras permite crear un producto nuevo sin salir de la pantalla de registro de compra.

Mejora aplicada:
- El formulario de producto rápido se muestra en modal.
- Esto evita confusión visual con el formulario principal de compra.
- Al guardar el producto, se crea:
  - producto
  - producto_presentacion
  - código de barras opcional
- El producto creado queda seleccionado automáticamente para continuar la compra.

### Z-index de modales

El modal de producto rápido usa `z-index: 9999`.

SweetAlert2 debe estar por encima para permitir crear laboratorio o presentación rápida dentro del modal de producto.

Regla CSS aplicada:

"```css''"
.swal2-container {
    z-index: 20000 !important;
}'

### Checkpoint funcional - creación rápida desde compras

Estado comprobado:
- Producto rápido funciona desde compras.
- El formulario de producto rápido se muestra en modal.
- El producto creado queda seleccionado automáticamente.
- Categoría rápida funciona.
- Laboratorio rápido funciona.
- Presentación rápida funciona.
- Proveedor rápido funciona.
- Los modales de SweetAlert2 aparecen por encima del modal principal.
- El modal de proveedor rápido fue ajustado visualmente para evitar espacios desordenados.

### Checkpoint funcional - caja compartida por sucursal

Se corrigió la lógica de caja para que la caja abierta dependa de la sucursal y no del usuario.

Regla actual:
- Una sucursal solo puede tener una caja abierta.
- Varios usuarios de la misma sucursal pueden vender usando la misma caja.
- cajas.usuario_id representa el usuario que abrió la caja.
- ventas.usuario_id representa el usuario que realizó la venta.
- movimientos_caja.usuario_id representa el usuario que realizó el movimiento.
- El cierre de caja puede ser realizado por un usuario autorizado de la misma sucursal.

### Checkpoint funcional - menú por permisos

Se reorganizó el menú principal del sistema.

Cambios realizados:
- Se agregó botón destacado para Venta rápida.
- Se agruparon opciones por secciones: Principal, Operación, Administración y Control.
- El menú ahora muestra opciones según permisos del usuario.
- Se agregaron iconos simples y tooltips.
- Se corrigió el scroll del menú lateral para usuarios administradores con más opciones.
- El vendedor solo visualiza las opciones permitidas.
- El administrador visualiza todos los módulos habilitados.

### Checkpoint funcional - botones internos por permisos

Se ajustó la vista de detalle de venta.

Cambios:
- El botón Registrar reembolso solo aparece con permiso `reembolsar_venta`.
- El botón Cambio de producto solo aparece con permiso `cambiar_producto`.
- El botón Anular venta solo aparece con permiso `anular_venta`.
- Se eliminó el bloque duplicado de cambios de producto registrados.
- Las rutas siguen protegidas por middleware, y la vista ahora evita mostrar acciones no permitidas.

### Checkpoint funcional - botones de caja por permisos

Se ajustó la vista de caja para mostrar acciones según permisos.

Cambios:
- Abrir caja solo aparece con permiso `abrir_caja`.
- Ver movimientos solo aparece con permiso `ver_caja`.
- Registrar egreso solo aparece con permiso `registrar_egreso`.
- Cerrar caja solo aparece con permiso `cerrar_caja`.
- Las rutas siguen protegidas por middleware y la vista evita mostrar acciones no permitidas.

### Checkpoint funcional - botones de compras por permisos

Se ajustó la vista de listado de compras.

Cambios:
- Nueva compra solo aparece con permiso `registrar_compra`.
- Ver compra solo aparece con permiso `ver_compras`.
- Pagar compra solo aparece si hay saldo pendiente, la compra no está anulada y el usuario tiene permiso `pagar_compra`.
- Anular compra solo aparece si la compra no está anulada y el usuario tiene permiso `anular_compra`.
- Las rutas siguen protegidas por middleware y la vista evita mostrar acciones no permitidas.

### Checkpoint funcional - botones de productos por permisos

Se ajustó la vista de listado de productos.

Cambios:
- Nuevo producto solo aparece con permiso `crear_producto`.
- Presentaciones solo aparece con permiso `ver_productos`.
- Editar solo aparece con permiso `editar_producto`.
- Desactivar solo aparece con permiso `desactivar_producto`.
- La confirmación de desactivación usa SweetAlert mediante `confirmarFormulario`.
- Las rutas siguen protegidas por middleware y la vista evita mostrar acciones no permitidas.

### Checkpoint funcional - botones de clientes por permisos

Se ajustó la vista de listado de clientes.

Cambios:
- Nuevo cliente solo aparece con permiso `crear_cliente`.
- Ver cliente solo aparece con permiso `ver_clientes`.
- Editar cliente solo aparece con permiso `editar_cliente`.
- Desactivar cliente solo aparece con permiso `eliminar_cliente`.
- Las rutas siguen protegidas por middleware y la vista evita mostrar acciones no permitidas.

### Checkpoint funcional - botones de proveedores por permisos

Se ajustó la vista de listado de proveedores.

Cambios:
- Nuevo proveedor solo aparece con permiso `crear_proveedor`.
- Ver proveedor solo aparece con permiso `ver_proveedores`.
- Editar proveedor solo aparece con permiso `editar_proveedor`.
- Desactivar proveedor solo aparece con permiso `eliminar_proveedor`.
- El usuario sin permiso `ver_proveedores` no puede ingresar al módulo.
- Las rutas siguen protegidas por middleware y la vista evita mostrar acciones no permitidas.

### Checkpoint funcional - vista de usuarios por permisos

Se ajustó la vista de listado de usuarios.

Cambios:
- Nuevo usuario solo aparece con permiso `administrar_usuarios`.
- Editar usuario solo aparece con permiso `administrar_usuarios`.
- Desactivar usuario solo aparece con permiso `administrar_usuarios`.
- Se corrigió la lectura de sucursal principal usando `principal` en lugar de `es_principal`.
- El usuario no puede desactivarse a sí mismo.
- Las rutas siguen protegidas por middleware.