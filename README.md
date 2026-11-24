# Canastillas de la Baja — sitio público

Sitio web de Canastillas de la Baja (La Paz, B.C.S.) hecho en PHP 8, SASS y JavaScript. Es un frontend sobre la base de datos de **cotizaciones**, igual que Soluciones en Acero:

- Catálogo de productos publicado desde `productos` (`EMPRESA_ID=2`).
- Imágenes de producto desde el volumen de cotizaciones mediante `/img/catalogo/{archivo}`.
- Fotos de entrega firmadas desde `ventas → entregas → entrega_fotos` mediante `/img/entregas/{id}`.
- Login: por ahora **solo pantalla**, sin autenticación ni portal.

## Estructura

```
controllers/      PaginasController, FotosController, ImagenesController
models/           ModeloDB, Catalogo, Categoria, Producto, Galeria
router/           Router con rutas GET/POST y parámetros {id}/{archivo}
includes/         app.php, funciones.php, database.php, .env.example
data/             sitio.php y fichas de respaldo de productos
views/            layout.php, paginas/, auth/, templates/
src/              SCSS, JS, imágenes y video (fuentes)
build/            CSS, JS e imágenes compiladas por Gulp
deployment/       Helm chart del sitio (sin MySQL propio)
```

## Rutas

| Ruta | Página |
|---|---|
| `/` | Inicio |
| `/login` | Pantalla de login (sin autenticación todavía) |
| `/img/catalogo/{archivo}` | Proxy de imágenes de productos |
| `/img/entregas/{id}` | Foto de entrega firmada, modo galería |

Las rutas antiguas `/admin`, `/cliente`, `/proveedor` y `/logout` ya no existen.

## Desarrollo local

```bash
npm install
npm run build       # compila SASS, JS e imágenes a build/
npm run dev         # build + watchers
cp includes/.env.example includes/.env
# edita DB_HOST, DB_USER, DB_PASSWORD, COTIZACIONES_URL y PORTAL_FOTOS_SECRETO
npm run serve       # http://localhost:8000
```

El sitio necesita el servicio de cotizaciones disponible para servir imágenes y fotos:

```bash
# en otra terminal, desde el repo de cotizaciones
php -S localhost:8001 -t .
```

Con `COTIZACIONES_URL=http://127.0.0.1:8001` el proxy de imágenes funciona en local.

Para probar el portal, asigna `email` y `portal_contrasena` (bcrypt) a un cliente en cotizaciones. El cliente verá únicamente sus compras de `EMPRESA_ID=2`.

## Datos en cotizaciones

El sitio público sólo muestra productos con:

- `productos.empresa_id = 2` (Canastillas de la Baja).
- `productos.activo = 1`.
- `productos.publicado_web = 1`.

El catálogo actual de Canastillas son **C-1, C-3 y RA-7**; la C-2 anterior ya no forma parte del sitio. Los productos de prueba (`EMP-B-*`) deben quedar fuera del catálogo. Las descripciones se capturan en cotizaciones con el formato:

```text
Canastilla pasajuntas C-1 de 5 varillas.
Dimensión: 1.5 m lineales. Pasajunta: redondo 3/4", 43 cm. Separación: 30 cm. Pavimento: 15 a 18 cm.
Tipo de uso: vialidades con tráfico vehicular.
```

Si una descripción todavía no tiene ese formato, el sitio usa una ficha de respaldo por SKU desde `data/productos_fallback.php`.

## Imágenes y fotos

- Las imágenes de producto viven en el volumen `public/image` de cotizaciones. El sitio no las copia: las pide por el servicio interno y las cachea en `/tmp/cdb-img`.
- Las fotos de entrega se piden firmadas con `PORTAL_FOTOS_SECRETO` (HMAC) al endpoint interno `/portal/entregas/foto` en modo `galeria`. La foto sale con logo, sin fecha, sin ubicación del cliente y sin EXIF.
- El navegador nunca ve la URL interna de cotizaciones.

## Portal de clientes

El login usa `clientes.email` + `clientes.portal_contrasena` (bcrypt) de cotizaciones:

- Sesión con cookie httponly/SameSite, CSRF y límite de intentos.
- `/mi-cuenta/compras` lista las compras del cliente con estatus **En proceso**, **Entregada** o **Cancelada**.
- `/mi-cuenta/compras/{id}` muestra productos, importes, entregas, fotos y acceso al acta.
- `/mi-cuenta/entregas/{id}/acta` genera el acta imprimible con los productos entregados, fotos, ubicación, notas y firma, con el mismo lenguaje visual del acta de cotizaciones.
- Las fotos se sirven por `/mi-cuenta/fotos/{id}` y la firma por `/mi-cuenta/entregas/{id}/firma`, siempre verificando que pertenezcan a una compra del cliente.

Para dar acceso a un cliente, en cotizaciones se capturan `clientes.email` y `clientes.portal_contrasena` (hash bcrypt). El portal es **solo de consulta**: no crea pedidos, no cancela compras y no repite pedidos.

## Configuración

`includes/.env` (o variables de entorno reales en Kubernetes):

```env
APP_ENV=produccion
DB_HOST=mysql.cotizaciones.svc.cluster.local
DB_PORT=3306
DB_NAME=cotizaciones
DB_USER=portal_canastillas
DB_PASSWORD=...
EMPRESA_ID=2
COTIZACIONES_URL=http://cotizaciones.cotizaciones.svc.cluster.local
PORTAL_FOTOS_SECRETO=...
```

El usuario MySQL `portal_canastillas` necesita lectura sobre productos, categorías, ventas, venta_detalles, ingresos, cobros, entregas, entrega_renglones y fotos. Se puede crear con el script de cotizaciones (incluye permisos de portal que este sitio todavía no usa):

```bash
PORTAL_DB_USER=portal_canastillas \
PORTAL_DB_PASSWORD='...' \
scripts/crear_usuario_portal.sh cotizaciones
```

## Despliegue

```bash
cp deployment/values.local.yaml.example deployment/values.local.yaml
# llena dbPassword y portalFotosSecreto
./deploy.sh
```

El chart:

- Usa `EMPRESA_ID=2`.
- Se conecta a la base de cotizaciones.
- No crea MySQL ni PVC de imágenes.
- Guarda las contraseñas en un Secret.
- Publica en `canastillas.iguzman.com.mx`, `canastillasdelabaja.com` y `www.canastillasdelabaja.com`.
- Mantiene una sola réplica: las sesiones y el límite de intentos viven en el disco del pod.

## Pendiente

- Cambio de contraseña desde el portal (opcional).
- Ajustar en cotizaciones las descripciones e imágenes de los productos.
