# 📋 Documentación Funcional del Sistema ERP POS

> **Sistema de Gestión Integral para Negocios de Alimentos y Bebidas**  
> Versión: 1.0 | Plataforma: Web (Laravel + Vue.js)

---

## 🎯 Propósito del Sistema

Este sistema ERP POS es una solución completa de gestión para negocios de alimentos, bebidas y servicios de restauración. Aunque fue inicialmente diseñado para una heladería, todas sus funcionalidades son **completamente transferibles** a cualquier negocio similar, incluyendo:

- 🍕 Restaurantes y pizzerías
- ☕ Cafeterías y panaderías
- 🥤 Juguerías y tiendas de bebidas
- 🍔 Comidas rápidas y hamburgueserías
- 🧋 Tiendas de snacks y dessert bars
- 🛒 Negocios con domicilios y entregas a domicilio

---

## 📦 Módulos del Sistema

### 1. 🛒 Punto de Venta (POS)

**Descripción:** Interfaz principal de cobro diseñada para velocidad y facilidad de uso en tablets y desktop.

**Funcionalidades:**
- Catálogo de productos organizado por **categorías configurables**
- Búsqueda de productos en tiempo real
- Carrito de compras interactivo con edición rápida de cantidades
- Soporte para **variaciones de producto** (tamaños, sabores, etc.)
- Soporte para **productos adicionales / add-ons** (toppings, extras)
- **Notas por ítem** para personalizar cada producto
- Selección de tipo de orden: **Mesa/Local** o **Domicilio**
- Para domicilios:
  - Campo de dirección de entrega
  - Selección de repartidor (lista configurable)
  - **Costo de domicilio editable** por pedido (con valor por defecto configurable)
- Búsqueda de cliente por teléfono con autocompletado
- Registro automático de nuevos clientes
- Sistema de **Puntos de Fidelidad** (10 puntos por orden)
- Aplicación de **Promociones** (descuento %, monto fijo, producto gratis)
- Múltiples métodos de pago: Efectivo, Nequi, Daviplata, Tarjeta
- **Impresión automática de comanda** al confirmar venta
- Apertura de nueva pestaña para imprimir el ticket térmico
- Validación de caja abierta antes de procesar venta

---

### 2. 🍳 Sistema Kanban de Cocina (KDS - Kitchen Display System)

**Descripción:** Tablero de control en tiempo real para gestionar el flujo de producción de órdenes.

**Columnas del Kanban:**
1. **Recepción** (amarillo) — Pedidos recién ingresados esperando confirmación
2. **En Preparación** (naranja) — Pedidos en proceso en cocina
3. **Listos para Despachar** (verde) — Listos para llevar o enviar
4. **En Camino** (azul, solo domicilios) — Pedidos con domiciliario en ruta

**Funcionalidades por columna:**
- Visualización del tiempo transcurrido desde el pedido (**timer en vivo**)
- Indicador de tipo de orden (domicilio / local) con iconos
- Información del repartidor asignado
- Dirección de entrega visible
- Botones de acción rápida:
  - ✅ Avanzar al siguiente estado
  - 🛵 Enviar a domicilio (solo órdenes listas para domicilio)
  - 🖨️ Reimprimir comanda
  - ❌ Cancelar orden en cualquier momento
- Contador total de órdenes activas
- Scroll horizontal en mobile para navegar entre columnas

---

### 3. 📍 Tracking de Pedido para el Cliente

**Descripción:** Página pública de seguimiento de pedido en tiempo real, accesible por el cliente vía enlace (WhatsApp, SMS, etc.)

**Funcionalidades:**
- URL única por pedido: `/tracking/{id}`
- Sin necesidad de login para el cliente
- **Diseño diferenciado por tipo de orden:**
  - 🍽️ **Local:** Progreso simple (Recibido → Cocina → Listo)
  - 🛵 **Domicilio:** Header oscuro tipo delivery app con dirección de entrega, pasos con iconos animados
- Pasos de progreso con círculos de colores:
  - ✓ Verde = completado
  - 🟡 Amarillo = estado actual activo
  - ⚪ Gris = pendiente
- Animación de motocicleta 🛵 cuando el pedido está en camino
- **Botón "¡Ya recibí mi pedido!"** — el cliente confirma la entrega directamente
  - Actualiza el estado a `completed` en tiempo real
  - Notifica al sistema (WebSocket)
  - Dispara mensaje de WhatsApp de confirmación
