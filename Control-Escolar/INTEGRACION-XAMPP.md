# 🎯 INTEGRACIÓN CON XAMPP - Control-Escolar

## 📋 INSTRUCCIONES PARA INTEGRACIÓN SIMPLE

### 🚀 **PASO 1: Copiar Archivos**
1. Copia toda la carpeta `Control-Escolar` a tu proyecto XAMPP
2. La estructura debe quedar así:
   ```
   htdocs/escuela-de-crecimiento/
   ├── index.php (tu landing page existente)
   ├── Control-Escolar/ (nueva carpeta)
   │   ├── .htaccess
   │   ├── public/
   │   │   └── index.php
   │   ├── config/
   │   ├── src/
   │   └── otros archivos...
   ```

### 🔗 **PASO 2: Conectar Landing Page**
En tu `index.php` principal (landing page), agrega un botón que redirija a Control-Escolar:

```html
<!-- Ejemplo de botón para tu landing page -->
<div class="text-center mt-4">
    <a href="/Control-Escolar/" class="btn btn-primary btn-lg">
        <i class="fas fa-school"></i> Acceder al Control Escolar
    </a>
</div>
```

### ⚙️ **PASO 3: Configuración de Base de Datos**
1. Abre el archivo `Control-Escolar/config/database.php`
2. Edita solo estas líneas con tus credenciales:
   ```php
   'host' => 'localhost',           // Tu servidor BD
   'dbname' => 'tu_base_datos',     // Nombre de tu BD
   'username' => 'tu_usuario',      // Tu usuario MySQL
   'password' => 'tu_password',     // Tu contraseña MySQL
   ```

### 💾 **PASO 4: Importar Base de Datos**
1. Abre phpMyAdmin en XAMPP
2. Crea una nueva base de datos o usa una existente
3. Importa el archivo `Control-Escolar/database.sql`

### 🌐 **PASO 5: Acceder al Sistema**
- **URL del Control Escolar**: `http://localhost/escuela-de-crecimiento/Control-Escolar/`
- **Usuario por defecto**: admin@christianlms.com
- **Contraseña por defecto**: password

---

## ✅ **CONFIGURACIÓN AUTOMÁTICA**

### 🔧 **Todo está preconfigurado para funcionar como subcarpeta:**
- ✅ `.htaccess` configurado para `/Control-Escolar/`
- ✅ Routing automático desde la subcarpeta
- ✅ Redirecciones correctas
- ✅ URLs absolutas funcionando
- ✅ Sin configuraciones complejas

### 🎯 **URLs que funcionarán automáticamente:**
- `http://tu-dominio.com/Control-Escolar/` → Dashboard/Login
- `http://tu-dominio.com/Control-Escolar/login` → Login
- `http://tu-dominio.com/Control-Escolar/dashboard` → Panel principal
- `http://tu-dominio.com/Control-Escolar/dashboard/courses` → Cursos
- `http://tu-dominio.com/Control-Escolar/dashboard/enrollments` → Inscripciones
- `http://tu-dominio.com/Control-Escolar/dashboard/subjects` → Materias

---

## 🔗 **INTEGRACIÓN CON TU LANDING EXISTENTE**

### Ejemplo de integración en tu landing page:

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escuela de Crecimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Tu contenido existente de la landing page -->
    <div class="hero-section text-center py-5">
        <h1>Bienvenidos a la Escuela de Crecimiento</h1>
        <p class="lead">Formando líderes con valores cristianos</p>
        
        <!-- BOTÓN PARA CONTROL ESCOLAR -->
        <div class="mt-4">
            <a href="Control-Escolar/" class="btn btn-primary btn-lg">
                <i class="fas fa-graduation-cap"></i>
                Acceder al Control Escolar
            </a>
        </div>
        
        <!-- Otros botones de tu landing -->
        <div class="mt-3">
            <a href="#cursos" class="btn btn-outline-primary">Ver Cursos</a>
            <a href="#contacto" class="btn btn-outline-secondary">Contacto</a>
        </div>
    </div>
    
    <!-- Resto de tu contenido -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 🎯 **VENTAJAS DE ESTA CONFIGURACIÓN**

### ✅ **Para Dominio Compartido:**
- Sin configuraciones complejas de servidor
- Funciona en cualquier hosting con Apache
- No requiere acceso a configuración del servidor
- Compatible con cPanel y otros paneles

### ✅ **Para XAMPP:**
- Instalación simple copiando archivos
- Configuración de BD manual
- URLs amigables funcionando
- Routing automático

### ✅ **Para tu Proyecto:**
- Mantiene tu landing page existente
- Control Escolar como módulo separado
- Navegación fluida entre secciones
- URLs consistentes

---

## 🚨 **IMPORTANTE**

1. **No modifiques** el archivo `.htaccess` a menos que sea necesario
2. **No cambies** la estructura de carpetas
3. **Edita solo** el archivo `config/database.php` para conectar BD
4. **Importa** el archivo `database.sql` en phpMyAdmin

---

## 📞 **SOPORTE**

Si tienes problemas:
1. Verifica que XAMPP esté funcionando
2. Confirma que la carpeta `Control-Escolar` esté en el lugar correcto
3. Revisa los logs de error de PHP
4. Verifica las credenciales de base de datos

**¡El sistema está diseñado para funcionar sin configuraciones complejas!** 🎉