# 🚀 INSTALACIÓN RÁPIDA - Sistema Christian LMS

## ✅ REQUISITOS DEL SISTEMA

- **PHP**: 8.0 o superior
- **MySQL**: 5.7+ o MariaDB 10.3+
- **Apache**: 2.4+ con mod_rewrite habilitado
- **Extensiones PHP**: PDO, PDO_MySQL, JSON, OpenSSL

## 📋 PASOS DE INSTALACIÓN

### 1. **Subir Archivos**
- Sube todos los archivos de este ZIP a tu servidor web
- Asegúrate de que la carpeta `public/` sea el documento raíz de tu dominio

### 2. **Configurar Base de Datos**

#### A) Crear Base de Datos:
```sql
CREATE DATABASE christian_lms_db CHARACTER SET utf8 COLLATE utf8_general_ci;
```

#### B) Editar Configuración:
- Abre el archivo `config/database.php`
- Cambia las credenciales con tus datos reales:

```php
'host' => 'localhost',           // Tu servidor de BD
'dbname' => 'christian_lms_db', // Tu base de datos
'username' => 'tu_usuario',      // Tu usuario MySQL
'password' => 'tu_password',     // Tu contraseña MySQL
```

#### C) Importar Base de Datos:
- Ejecuta el archivo `database.sql` en tu base de datos MySQL
- Puedes usar phpMyAdmin, línea de comandos, o tu herramienta preferida

### 3. **Configurar Servidor Web**

#### Para Apache:
- Asegúrate de que `.htaccess` esté habilitado
- El documento raíz debe apuntar a la carpeta `public/`

#### Para cPanel:
- Configura el "Document Root" a `public/`
- O sube todo el contenido del ZIP a `public_html/`

### 4. **Verificar Instalación**
- Ve a tu dominio en el navegador
- Deberías ver la página de login
- **Usuario por defecto**: admin@christianlms.com
- **Contraseña por defecto**: password

## 🌐 URLS DISPONIBLES

- `/` - Página de inicio (redirige a login o dashboard)
- `/login` - Inicio de sesión
- `/dashboard` - Panel principal
- `/dashboard/courses` - Gestión de cursos
- `/dashboard/enrollments` - Gestión de inscripciones
- `/dashboard/subjects` - Gestión de materias
- `/logout` - Cerrar sesión

## 🔧 CONFIGURACIÓN AVANZADA

### Permisos de Archivos:
```bash
chmod 755 public/
chmod 644 .htaccess
```

### Si tienes problemas con URLs:
- Verifica que mod_rewrite esté habilitado
- Revisa que AllowOverride esté en "All" en Apache

### Base de Datos Local (XAMPP):
```php
'host' => 'localhost',
'username' => 'root',
'password' => '',
```

### Base de Datos en Hosting:
```php
'host' => 'localhost',  // o IP que te dé tu hosting
'username' => 'tu_usuario_cpanel',
'password' => 'tu_password_cpanel',
'dbname' => 'tu_usuario_lms',
```

## 📞 SOPORTE

Si encuentras problemas:

1. **Verifica los requisitos** del sistema
2. **Revisa los logs de error** de PHP y Apache
3. **Confirma la configuración** de base de datos
4. **Verifica permisos** de archivos y carpetas

## 🎯 PRIMEROS PASOS

1. **Login**: Usa admin@christianlms.com / password
2. **Cambia la contraseña** del administrador
3. **Configura períodos académicos**
4. **Crea materias y cursos**
5. **Registra estudiantes y profesores**

## ✅ SISTEMA LISTO

¡El Sistema Christian LMS con Arquitectura Hexagonal está completamente instalado y listo para usar!

**Características incluidas:**
- ✅ Arquitectura hexagonal completa
- ✅ Gestión de cursos, materias e inscripciones
- ✅ Dashboard con estadísticas
- ✅ Frontend responsivo moderno
- ✅ Sistema de autenticación
- ✅ Configuración simple y clara

**Desarrollado con ❤️ usando Arquitectura Hexagonal y DDD**