- Sección de **calificación con estrellas** (1-5) al finalizar
- Campo de comentarios opcionales
- Conexión en tiempo real via **WebSockets (Socket.io)**
- Sonidos de notificación al avanzar de estado
- Soporte de notificaciones push del navegador

---

### 4. 🖨️ Sistema de Comandas Térmicas

**Descripción:** Generación e impresión de tickets de venta optimizados para impresoras térmicas de 58mm y 80mm.

**Contenido del ticket:**
- Nombre del negocio y logo (configurable)
- Número y fecha/hora del pedido
- Tipo de orden (Mesa/Local o Domicilio)
- Nombre y teléfono del cliente
- Dirección de entrega (para domicilios)
- Repartidor asignado
- Listado de productos con:
  - Cantidad y nombre
  - Variación seleccionada
  - Notas especiales por ítem
- Subtotal, costo de domicilio y total
- Método de pago
- Botón de impresión directa en el navegador
- Diseño responsive centrado sin desbordamiento

---

### 5. 💰 Control de Caja

**Descripción:** Módulo para gestionar sesiones de caja, ingresos y notas de movimiento.

**Funcionalidades:**
- Apertura y cierre de sesión de caja con monto inicial
- Resumen de ventas en tiempo real por método de pago:
  - Efectivo, Nequi, Daviplata, Tarjeta
- Total facturado en la sesión actual
- Conteo de órdenes completadas
- Sistema de **notas de caja** (registro de movimientos manuales, retiros, etc.)
- Validación: no se pueden crear órdenes sin una caja abierta
- Historial de sesiones anteriores

---

### 6. 📊 Reportes y Estadísticas

**Descripción:** Análisis de ventas y métricas de negocio.

**Funcionalidades:**
- Gráfico de tendencia de ventas de los últimos 7 días
- Tabla de todas las órdenes con filtros
- Métricas del Dashboard:
  - 💰 Ventas totales del día
  - 🔥 Pedidos activos en este momento
  - ⏱️ Tiempo promedio de atención (minutos)
  - ⚠️ Alertas de stock bajo
- Listado de órdenes exportable
- Integración con feedback de clientes

---

### 7. ⭐ Sistema de Calificaciones y Feedback

**Descripción:** Recolección y visualización de opiniones de clientes post-venta.

**Funcionalidades:**
- Calificación de 1 a 5 estrellas desde el tracking
- Campo de comentario libre
- Visualización en Dashboard (últimas 5 calificaciones)
- Módulo de **Comentarios** con historial completo
- Filtrable por fecha y calificación

---

### 8. 🎁 Sistema de Promociones

**Descripción:** Gestión de descuentos y ofertas aplicables al momento de la venta.

**Tipos de Promociones:**
- 📉 **Porcentaje (%):** Descuento sobre el subtotal (ej. 10% de descuento)
- 💵 **Valor Fijo ($):** Monto fijo de descuento (ej. $5,000 menos)
- 🎁 **Producto Gratis:** Selección de un producto del catálogo como regalo
- 🍿 **Addon Gratis:** Un extra/topping gratuito

**Funcionalidades:**
- Creación, edición y eliminación de promociones
- Activación/desactivación sin borrar
- Selección de producto gratis desde el catálogo completo
- Aplicación desde el POS al momento del checkout
- Visible en el resumen de la orden y en el ticket

---

### 9. 📦 Inventario de Ingredientes

**Descripción:** Seguimiento del stock de materias primas e insumos.

**Funcionalidades:**
- Listado de ingredientes con stock actual
- Alerta automática de stock bajo (umbral configurable)
- Indicador en Dashboard cuando hay alertas activas
- Asociación de ingredientes a productos (receta)
- Descuento automático de stock al realizar ventas

---

### 10. 🧁 Gestión de Productos y Menú

**Descripción:** Administración completa del catálogo de productos.

**Funcionalidades:**
- Creación y edición de productos
- Asignación de **categorías** (configurables)
- **Variaciones de producto** (tamaños, presentaciones con precio diferencial)
- **Add-ons / Extras** como productos opcionales
- Gestión de insumos/ingredientes por producto
- Soporte para imágenes de productos
- Soft delete (productos desactivados no eliminados)
- Control de productos **addon** (solo disponibles como extras)

---

### 11. 👥 Clientes y Fidelización

**Descripción:** Base de datos de clientes con historial de compras.

**Funcionalidades:**
- Registro automático al primer pedido (por teléfono)
- Historial de órdenes por cliente
- Sistema de puntos acumulables
- Clasificación por puntos (potencial para niveles de fidelidad)
- Autocompletado del nombre al ingresar teléfono en POS

---

