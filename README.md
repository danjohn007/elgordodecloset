# Sistema de Reservaciones para Restaurantes

Un sistema completo de reservas para restaurantes desarrollado en PHP puro con arquitectura MVC, MySQL y Bootstrap 5.

## Características Principales

### Módulo de Cliente
- ✅ Registro y autenticación de usuarios
- ✅ Búsqueda y filtrado de restaurantes
- ✅ Reservas en tiempo real con disponibilidad
- ✅ Gestión de reservas (crear, modificar, cancelar)
- ✅ Sistema de reseñas y calificaciones
- ✅ Notificaciones por email

### Módulo de Administrador de Restaurante
- ✅ Gestión completa del perfil del restaurante
- ✅ Configuración de mesas y zonas
- ✅ Control de horarios y turnos
- ✅ Gestión de reservas (confirmar, rechazar, reasignar)
- ✅ Reportes y estadísticas
- ✅ Respuestas a reseñas

### Módulo Super Administrador
- ✅ Gestión de restaurantes del sistema
- ✅ Control de usuarios
- ✅ Monitoreo global y reportes
- ✅ Panel de administración completo

## Tecnologías Utilizadas

- **Backend**: PHP 7.x (sin frameworks)
- **Base de Datos**: MySQL 5.7/8.0
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Arquitectura**: MVC clásico
- **Servidor Web**: Apache/Nginx

## Estructura del Proyecto

```
elgordodecloset/
├── app/
│   ├── Controllers/     # Controladores MVC
│   ├── Models/         # Modelos de datos
│   ├── Services/       # Servicios de negocio
│   ├── Core/          # Framework MVC
│   ├── Helpers/       # Helpers y middleware
│   └── Views/         # Vistas y plantillas
├── config/            # Configuración
├── public/            # Punto de entrada web
│   ├── assets/        # CSS, JS, imágenes
│   └── index.php      # Front controller
├── sql/               # Esquema de base de datos
├── storage/           # Logs y cache
└── vendor/            # Autoloader
```

## Instalación

### Requisitos del Sistema
- PHP 7.4 o superior
- MySQL 5.7 o 8.0
- Apache o Nginx
- Extensiones PHP: PDO, mbstring, openssl

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/danjohn007/elgordodecloset.git
   cd elgordodecloset
   ```

2. **Configurar la base de datos**
   - Crear una base de datos MySQL llamada `restaurant_reservations`
   - Importar el esquema desde `sql/schema.sql`:
   ```bash
   mysql -u root -p restaurant_reservations < sql/schema.sql
   ```

3. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   ```
   
   Editar `.env` con tus configuraciones:
   ```env
   DB_HOST=localhost
   DB_NAME=restaurant_reservations
   DB_USER=tu_usuario
   DB_PASS=tu_contraseña
   
   MAIL_HOST=smtp.gmail.com
   MAIL_USERNAME=tu_email
   MAIL_PASSWORD=tu_password
   ```

4. **Configurar el servidor web**

   **Para Apache:**
   - Apuntar el DocumentRoot a la carpeta `public/`
   - Asegurar que el módulo `mod_rewrite` esté habilitado
   - El archivo `.htaccess` ya está configurado

   **Para Nginx:**
   ```nginx
   server {
       listen 80;
       server_name tu-dominio.com;
       root /path/to/elgordodecloset/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

5. **Configurar permisos**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/assets/
   ```

## Cuentas de Demostración

El sistema incluye cuentas de prueba pre-configuradas:

| Rol | Email | Contraseña | Descripción |
|-----|-------|------------|-------------|
| Cliente | juan@cliente.com | password | Usuario para hacer reservas |
| Cliente | maria@cliente.com | password | Usuario adicional |
| Admin Restaurante | carlos@restaurante.com | password | Administra "El Gordo de Closet" |
| Admin Restaurante | ana@restaurante.com | password | Administra "La Cocina de Ana" |
| Super Admin | admin@sistema.com | password | Administrador del sistema |

