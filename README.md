<p align="center">
  <img src="https://img.shields.io/badge/UNSAAC-Ingeniería%20Informática-9C221C?style=for-the-badge" alt="UNSAAC"/>
  <img src="https://img.shields.io/badge/Desarrollo%20de%20Software%20I-2025--2-1A1A2E?style=for-the-badge" alt="Curso"/>
</p>

<h1 align="center">🏙️ Plataforma de Denuncia Ciudadana</h1>
<h3 align="center">Sistema Web para Reportar Problemas Urbanos</h3>

<p align="center">
  <img src="https://img.shields.io/badge/React-18.2-61DAFB?style=flat-square&logo=react" alt="React"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php" alt="PHP"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/TailwindCSS-3.4-06B6D4?style=flat-square&logo=tailwindcss" alt="Tailwind"/>
  <img src="https://img.shields.io/badge/XAMPP-8.2-FB7A24?style=flat-square&logo=xampp" alt="XAMPP"/>
</p>

<p align="center">
  <strong>ODS 11:</strong> Ciudades Sostenibles | <strong>ODS 16:</strong> Instituciones Sólidas
</p>

---

## 📋 Tabla de Contenidos

- [🎯 Descripción del Proyecto](#-descripción-del-proyecto)
- [🏗️ Arquitectura del Sistema](#️-arquitectura-del-sistema)
- [🛠️ Stack Tecnológico](#️-stack-tecnológico)
- [🎨 Paleta de Colores](#-paleta-de-colores)
- [📁 Estructura del Proyecto](#-estructura-del-proyecto)
- [⚙️ Configuración del Entorno](#️-configuración-del-entorno)
- [🗄️ Base de Datos](#️-base-de-datos)
- [📅 Cronograma de Desarrollo](#-cronograma-de-desarrollo)
- [🚀 Fases de Implementación](#-fases-de-implementación)
- [🔐 Seguridad](#-seguridad)
- [🗺️ Integración de Mapas](#️-integración-de-mapas)
- [✅ Testing](#-testing)
- [👥 Equipo](#-equipo)

---

## 🎯 Descripción del Proyecto

La resolución de problemas urbanos como **baches**, **falta de alumbrado público** o **acumulación de basura** suele ser lenta debido a la falta de comunicación directa entre la ciudadanía y las autoridades.

Esta plataforma permite:

| Funcionalidad | Descripción |
|---------------|-------------|
| 📝 **Registro de Denuncias** | Ciudadanos reportan problemas con fotos y ubicación GPS |
| 📍 **Geolocalización** | Mapa interactivo para ubicar exactamente el problema |
| 📊 **Seguimiento** | Tracking del estado de cada denuncia en tiempo real |
| 📈 **Dashboard** | Panel de control para autoridades municipales |
| 📉 **Estadísticas** | Métricas de incidencias resueltas y pendientes |

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                      CAPA DE PRESENTACIÓN                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │  React.js   │  │ TailwindCSS │  │   Leaflet + OpenStreet  │  │
│  │  + Vite     │  │  (#9C221C)  │  │        Map              │  │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘  │
└───────────────────────────┬─────────────────────────────────────┘
                            │ HTTP/HTTPS (API REST - JSON)
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                       CAPA DE NEGOCIO                           │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                PHP 8.2 API REST (XAMPP)                  │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────────┐  │   │
│  │  │ Usuarios │ │Denuncias │ │ Archivos │ │Notificación │  │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └─────────────┘  │   │
│  └──────────────────────────────────────────────────────────┘   │
└───────────────────────────┬─────────────────────────────────────┘
                            │ PDO (MySQL Driver)
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        CAPA DE DATOS                            │
│  ┌─────────────────────────┐    ┌────────────────────────────┐  │
│  │   MySQL / MariaDB       │    │   Sistema de Archivos      │  │
│  │   (Datos + Índices)     │    │   (uploads/evidencias)     │  │
│  └─────────────────────────┘    └────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Stack Tecnológico

### Frontend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| ⚛️ **React.js** | 18.2 | Biblioteca UI con componentes |
| ⚡ **Vite** | 5.x | Build tool ultrarrápido |
| 🎨 **TailwindCSS** | 3.4 | Framework CSS utility-first |
| 🗺️ **React-Leaflet** | 4.x | Mapas interactivos |
| 📊 **Chart.js** | 4.x | Gráficos y visualizaciones |
| 🔄 **Axios** | 1.x | Cliente HTTP |
| 📝 **React Hook Form** | 7.x | Gestión de formularios |
| 🗃️ **Zustand** | 4.x | Estado global |

### Backend
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| 🐘 **PHP** | 8.2 | Lenguaje servidor |
| 🔐 **JWT** | - | Autenticación stateless |
| 📧 **PHPMailer** | 6.x | Envío de emails |
| 🎼 **Composer** | 2.x | Gestor de dependencias |

### Base de Datos & Servidor
| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| 🐬 **MySQL** | 8.0 | Base de datos relacional |
| 🦊 **XAMPP** | 8.2 | Entorno de desarrollo local |
| 🌐 **Apache** | 2.4 | Servidor web |

---

## 🎨 Paleta de Colores

<table>
  <tr>
    <td align="center">
      <img src="https://via.placeholder.com/80/9C221C/FFFFFF?text=+" alt="Primary"/><br/>
      <strong>Primary</strong><br/>
      <code>#9C221C</code><br/>
      <em>Botones, enlaces</em>
    </td>
    <td align="center">
      <img src="https://via.placeholder.com/80/7A1A16/FFFFFF?text=+" alt="Primary Dark"/><br/>
      <strong>Primary Dark</strong><br/>
      <code>#7A1A16</code><br/>
      <em>Hover, énfasis</em>
    </td>
    <td align="center">
      <img src="https://via.placeholder.com/80/FDF6F5/000000?text=+" alt="Primary Light"/><br/>
      <strong>Primary Light</strong><br/>
      <code>#FDF6F5</code><br/>
      <em>Fondos, cards</em>
    </td>
  </tr>
  <tr>
    <td align="center">
      <img src="https://via.placeholder.com/80/22C55E/FFFFFF?text=+" alt="Success"/><br/>
      <strong>Success</strong><br/>
      <code>#22C55E</code><br/>
      <em>Resuelto</em>
    </td>
    <td align="center">
      <img src="https://via.placeholder.com/80/F59E0B/FFFFFF?text=+" alt="Warning"/><br/>
      <strong>Warning</strong><br/>
      <code>#F59E0B</code><br/>
      <em>En proceso</em>
    </td>
    <td align="center">
      <img src="https://via.placeholder.com/80/EF4444/FFFFFF?text=+" alt="Danger"/><br/>
      <strong>Danger</strong><br/>
      <code>#EF4444</code><br/>
      <em>Errores</em>
    </td>
  </tr>
</table>

### Configuración Tailwind
```javascript
// tailwind.config.js
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#9C221C',
          dark: '#7A1A16',
          light: '#FDF6F5',
        },
      },
    },
  },
}
```

---

## 📁 Estructura del Proyecto

```
denuncia-ciudadana/
│
├── 📂 backend/                     # API PHP
│   ├── 📂 api/
│   │   ├── 📂 auth/               # Autenticación
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   └── verify.php
│   │   ├── 📂 denuncias/          # CRUD Denuncias
│   │   │   ├── create.php
│   │   │   ├── read.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   ├── 📂 archivos/           # Upload de evidencias
│   │   └── 📂 estadisticas/       # Reportes
│   │
│   ├── 📂 config/
│   │   ├── database.php           # Conexión PDO
│   │   └── cors.php               # Configuración CORS
│   │
│   ├── 📂 models/                 # Clases de entidades
│   ├── 📂 middleware/             # JWT, validaciones
│   ├── 📂 uploads/                # Archivos subidos
│   └── composer.json
│
├── 📂 frontend/                    # Aplicación React
│   ├── 📂 src/
│   │   ├── 📂 components/         # Componentes reutilizables
│   │   │   ├── MapSelector.jsx
│   │   │   ├── DenunciaCard.jsx
│   │   │   └── Navbar.jsx
│   │   ├── 📂 pages/              # Vistas principales
│   │   │   ├── Home.jsx
│   │   │   ├── Login.jsx
│   │   │   ├── Dashboard.jsx
│   │   │   └── NuevaDenuncia.jsx
│   │   ├── 📂 services/           # Llamadas API
│   │   ├── 📂 store/              # Estado global (Zustand)
│   │   ├── 📂 hooks/              # Custom hooks
│   │   └── App.jsx
│   │
│   ├── tailwind.config.js
│   ├── vite.config.js
│   └── package.json
│
├── 📂 database/
│   ├── schema.sql                 # Estructura de tablas
│   └── seed.sql                   # Datos iniciales
│
├── 📂 docs/                       # Documentación
└── README.md
```

---

## ⚙️ Configuración del Entorno

### Requisitos Previos

| Software | Versión | Enlace |
|----------|---------|--------|
| XAMPP | 8.2+ | [Descargar](https://www.apachefriends.org/) |
| Node.js | 18 LTS | [Descargar](https://nodejs.org/) |
| Git | Última | [Descargar](https://git-scm.com/) |
| Composer | 2.x | [Descargar](https://getcomposer.org/) |
| VS Code | Última | [Descargar](https://code.visualstudio.com/) |

### 🚀 Instalación Paso a Paso

#### 1️⃣ Configurar XAMPP
```bash
# Abrir XAMPP Control Panel
# Iniciar Apache ✅
# Iniciar MySQL ✅
# Verificar: http://localhost/phpmyadmin
```

#### 2️⃣ Clonar Repositorio
```bash
cd C:/xampp/htdocs
git clone https://github.com/[tu-usuario]/denuncia-ciudadana.git
cd denuncia-ciudadana
```

#### 3️⃣ Configurar Backend
```bash
cd backend
composer install
# Crear archivo .env con configuración de BD
```

#### 4️⃣ Configurar Base de Datos
```bash
# En phpMyAdmin:
# 1. Crear base de datos: denuncia_ciudadana
# 2. Importar: database/schema.sql
# 3. (Opcional) Importar: database/seed.sql
```

#### 5️⃣ Configurar Frontend
```bash
cd ../frontend
npm install
npm run dev
# Abrir: http://localhost:5173
```

---

## 🗄️ Base de Datos

### Diagrama Entidad-Relación

```
┌─────────────┐       ┌─────────────┐       ┌─────────────────┐
│  USUARIOS   │       │  DENUNCIAS  │       │   CATEGORIAS    │
├─────────────┤       ├─────────────┤       ├─────────────────┤
│ id          │◄──────│ usuario_id  │       │ id              │
│ dni         │       │ id          │───────│ nombre          │
│ nombres     │       │ categoria_id│──────►│ descripcion     │
│ apellidos   │       │ titulo      │       │ icono           │
│ email       │       │ descripcion │       └─────────────────┘
│ password    │       │ latitud     │
│ rol         │       │ longitud    │       ┌─────────────────┐
│ verificado  │       │ estado      │       │   EVIDENCIAS    │
└─────────────┘       │ codigo      │       ├─────────────────┤
                      │ created_at  │◄──────│ denuncia_id     │
┌─────────────┐       └─────────────┘       │ id              │
│    AREAS    │              │              │ archivo_url     │
├─────────────┤              │              │ tipo            │
│ id          │◄─────────────┤              └─────────────────┘
│ nombre      │              │
│ responsable │              ▼              ┌─────────────────┐
└─────────────┘       ┌─────────────┐       │ NOTIFICACIONES  │
                      │ SEGUIMIENTO │       ├─────────────────┤
                      ├─────────────┤       │ id              │
                      │ denuncia_id │       │ usuario_id      │
                      │ estado_ant  │       │ mensaje         │
                      │ estado_new  │       │ leida           │
                      │ comentario  │       │ created_at      │
                      │ usuario_id  │       └─────────────────┘
                      │ created_at  │
                      └─────────────┘
```

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Ciudadanos y autoridades municipales |
| `denuncias` | Registro principal con geolocalización |
| `categorias` | Tipos: baches, alumbrado, basura, etc. |
| `evidencias` | Fotos y videos adjuntos |
| `seguimientos` | Historial de cambios de estado |
| `areas_municipales` | Dependencias responsables |
| `notificaciones` | Emails enviados |

### Script SQL Principal
```sql
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS denuncia_ciudadana
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE denuncia_ciudadana;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(15),
    rol ENUM('ciudadano', 'operador', 'supervisor', 'admin') DEFAULT 'ciudadano',
    verificado BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_dni (dni)
);

-- Tabla de denuncias
CREATE TABLE denuncias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,  -- DU-2025-000001
    usuario_id INT,
    categoria_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    direccion_referencia TEXT,
    estado ENUM('registrada', 'en_revision', 'asignada', 'en_proceso', 'resuelta', 'cerrada', 'rechazada') DEFAULT 'registrada',
    area_asignada_id INT,
    es_anonima BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_estado (estado),
    INDEX idx_codigo (codigo)
);
```

---

## 📅 Cronograma de Desarrollo

### Vista General (9 Semanas)

```
SEMANA    1    2    3    4    5    6    7    8    9
          ▼    ▼    ▼    ▼    ▼    ▼    ▼    ▼    ▼
FASE 1    ████████                              ──► Fundación
FASE 2              ████████                    ──► Core Features  
FASE 3                        ████████          ──► Gestión Municipal
FASE 4                                  ██████  ──► Optimización & Deploy
```

### Detalle por Semana

| Semana | Fase | Actividades | Entregables |
|:------:|:----:|-------------|-------------|
| **1** | 🔧 Fundación | Config. entorno, diseño BD | Repo configurado |
| **2** | 🔧 Fundación | Auth JWT, registro/login | Sistema login funcional |
| **3** | ⚡ Core | Integración Leaflet, GPS | Mapa interactivo |
| **4** | ⚡ Core | CRUD denuncias, uploads | Módulo denuncias |
| **5** | 🏛️ Municipal | Dashboard, Chart.js | Panel admin |
| **6** | 🏛️ Municipal | Notificaciones, asignaciones | Sistema completo |
| **7** | 🚀 Optimización | Reportes, mapa de calor | Estadísticas |
| **8** | 🚀 Optimización | Testing, documentación | QA completado |
| **9** | 🚀 Deploy | Despliegue, exposición | **Entrega Final** |

---

## 🚀 Fases de Implementación

### FASE 1: Fundación (Semanas 1-2) 🔧

<table>
<tr>
<td width="50%">

**Objetivos:**
- ✅ Configurar entorno de desarrollo
- ✅ Diseñar base de datos completa
- ✅ Implementar autenticación JWT
- ✅ Crear estructura del proyecto

</td>
<td width="50%">

**Entregables:**
- 📄 Diagrama E-R
- 🔐 Sistema login/registro
- 📁 Repositorio GitHub
- 📋 Documentación inicial

</td>
</tr>
</table>

### FASE 2: Core Features (Semanas 3-4) ⚡

<table>
<tr>
<td width="50%">

**Objetivos:**
- ✅ Integrar mapas con Leaflet
- ✅ CRUD completo de denuncias
- ✅ Sistema de upload de archivos
- ✅ Generación de códigos únicos

</td>
<td width="50%">

**Entregables:**
- 🗺️ Mapa interactivo
- 📝 Formulario de denuncia
- 📎 Upload de evidencias
- 🔢 Códigos DU-YYYY-NNNNNN

</td>
</tr>
</table>

### FASE 3: Gestión Municipal (Semanas 5-6) 🏛️

<table>
<tr>
<td width="50%">

**Objetivos:**
- ✅ Dashboard para autoridades
- ✅ Sistema de asignaciones
- ✅ Notificaciones por email
- ✅ Portal de consulta pública

</td>
<td width="50%">

**Entregables:**
- 📊 Panel de control
- 📧 Emails automáticos
- 🔍 Consulta por código
- 📈 Gráficos con Chart.js

</td>
</tr>
</table>

### FASE 4: Optimización & Deploy (Semanas 7-9) 🚀

<table>
<tr>
<td width="50%">

**Objetivos:**
- ✅ Reportes estadísticos
- ✅ Mapa de calor de incidencias
- ✅ Testing completo
- ✅ Despliegue en la nube

</td>
<td width="50%">

**Entregables:**
- 📉 Reportes PDF
- 🔥 Heatmap de problemas
- ✅ Tests automatizados
- 🌐 App desplegada

</td>
</tr>
</table>

---

## 🔐 Seguridad

### Flujo de Autenticación JWT

```
┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
│ USUARIO  │      │ FRONTEND │      │ BACKEND  │      │   BD     │
└────┬─────┘      └────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │                 │
     │ 1. Login        │                 │                 │
     │────────────────►│                 │                 │
     │                 │ 2. POST /login  │                 │
     │                 │────────────────►│                 │
     │                 │                 │ 3. Verificar    │
     │                 │                 │────────────────►│
     │                 │                 │◄────────────────│
     │                 │                 │ 4. bcrypt check │
     │                 │ 5. JWT Token    │                 │
     │                 │◄────────────────│                 │
     │ 6. Guardar      │                 │                 │
     │◄────────────────│                 │                 │
     │                 │                 │                 │
     │ 7. Request +    │ 8. Authorization│                 │
     │    Token        │    Bearer token │                 │
     │────────────────►│────────────────►│                 │
     │                 │                 │ 9. Validar JWT  │
     │                 │ 10. Response    │                 │
     │◄────────────────│◄────────────────│                 │
```

### Medidas Implementadas

| Medida | Implementación |
|--------|----------------|
| 🔒 **Cifrado contraseñas** | bcrypt con cost factor 12 |
| 💉 **SQL Injection** | PDO Prepared Statements |
| 🛡️ **XSS Prevention** | htmlspecialchars() + CSP |
| 🌐 **CORS** | Headers restrictivos |
| ⏱️ **Rate Limiting** | 5 intentos / 15 min |
| 🔑 **Sesiones** | JWT con expiración |

---

## 🗺️ Integración de Mapas

### Componente MapSelector

```jsx
// frontend/src/components/MapSelector.jsx
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import { useState } from 'react';

// Centro: Cusco, Perú
const CUSCO_CENTER = [-13.5319, -71.9675];

function LocationMarker({ position, setPosition }) {
  useMapEvents({
    click(e) {
      setPosition([e.latlng.lat, e.latlng.lng]);
    }
  });
  return position ? <Marker position={position} /> : null;
}

export default function MapSelector({ onLocationSelect }) {
  const [position, setPosition] = useState(null);

  const handlePosition = (pos) => {
    setPosition(pos);
    onLocationSelect({ lat: pos[0], lng: pos[1] });
  };

  return (
    <MapContainer 
      center={CUSCO_CENTER} 
      zoom={14} 
      className="h-96 w-full rounded-lg shadow-lg"
    >
      <TileLayer
        attribution='&copy; OpenStreetMap'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      <LocationMarker position={position} setPosition={handlePosition} />
    </MapContainer>
  );
}
```

---

## ✅ Testing

### Estrategia de Pruebas

| Tipo | Herramienta | Cobertura |
|------|-------------|-----------|
| 🧪 **Unitarias** | PHPUnit, Jest | Funciones críticas |
| 🔗 **Integración** | Postman | API endpoints |
| 🌐 **E2E** | Cypress | Flujos de usuario |
| ⚡ **Performance** | Lighthouse | Core Web Vitals |

### Criterios de Aceptación

- [ ] ⏱️ Tiempo de carga < 3 segundos
- [ ] 🚀 API response < 500ms
- [ ] 📊 Cobertura de código > 70%
- [ ] 🔒 Zero vulnerabilidades críticas
- [ ] 🌐 Compatible: Chrome, Firefox, Safari, Edge
- [ ] 📱 Responsivo desde 320px

---

## 👥 Equipo

<table>
  <tr>
    <td align="center">
      <strong>Integrante 1</strong><br/>
      <em>Frontend Lead</em><br/>
      <code>React, TailwindCSS</code>
    </td>
    <td align="center">
      <strong>Integrante 2</strong><br/>
      <em>Backend Lead</em><br/>
      <code>PHP, MySQL</code>
    </td>
    <td align="center">
      <strong>Integrante 3</strong><br/>
      <em>Database & DevOps</em><br/>
      <code>SQL, Deploy</code>
    </td>
    <td align="center">
      <strong>Integrante 4</strong><br/>
      <em>QA & Documentation</em><br/>
      <code>Testing, Docs</code>
    </td>
  </tr>
</table>

---

## 📄 Licencia

Este proyecto es desarrollado como parte del curso **Desarrollo de Software I** en la **Universidad Nacional de San Antonio Abad del Cusco (UNSAAC)**.

**Docente:** Gabriela Zuñiga Rojas

---

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-❤️-9C221C?style=for-the-badge" alt="Made with love"/>
  <img src="https://img.shields.io/badge/Cusco-Perú%20🇵🇪-9C221C?style=for-the-badge" alt="Cusco"/>
</p>

<p align="center">
  <strong>© 2025 - UNSAAC | Desarrollo de Software I</strong>
</p>