### 12. 🚴 Gestión de Repartidores

**Descripción:** Directorio de domiciliarios disponibles para asignar a pedidos.

**Funcionalidades:**
- Alta, edición y baja de repartidores
- Datos: nombre, teléfono, placa del vehículo
- Estado activo/inactivo
- Filtrado en POS: solo muestra repartidores activos al asignar domicilio
- Posibilidad de integración con WhatsApp para notificaciones automáticas

---

### 13. 👤 Usuarios y Roles

**Descripción:** Control de acceso multi-usuario con roles.

**Roles disponibles:**
- **Admin:** Acceso total al sistema
- **Cajero / Operador:** Acceso al POS, Kanban y Caja (sin configuración ni reportes detallados)

**Funcionalidades:**
- Creación de usuarios con email y contraseña
- Asignación de roles
- Login con sesión persistente

---

### 14. ⚙️ Configuración del Sistema

**Descripción:** Panel de administración para personalizar el comportamiento del sistema.

**Secciones:**
- **Categorías de Productos:** Crear/eliminar categorías que aparecen en el POS y catálogo
- **Repartidores:** Directorio de domiciliarios
- **Promociones:** Gestión de descuentos y ofertas
- **Parámetros Clave-Valor (Avanzado):**
  - `default_delivery_fee`: Tarifa base de domicilio
  - Cualquier parámetro personalizable del negocio

---

### 15. 📱 WhatsApp Business Integration

**Descripción:** Mensajería automática con los clientes vía WhatsApp.

**Mensajes automáticos:**
- ✅ Pedido en preparación
- 🛵 Pedido en camino (con tiempo estimado)
- 🍨 Pedido listo para recoger
- 📍 Enlace de tracking en tiempo real
- ⭐ Solicitud de calificación post-entrega

---

## 🏗️ Arquitectura Técnica

| Componente | Tecnología |
|-----------|-----------|
| Backend | Laravel 10 (PHP 8.2) |
| Frontend | Vue 3 + Inertia.js |
| Base de Datos | MySQL 8 |
| Tiempo Real | Node.js + Socket.io |
| Estilos | Tailwind CSS |
| Autenticación | Laravel Sanctum + Spatie Permissions |
| WhatsApp | Integración con API Node (baileys/whatsapp-web.js) |
| Impresión | Print nativo del navegador (CSS print media) |

---

## 🔄 Flujo Completo de una Orden

```
1. CAJERO abre POS
2. Selecciona productos → agrega notas, variaciones, extras
3. Selecciona tipo (Local o Domicilio)
   └─ Domicilio: ingresa dirección, asigna repartidor, ajusta tarifa
4. Busca cliente por teléfono (opcional)
5. Aplica promoción (opcional)
6. Cobra con método de pago
7. Sistema imprime comanda automáticamente
8. Orden aparece en Kanban → Columna "Recepción"
9. Cocinero/Cajero avanza a "En Preparación"
10. Al terminar → "Listos para Despachar"
    ├─ Local: Cajero entrega → "Completado"
    └─ Domicilio: "Mandar en Camino" → Repartidor sale
         → Cliente ve tracking con animación 🛵
         → Cliente confirma llegada → "Completado"
11. Cliente califica en tracking (estrellas + comentario)
12. Dashboard actualiza métricas en tiempo real
```

---

## 📐 Responsive Design

El sistema está optimizado para:
- 📱 **Móvil (320px+):** POS táctil, Kanban con scroll horizontal, bottom navigation
- 📟 **Tablet (768px+):** Layout híbrido
- 🖥️ **Desktop (1024px+):** Sidebar completo, múltiples columnas
- 🖨️ **Impresión:** Vista optimizada para impresoras térmicas

---

## 🔐 Seguridad

- Autenticación por sesión con CSRF protection
- Rutas protegidas por middleware de auth y roles
- Rutas públicas limitadas (tracking, confirm delivery) sin información sensible
- Soft delete para protección de datos históricos

---

## 🚀 Adaptabilidad a Otros Negocios

Para adaptar el sistema a otro tipo de negocio, solo se requiere:

1. **Cambiar el nombre/logo** en la configuración
2. **Redefinir categorías** de productos desde el panel
3. **Ajustar parámetros** de domicilio y tarifas
4. **Personalizar el ticket** de comanda con el nombre del negocio
5. Opcionalmente desactivar módulos no necesarios (ej. domicilios si es solo local)

El sistema es modular y todos sus componentes funcionan de forma independiente.

---

*Documento generado automáticamente | Sistema ERP POS v1.0*