## Funcionalidades Implementadas

### Sistema de Autenticación
- [x] Registro de usuarios con validación
- [x] Inicio de sesión seguro
- [x] Recuperación de contraseña por email
- [x] Control de roles (Cliente, Admin Restaurante, Super Admin)
- [x] Middleware de autorización

### Gestión de Restaurantes
- [x] CRUD completo de restaurantes
- [x] Gestión de mesas y zonas
- [x] Configuración de horarios y turnos
- [x] Subida de imágenes
- [x] Geolocalización

### Sistema de Reservas
- [x] Búsqueda de disponibilidad en tiempo real
- [x] Reservas automáticas y manuales
- [x] Modificación y cancelación de reservas
- [x] Códigos de confirmación únicos
- [x] Verificación de conflictos

### Notificaciones
- [x] Emails de confirmación
- [x] Recordatorios automáticos
- [x] Notificaciones de cancelación
- [x] Templates HTML personalizados

### Sistema de Reseñas
- [x] Calificaciones con estrellas
- [x] Comentarios detallados
- [x] Respuestas de restaurantes
- [x] Cálculo automático de rating promedio

### Seguridad
- [x] Protección CSRF
- [x] Validación de entrada
- [x] Passwords hasheados
- [x] Sanitización de datos
- [x] Control de acceso basado en roles

### Panel de Administración
- [x] Dashboard con métricas
- [x] Gestión de reservas
- [x] Reportes y estadísticas
- [x] Configuración de restaurant

## API Endpoints

El sistema incluye algunos endpoints API para funcionalidades específicas:

- `GET /api/restaurants/search` - Búsqueda de restaurantes
- `GET /api/restaurants/{id}/availability` - Verificar disponibilidad
- `POST /api/reservations` - Crear reserva vía API

## Personalización

### Agregar nuevos tipos de cocina
Editar el enum en `sql/schema.sql` y actualizar la base de datos.

### Modificar horarios de servicio
Los horarios se configuran por restaurante en el panel de administración.

### Personalizar emails
Editar las plantillas en `app/Services/MailerService.php`.

## Mantenimiento

### Logs del Sistema
Los logs se almacenan en `storage/logs/`:
- `mail.log` - Registro de emails enviados
- `upcoming_reservations.log` - Reservas próximas

### Tareas Programadas (Cron)
Configurar las siguientes tareas en el crontab del servidor:

```bash
# Enviar recordatorios diarios (8:00 AM)
0 8 * * * php /path/to/project/scripts/send_daily_reminders.php

# Limpiar reservas expiradas (2:00 AM)
0 2 * * * php /path/to/project/scripts/cleanup_expired_reservations.php

# Reportes semanales (Lunes 9:00 AM)
0 9 * * 1 php /path/to/project/scripts/send_weekly_reports.php
```

## Solución de Problemas

### Error de conexión a la base de datos
1. Verificar configuración en `.env`
2. Confirmar que MySQL está corriendo
3. Validar permisos del usuario de base de datos

### Emails no se envían
1. Verificar configuración SMTP en `.env`
2. Revisar logs en `storage/logs/mail.log`
3. Confirmar que las credenciales de email son correctas

### Error 404 en rutas
1. Verificar configuración de Apache/Nginx
2. Confirmar que mod_rewrite está habilitado (Apache)
3. Revisar permisos del archivo `.htaccess`

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para reportar bugs o solicitar nuevas funcionalidades, por favor crear un issue en GitHub.

## Changelog

### v1.0.0 (2024-08-14)
- ✅ Implementación inicial del sistema MVC
- ✅ Sistema de autenticación completo
- ✅ Módulo de reservas funcional
- ✅ Panel de administración
- ✅ Sistema de notificaciones
- ✅ Base de datos con datos de ejemplo
- ✅ Interfaz responsive con Bootstrap 5
