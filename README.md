# 🎓 Sistema Académico UPEA — Plataforma de Gestión Académica Universitaria

Plataforma integral de gestión universitaria desarrollada con **Laravel 11**, **Inertia.js v2**, **Vue 3**, **PrimeVue** y **Tailwind CSS**. Administra el ciclo de vida académico completo: admisiones, pre-requisitos de malla curricular, inscripciones regulares y extemporáneas, asignación docente de paralelos, registro de evaluaciones parciales y auditoría de kárdex con control de acceso basado en roles (**RBAC** con Spatie Permissions).

---

## 📋 Requisitos Previos

Antes de configurar el entorno de desarrollo, asegúrate de contar con:

- **PHP**: Versión `8.2` u `8.3` (con extensiones activas: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `xml`, `bcmath`).
- **Composer**: Versión `2.x` o superior.
- **Node.js**: Versión `v18.x` o `v20.x` LTS.
- **npm**: Versión `9.x` o superior.
- **MySQL / MariaDB**: Versión `8.0+` o MariaDB `10.4+` (a través de XAMPP, Laragon, Docker o servicio local en el puerto `3306`).

---

## 🚀 Paso a Paso: Configuración para Modo Desarrollador

Sigue este procedimiento para instalar y poner en marcha el proyecto:

### 1. Ubicarse en la carpeta del proyecto
Abre tu terminal en la raíz del proyecto:
```bash
cd d:/TRABAJO/sistema-academico
```

### 2. Instalar dependencias de PHP con Composer
```bash
composer install
```

### 3. Instalar dependencias de Frontend con npm
```bash
npm install
```

### 4. Configurar el archivo de Variables de Entorno (`.env`)
Si aún no existe el archivo `.env`, créalo a partir de la plantilla `.env.example`:

En Windows (PowerShell):
```powershell
Copy-Item .env.example .env
```
O en Bash / CMD:
```bash
cp .env.example .env
```

Genera la clave criptográfica única de la aplicación Laravel:
```bash
php artisan key:generate
```

### 5. Configurar la Conexión a la Base de Datos en `.env`
Abre el archivo `.env` y define la conexión a tu servidor MySQL local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

> [!NOTE]
> - Asegúrate de que la base de datos `laravel` (o el nombre que asignes en `DB_DATABASE`) exista en tu MySQL (puedes crearla con `CREATE DATABASE laravel;` en phpMyAdmin o MySQL CLI).
> - Si usas XAMPP estándar, el usuario es `root` y la contraseña se deja vacía (`DB_PASSWORD=`).

---

## 🗄️ Migraciones y Poblado de Datos (Seeders)

Para construir la estructura de tablas y cargar los datos maestros de prueba (roles, materias, docentes, cursos, calificaciones y cuentas demo):

### Opción Recomendada: Migrar y Poblar en un solo comando
```bash
php artisan migrate:fresh --seed
```

### O ejecutar paso por paso:
1. **Ejecutar migraciones de la base de datos:**
   ```bash
   php artisan migrate
   ```
2. **Ejecutar semilleros de datos (Seeders):**
   ```bash
   php artisan db:seed
   ```

El seeder inicializa automáticamente:
- **Roles y Permisos**: Administrador, Director, Encargado de Kardex, Comisión Académica, Docente, Estudiante.
- **Carreras**: Ingeniería de Sistemas (`SIS`), Informática (`INF`), Telecomunicaciones (`TEL`).
- **Malla Curricular**: Niveles semestrales y asignaturas con dependencias de pre-requisitos.
- **Cursos y Paralelos**: Gestión actual `2026-I`.
- **Periodos de Evaluación**: 1er Parcial, 2do Parcial, Examen Final, 2da Instancia.
- **Cuentas de Demostración**: Usuarios con historial académico y actas de notas listas para interactuar.

---

## 💻 ¿Cómo Ejecutar el Entorno de Trabajo en Desarrollo?

El sistema requiere tanto el backend de Laravel (PHP) como el compilador de componentes Vue (Vite).

### Opción 1: Comando Concurrente (Recomendado)
Laravel 11 incluye soporte para ejecutar el servidor PHP y Vite al mismo tiempo en una sola terminal:
```bash
composer run dev
```

### Opción 2: En Dos Terminales Separadas
Si prefieres ver los logs de cada proceso de manera independiente:

- **Terminal 1 — Servidor Backend Laravel:**
  ```bash
  php artisan serve
  ```
  *Disponible en: [http://127.0.0.1:8000](http://127.0.0.1:8000)*

- **Terminal 2 — Compilador Frontend Vite (Inertia + Vue 3):**
  ```bash
  npm run dev
  ```
  *Servidor de desarrollo Vite con Hot Module Replacement (HMR) en: [http://localhost:5173](http://localhost:5173)*

---

## 🔑 Cuentas de Acceso de Demostración (Seeders)

Todas las cuentas demo utilizan la contraseña predeterminada: `password`

| Rol | Correo Electrónico | CI | Acceso y Permisos |
| :--- | :--- | :--- | :--- |
| **Super Administrador** | `admin@academico.test` | `10000001` | Acceso total a todas las configuraciones y roles |
| **Director de Carrera** | `director@academico.test` | `10000002` | Malla curricular, cursos, admisión y asignación docente |
| **Directora Informática**| `director.informatica@academico.test` | `10000003` | Gestión de carrera Informática |
| **Encargado de Kardex** | `kardex@academico.test` | `10000004` | Inscripción manual, certificados y kárdex |
| **Docente Demo** | `docente1@academico.test` | `20009999` | Calificación de actas y listas de estudiantes |
| **Estudiante Demo** | `estudiante1@academico.test` | `30009999` | Historial de materias, kárdex y notas parciales |

---

## 🧪 Pruebas, Formato y Construcción

```bash
# Ejecutar suite de pruebas con PHPUnit
php artisan test

# Dar formato al código PHP respetando estándares Laravel Pint
vendor/bin/pint

# Compilar assets frontend para producción
npm run build
```

---

## 🔗 Integración con el Ecosistema UPEA

Este sistema académico se complementa con:
1. **`upea-token-service`** (Puerto `3000`): Microservicio NestJS que valida tokens de seguridad OTP para acciones críticas y firmas de actas.
2. **`upea-token-app`** (Puerto Web `8081`): Aplicación React Native móvil/web para generación de tokens dinámicos con Device Binding.
