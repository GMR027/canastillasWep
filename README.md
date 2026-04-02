# Canastillas de la Baja — Web Application

> **[English version below](#english-version)**

---

## Descripción del Proyecto

**Canastillas de la Baja** es una aplicación web B2B desarrollada en PHP para una empresa fabricante de productos metálicos para construcción de carreteras con sede en La Paz, Baja California Sur. Los productos principales son canastillas y marcos de concreto (C-1, C-2, Marco y contramarco) que se distribuyen en cinco ciudades de Baja California Sur.

La plataforma permite:
- Exhibir el catálogo de productos al público general.
- Gestionar pedidos y su seguimiento desde creación hasta entrega.
- Generar reportes de entrega con fotografías y ubicación.
- Administrar usuarios con tres roles diferenciados (Administrador, Cliente, Proveedor).

---

## Arquitectura General

```
┌─────────────────────────────────────────────────────┐
│                   Navegador / Browser               │
└────────────────────────┬────────────────────────────┘
                         │ HTTPS
                         ▼
┌─────────────────────────────────────────────────────┐
│          Ingress (canastillas.iguzman.com.mx)        │
│              cert-manager / Let's Encrypt            │
└────────────────────────┬────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────┐
│        PHP App — Kubernetes Deployment (x3)          │
│  Apache + PHP 8  │  ConfigMap con credenciales DB    │
│  /var/www/public/image  ←→  hostPath /shared-master  │
└────────────────────────┬────────────────────────────┘
                         │ mysql.canastillas.svc.cluster.local:3306
                         ▼
┌─────────────────────────────────────────────────────┐
│           MySQL 8 — Kubernetes Deployment (x1)       │
│    DB: canastillas   │   User: canastillas            │
│    hostPath /shared-master → init + data volumes     │
└─────────────────────────────────────────────────────┘
```

### Estructura de la Aplicación

```
canastillasWep/
├── index.php                  # Página pública principal
├── login.php                  # Autenticación
├── cerrar-sesion.php          # Cierre de sesión
├── config/
│   └── database.php           # Conexión a base de datos (env vars)
├── includes/
│   ├── app.php                # Inicialización y autoload
│   └── funciones.php          # Helpers: plantillas, auth, escape
├── clases/                    # Lógica de negocio
│   ├── Usuarios.php           # CRUD de usuarios (3 roles)
│   ├── Productos.php          # Catálogo de productos
│   ├── Pedidos.php            # Pedidos y seguimiento
│   └── Reportes.php           # Reportes de entrega con foto
├── admin/                     # Dashboard de administrador (rol 1)
│   ├── pedidos/
│   ├── productos/
│   ├── usuarios/
│   └── reportes/
├── public/                    # Dashboards de cliente y proveedor
│   ├── cliente/               # Vista de cliente (rol 2)
│   ├── proveedor/             # Vista de proveedor (rol 3)
│   └── image/                 # Imágenes subidas (montado en k8s)
├── templates/                 # Plantillas HTML reutilizables
├── src/                       # Fuentes frontend (SCSS, JS, img)
├── build/                     # CSS compilado (Gulp + Sass)
└── deployment/                # Helm charts para Kubernetes
    ├── Chart.yaml
    ├── values.yaml
    ├── templates/             # Deployment, Service, Ingress, ConfigMap
    └── my-sql/                # Chart de MySQL
```

### Roles de Usuario

| Rol | Descripción | Redirige a |
|-----|-------------|------------|
| 1 — Administrador | Acceso completo: pedidos, productos, usuarios, reportes | `/admin/` |
| 2 — Cliente | Consulta sus pedidos y detalle de reportes | `/public/cliente/` |
| 3 — Proveedor | Vista de sus entregas | `/public/proveedor/` |

---

## Desarrollo Local

### Requisitos

- PHP 8+
- MySQL 8
- Composer
- Node.js + npm

### Instalación

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Instalar dependencias de frontend
npm install

# 3. Compilar assets (CSS)
npm run build

# 4. Crear la base de datos
mysql -u root -p -e "CREATE DATABASE canastillas;"
# Importar el schema si existe en mysql/

# 5. Configurar variables de entorno (opcional, hay fallbacks)
export DB_HOST=localhost
export DB_USER=root
export DB_PASSWORD=
export DB_NAME=canastillas

# 6. Servir con PHP o Apache apuntando a la raíz del proyecto
php -S localhost:8000
```

---

## Despliegue en Kubernetes (Helm)

### Prerrequisitos

- Clúster de Kubernetes con acceso a `kubectl` y `helm`
- Namespace `canastillas` creado
- Nodo con label `nodeProjects=real-clients` para el app
- Nodo con label `nodeName=master` para MySQL
- cert-manager instalado con ClusterIssuer `letsencrypt-prod`
- Volumen compartido en `/shared-master` en el nodo master (para imágenes y datos de MySQL)
- Imagen Docker publicada en `docker.io/christopherguzman/canastillas:latest`

### Crear el namespace

```bash
kubectl create namespace canastillas
```

### Desplegar MySQL

```bash
helm install mysql ./deployment/my-sql \
  --namespace canastillas
```

Esperar a que MySQL esté listo:

```bash
kubectl rollout status deployment/mysql -n canastillas
```

### Desplegar la aplicación

```bash
helm install canastillas ./deployment \
  --namespace canastillas
```

### Verificar el despliegue

```bash
kubectl get pods -n canastillas
kubectl get ingress -n canastillas
```

### Actualizar la aplicación

```bash
# Construir y publicar nueva imagen
docker build -t christopherguzman/canastillas:latest .
docker push christopherguzman/canastillas:latest

# Actualizar el chart
helm upgrade canastillas ./deployment --namespace canastillas

# O forzar restart para jalar latest
kubectl rollout restart deployment/canastillas -n canastillas
```

### Build y despliegue en un solo comando

```bash
docker build -t christopherguzman/canastillas:latest . && docker push christopherguzman/canastillas:latest && helm uninstall canastillas -n canastillas && helm install canastillas ./deployment --namespace canastillas
```

### Desinstalar

```bash
helm uninstall canastillas -n canastillas
helm uninstall mysql -n canastillas
```

---

---

# English Version

## Project Description

**Canastillas de la Baja** is a B2B web application built in PHP for a metalwork manufacturing company based in La Paz, Baja California Sur, Mexico. The company produces concrete forms and frames (C-1, C-2, Marco y contramarco) distributed across five cities in Baja California Sur.

The platform provides:
- A public-facing product catalog.
- Order management and tracking from creation to delivery.
- Delivery reports with photos and location data.
- User management with three distinct roles (Administrator, Client, Provider).

---

## Overall Architecture

```
┌─────────────────────────────────────────────────────┐
│                   Browser / Navegador               │
└────────────────────────┬────────────────────────────┘
                         │ HTTPS
                         ▼
┌─────────────────────────────────────────────────────┐
│          Ingress (canastillas.iguzman.com.mx)        │
│              cert-manager / Let's Encrypt            │
└────────────────────────┬────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────┐
│        PHP App — Kubernetes Deployment (x3)          │
│  Apache + PHP 8  │  ConfigMap with DB credentials   │
│  /var/www/public/image  ←→  hostPath /shared-master  │
└────────────────────────┬────────────────────────────┘
                         │ mysql.canastillas.svc.cluster.local:3306
                         ▼
┌─────────────────────────────────────────────────────┐
│           MySQL 8 — Kubernetes Deployment (x1)       │
│    DB: canastillas   │   User: canastillas            │
│    hostPath /shared-master → init + data volumes     │
└─────────────────────────────────────────────────────┘
```

### Application Structure

```
canastillasWep/
├── index.php                  # Public homepage
├── login.php                  # Authentication
├── cerrar-sesion.php          # Logout handler
├── config/
│   └── database.php           # DB connection (reads from env vars)
├── includes/
│   ├── app.php                # Bootstrap and autoload
│   └── funciones.php          # Helpers: templates, auth, escaping
├── clases/                    # Business logic
│   ├── Usuarios.php           # User CRUD (3 roles)
│   ├── Productos.php          # Product catalog
│   ├── Pedidos.php            # Orders and tracking
│   └── Reportes.php           # Delivery reports with photos
├── admin/                     # Administrator dashboard (role 1)
│   ├── pedidos/
│   ├── productos/
│   ├── usuarios/
│   └── reportes/
├── public/                    # Client and provider dashboards
│   ├── cliente/               # Client view (role 2)
│   ├── proveedor/             # Provider view (role 3)
│   └── image/                 # Uploaded images (mounted in k8s)
├── templates/                 # Reusable HTML templates
├── src/                       # Frontend sources (SCSS, JS, images)
├── build/                     # Compiled CSS (Gulp + Sass)
└── deployment/                # Helm charts for Kubernetes
    ├── Chart.yaml
    ├── values.yaml
    ├── templates/             # Deployment, Service, Ingress, ConfigMap
    └── my-sql/                # MySQL Helm chart
```

### User Roles

| Role | Description | Redirects to |
|------|-------------|--------------|
| 1 — Administrator | Full access: orders, products, users, reports | `/admin/` |
| 2 — Client | View own orders and delivery report details | `/public/cliente/` |
| 3 — Provider | View own deliveries | `/public/proveedor/` |

---

## Local Development

### Requirements

- PHP 8+
- MySQL 8
- Composer
- Node.js + npm

### Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install frontend dependencies
npm install

# 3. Compile assets (CSS)
npm run build

# 4. Create the database
mysql -u root -p -e "CREATE DATABASE canastillas;"
# Import schema if available in mysql/

# 5. Set environment variables (optional — fallbacks are set in config/database.php)
export DB_HOST=localhost
export DB_USER=root
export DB_PASSWORD=
export DB_NAME=canastillas

# 6. Serve with PHP or Apache pointing to the project root
php -S localhost:8000
```

---

## Kubernetes Deployment (Helm)

### Prerequisites

- Kubernetes cluster with `kubectl` and `helm` access
- Namespace `canastillas` created
- A node labeled `nodeProjects=real-clients` for the app pods
- A node labeled `nodeName=master` for MySQL
- cert-manager installed with a `letsencrypt-prod` ClusterIssuer
- Shared volume available at `/shared-master` on the master node (for images and MySQL data)
- Docker image published to `docker.io/christopherguzman/canastillas:latest`

### Create the namespace

```bash
kubectl create namespace canastillas
```

### Deploy MySQL

```bash
helm install mysql ./deployment/my-sql \
  --namespace canastillas
```

Wait for MySQL to be ready:

```bash
kubectl rollout status deployment/mysql -n canastillas
```

### Deploy the application

```bash
helm install canastillas ./deployment \
  --namespace canastillas
```

### Verify the deployment

```bash
kubectl get pods -n canastillas
kubectl get ingress -n canastillas
```

### Update the application

```bash
# Build and push a new image
docker build -t christopherguzman/canastillas:latest .
docker push christopherguzman/canastillas:latest

# Upgrade the Helm release
helm upgrade canastillas ./deployment --namespace canastillas

# Or force a rolling restart to pull the latest image
kubectl rollout restart deployment/canastillas -n canastillas
```

### Build and deploy in one command

```bash
docker build -t christopherguzman/canastillas:latest . && docker push christopherguzman/canastillas:latest && helm uninstall canastillas -n canastillas && helm install canastillas ./deployment --namespace canastillas
```

### Uninstall

```bash
helm uninstall canastillas -n canastillas
helm uninstall mysql -n canastillas
```
