Plataforma Web para la Denuncia Ciudadana de Problemas Urbanos
Este repositorio contiene el código fuente y la documentación para el proyecto semestral del curso "Desarrollo de Software I" (2025-2). El objetivo es desarrollar una plataforma web completa que permita a los ciudadanos reportar problemas urbanos (baches, fallas de alumbrado, basura, etc.) para que sean gestionados por las autoridades correspondientes.

El sistema se compone de una API RESTful, una aplicación web de tipo SPA (Single Page Application), una base de datos relacional y una configuración de DevOps para su despliegue.

(Ver documento de referencia adjunto: Proyecto semestral 2025-2.docx)

Objetivo del Proyecto
Crear una herramienta digital intuitiva y eficiente que conecte a los ciudadanos con las entidades municipales. La plataforma facilitará la visibilización, seguimiento y resolución de incidencias en la infraestructura y servicios urbanos, mejorando la transparencia y la participación ciudadana.

Funcionalidades Clave
Gestión de Usuarios: Registro, inicio de sesión (autenticación con JWT) y perfiles de usuario (ciudadano y administrador).

Creación de Reportes: Formulario para crear denuncias, incluyendo título, descripción, categoría (ej. "Vialidad", "Limpieza", "Seguridad"), y ubicación geográfica (integración con un mapa interactivo).

Evidencia Multimedia: Posibilidad de adjuntar imágenes o videos cortos a cada reporte para documentar la incidencia.

Visualización y Filtro: Un mapa interactivo y una vista de lista para que los usuarios puedan ver todos los reportes públicos. Se podrán aplicar filtros por categoría, estado o fecha.

Seguimiento de Estado: Los reportes tendrán diferentes estados (ej. "Recibido", "En Proceso", "Resuelto", "Rechazado") que serán actualizados por los administradores.

Notificaciones: Alertas por correo electrónico o dentro de la plataforma para los usuarios sobre cambios de estado en sus reportes.

Dashboard de Administración: Un panel para que los administradores gestionen usuarios, revisen, asignen y actualicen el estado de los reportes, y visualicen estadísticas básicas.

Estructura del Repositorio
A continuación se detalla el propósito de cada directorio principal del proyecto:

backend/
Contiene el código fuente de la API REST. Es el cerebro de la aplicación, encargado de la lógica de negocio, el acceso a los datos y la seguridad.

Framework: Node.js con Express.js para la creación de endpoints robustos.

ORM: Sequelize para la interacción con la base de datos PostgreSQL, permitiendo un manejo de datos seguro y modelado de objetos.

Autenticación: Implementación de JSON Web Tokens (JWT) para proteger las rutas y gestionar las sesiones de usuario.

Estructura: Organizado siguiendo patrones de diseño como Modelo-Vista-Controlador (MVC) para mantener el código ordenado y escalable.

frontend/
Alberga la Single Page Application (SPA) con la que los usuarios interactúan directamente.

Librería: React, utilizando hooks y componentes para construir una interfaz de usuario moderna y reactiva.

Gestión de Estado: React Context API o Redux para manejar el estado global de la aplicación de manera eficiente.

Enrutamiento: React Router para la navegación entre las distintas vistas (mapa, lista de reportes, perfil, etc.) sin recargar la página.

Estilos: Se utilizará un framework como Material-UI, Bootstrap o Tailwind CSS para un diseño visualmente atractivo y responsivo (adaptable a móviles).

db/
Contiene todos los archivos relacionados con la base de datos PostgreSQL.

Esquemas: Diagramas y archivos schema.sql que definen la estructura de las tablas, sus columnas, tipos de datos y relaciones (claves primarias y foráneas).

Migraciones: Scripts gestionados por Sequelize-CLI que permiten versionar los cambios en la estructura de la base de datos de forma controlada. Esto facilita la colaboración y el despliegue.

Seeds: Archivos para poblar la base de datos con datos de prueba (ej. usuarios falsos, categorías iniciales, reportes de ejemplo) para facilitar el desarrollo y las pruebas.

docs/
Toda la documentación del proyecto, tanto técnica como funcional.

Informe: El documento principal del proyecto con el análisis de requerimientos, diseño de la solución y conclusiones.

Diagramas: Modelos UML (casos de uso, diagramas de secuencia, modelo entidad-relación) y diagramas de arquitectura de software.

Prototipos: Diseños de la interfaz de usuario (wireframes y mockups) creados en herramientas como Figma o Adobe XD, que sirvieron como guía para el desarrollo del frontend.

devops/
Configuración para la integración y el despliegue continuo (CI/CD).

Docker: Un docker-compose.yml que define y orquesta los contenedores para cada servicio (backend, frontend, base de datos), garantizando un entorno de desarrollo y producción consistente.

Servidor Web/Proxy Inverso: Configuración de Nginx para gestionar las peticiones entrantes, redirigirlas al servicio correspondiente (frontend o backend) y manejar certificados SSL.

Scripts de Despliegue: Archivos de script (bash) para automatizar el proceso de despliegue en un servidor.

tests/
Contiene las pruebas automatizadas para garantizar la calidad y el correcto funcionamiento del software.

Pruebas Unitarias: Usando frameworks como Jest, se prueban las funciones y componentes individuales de forma aislada (ej. una función de validación en el backend o un componente de React).

Pruebas de Integración: Se verifica que múltiples partes del sistema funcionen correctamente juntas. Por ejemplo, se utiliza Supertest para probar que un endpoint de la API realiza la operación correcta en la base de datos.
