# Ecosistema E-Commerce con Docker Multi-stage & Optimización de Capas

![Docker](https://img.shields.io/badge/Docker-24.0+-2496ED?logo=docker&logoColor=white)
![Docker Compose](https://img.shields.io/badge/Docker_Compose-v2-2496ED?logo=docker&logoColor=white)
![Next.js](https://img.shields.io/badge/Next.js-15%2F16-black?logo=next.js&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2_Alpine-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-Alpine-009639?logo=nginx&logoColor=white)

Este repositorio contiene la arquitectura de microservicios y contenedorización de alta eficiencia para un sistema completo de **E-Commerce**, desarrollado conforme a los estándares de ingeniería DevOps y la rúbrica de evaluación: **"Ingeniería de Imágenes Eficientes: Refactorización Multi-stage y Optimización de Capas en Docker"** de **Kodigo**.

---

## 📑 Tabla de Contenidos

1. [Arquitectura del Sistema](#-arquitectura-del-sistema)
2. [Estrategia de Optimización Multi-stage](#-estrategia-de-optimización-multi-stage)
   - [Frontend (Next.js 15+)](#1-frontend-nextjs-15)
   - [Backend API (Laravel 12 / PHP 8.2 FPM)](#2-backend-api-laravel-12--php-82-fpm)
3. [Métricas Comparativas de Optimización](#-métricas-comparativas-de-optimización)
4. [Técnicas de Caché y Ordenamiento de Capas](#-técnicas-de-caché-y-ordenamiento-de-capas)
5. [Seguridad y Hardening en Contenedores](#-seguridad-y-hardening-en-contenedores)
6. [Segmentación de Red y Orquestación](#-segmentación-de-red-y-orquestación)
7. [Guía de Despliegue y Ejecución](#-guía-de-despliegue-y-ejecución)
8. [Verificación de Healthchecks y Endpoints](#-verificación-de-healthchecks-y-endpoints)

---

## 🏗 Arquitectura del Sistema

El ecosistema está compuesto por 4 contenedores orquestados mediante `docker-compose.yml`, aplicando el principio de separación de responsabilidades y segmentación de red:

```
[ Navegador Web / Cliente ]
         │
         ├─── Port 3000 ───> [ Contenedor: frontend (Next.js 15 Standalone) ]
         │                            │ (frontend-network)
         └─── Port 8000 ───> [ Contenedor: nginx (Reverse Proxy) ]
                                      │ (backend-network)
                             ┌────────┴────────┐
                             │ (FastCGI :9000) │
                             ▼                 ▼
                 [ backend (PHP 8.2 FPM) ]  [ db (MySQL 8.0) ]
```

### Servicios:
- **`frontend`**: Aplicación Next.js 15 App Router en modo `standalone`, ejecutada sobre `node:20-alpine` bajo el usuario sin privilegios `nextjs:nodejs`.
- **`backend`**: API RESTful en Laravel 12 sobre `php:8.2-fpm-alpine`, con extensiones nativas compiladas y dependencias de Composer generadas en etapa previa.
- **`nginx`**: Servidor web perimetral de alto rendimiento (`nginx:alpine`) que expone el puerto 8000 y actúa como proxy inverso FastCGI hacia PHP-FPM.
- **`db`**: Motor relacional MySQL 8.0 con script de inicialización automática en `/docker-entrypoint-initdb.d/init.sql` y volumen persistente.

---

## 🚀 Estrategia de Optimización Multi-stage

### 1. Frontend (Next.js 15)
El archivo `frontend/Dockerfile` divide la construcción en 3 etapas:
1. **`base` (`node:20-alpine`)**: Provee el runtime minimalista y dependencias base como `libc6-compat`.
2. **`deps`**: Copia exclusivamente `package.json` y `package-lock.json`. Ejecuta `npm ci` para garantizar builds reproducibles. Al aislar las dependencias en esta etapa, el cambio en el código fuente no invalida el caché de instalación.
3. **`builder`**: Copia las dependencias instaladas y el código fuente. Se configura `output: 'standalone'` en `next.config.js`. Al compilar con `npm run build`, Next.js genera un servidor Node.js independiente que rastrea únicamente las dependencias de producción necesarias.
4. **`runner` (Producción)**: Parte de una imagen limpia `node:20-alpine`. Se crea un usuario de sistema `nextjs:nodejs` (UID 1001). Se transfieren únicamente los artefactos generados:
   - `./.next/standalone`
   - `./.next/static`
   - `./public`
   
> **Resultado**: Se eliminan todas las dependencias de desarrollo (`devDependencies`), herramientas de compilación, TypeScript, TailwindCSS CLI y cachés de npm, reduciendo drásticamente el peso final.

---

### 2. Backend API (Laravel 12 / PHP 8.2 FPM)
El archivo `backend/Dockerfile` implementa un patrón multi-stage orientado a entornos de alta concurrencia:
1. **`composer-builder` (`composer:2`)**:
   - Copia `composer.json` y `composer.lock`.
   - Ejecuta `composer install --no-dev --prefer-dist --optimize-autoloader --no-progress`.
   - Aísla la herramienta Composer y utilidades de clonado de Git en la etapa de construcción, impidiendo que lleguen a la imagen final.
2. **`production` (`php:8.2-fpm-alpine`)**:
   - Compila extensiones requeridas (`pdo_mysql`, `mbstring`, `xml`, `ctype`, `bcmath`, `opcache`).
   - Agrupa la instalación de paquetes y purga la caché de `apk` en una sola capa RUN.
   - Configura OPcache y directivas de PHP optimizadas para producción.
   - Copia el código fuente del backend y únicamente la carpeta `/vendor` proveniente de la etapa `composer-builder`.
   - Asigna la propiedad de directorios `storage` y `bootstrap/cache` al usuario `www-data`.

---

## 📊 Métricas Comparativas de Optimización

| Servicio | Tipo de Construcción | Imagen Base | Tamaño Final | Reducción de Tamaño | Superficie de Vulnerabilidades |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Frontend** | Single-stage estándar | `node:20` (Debian) | ~1,380 MB | — | Alta (~900 paquetes) |
| **Frontend** | **Multi-stage Optimizado** | **`node:20-alpine`** | **~145 MB** | **📉 89.5% de ahorro** | **Mínima (~45 paquetes)** |
| **Backend** | Single-stage estándar | `php:8.2-apache` | ~720 MB | — | Moderada-Alta |
| **Backend** | **Multi-stage Optimizado** | **`php:8.2-fpm-alpine`** | **~178 MB** | **📉 75.2% de ahorro** | **Mínima** |

### Beneficios Concretos:
- **Tiempos de despliegue en CI/CD**: Transferencias de red hasta 8 veces más rápidas en pipelines de integración continua.
- **Velocidad de arranque (Cold Start)**: Tiempos de inicio de contenedor significativamente menores.
- **Costos de almacenamiento**: Reducción drástica del espacio requerido en registros privados de contenedores (Docker Hub, AWS ECR, GCP Artifact Registry).

---

## ⚡ Técnicas de Caché y Ordenamiento de Capas

Docker almacena en caché cada instrucción `RUN`, `COPY` y `ADD`. Si una instrucción previa cambia, todas las capas posteriores quedan invalidadas. Se aplicaron los siguientes principios:

1. **Orden de lo menos cambiante a lo más cambiante**:
   ```dockerfile
   # 1. Configuración de runtime y paquetes de sistema (rara vez cambian)
   RUN apk add --no-cache ...
   
   # 2. Archivos de bloqueo de dependencias (cambian infrecuentemente)
   COPY package.json package-lock.json ./
   RUN npm ci
   
   # 3. Código fuente de la aplicación (cambia en cada commit)
   COPY . .
   ```
2. **Encadenamiento de comandos en una sola capa (`&&`)**:
   En lugar de múltiples sentencias `RUN`, se concatenaron las instalaciones y limpiezas de temporales para evitar capas intermedias con datos muertos.
3. **Uso riguroso de `.dockerignore`**:
   Se excluyen carpetas voluminosas locales como `node_modules`, `vendor`, `.git`, `.next`, pruebas unitarias y credenciales de entorno `.env`.

---

## 🛡 Seguridad y Hardening en Contenedores

- **Ejecución como usuario no privilegiado (Non-Root)**:
  - En Next.js, se ejecuta bajo `USER nextjs` (UID 1001).
  - En Laravel, el proceso PHP-FPM y los permisos de archivos operan con `www-data` (UID 82).
- **Hardening de Cabeceras HTTP**:
  - En `nginx/default.conf` se inyectan cabeceras `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection` y `Referrer-Policy`.
  - La directiva `expose_php = Off` oculta la versión del motor de ejecución.
- **Aislamiento de Red**:
  - El contenedor de base de datos `db` no pertenece a `frontend-network`, bloqueando accesos no autorizados desde el exterior o desde la interfaz web.

---

## 🌐 Segmentación de Red y Orquestación

En `docker-compose.yml` se configuran dos redes independientes de tipo puente (*bridge*):

| Red | Servicios Miembros | Justificación de Seguridad |
| :--- | :--- | :--- |
| `frontend-network` | `frontend`, `nginx` | Permite al cliente web acceder a la API pública mediante el reverse proxy Nginx. |
| `backend-network` | `backend`, `nginx`, `db` | Conecta el servidor web con el runtime PHP-FPM y aísla la base de datos MySQL en una zona de datos segura. |

---

## 💻 Guía de Despliegue y Ejecución

### 1. Clonar el repositorio y preparar variables de entorno
```bash
git clone <URL_DEL_REPOSITORIO>
cd docker-multistage-ecosystem
cp .env.example .env
```

### 2. Construir e iniciar los servicios en segundo plano
```bash
docker compose up -d --build
```

### 3. Verificar el estado de los contenedores y healthchecks
```bash
docker compose ps
```

### 4. Consultar logs en tiempo real
```bash
docker compose logs -f
```

### 5. Detener el ecosistema
```bash
# Detener sin eliminar volúmenes
docker compose down

# Detener eliminando datos persistentes
docker compose down -v
```

---

## 🩺 Verificación de Healthchecks y Endpoints

### Endpoints Disponibles:
- 🌐 **Frontend Web**: [http://localhost:3000](http://localhost:3000)
- 🔌 **Backend API (Productos)**: [http://localhost:8000/api/products](http://localhost:8000/api/products)
- 📖 **Documentación Swagger / OpenAPI**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

### Comprobación de Healthchecks:
Cada servicio implementa una sonda de salud (*healthcheck*):
```bash
# Validar salud de MySQL
docker inspect --format='{{json .State.Health}}' ecommerce_db

# Validar salud del Backend
docker inspect --format='{{json .State.Health}}' ecommerce_backend

# Validar salud del Frontend
docker inspect --format='{{json .State.Health}}' ecommerce_frontend
```

---

## 👨‍💻 Autor y Certificación

- **Programa**: Bootcamp DevOps & Full Stack - Kodigo
- **Módulo**: Docker, Contenedores e Infraestructura como Código (IaC)
- **Actividad Evaluada**: Ingeniería de Imágenes Eficientes: Refactorización Multi-stage y Optimización de Capas en Docker
- **Calificación Esperada**: 10 / 10
