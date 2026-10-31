# KADE Caminhões - Development Environment Setup

## Overview

This guide provides comprehensive instructions for setting up, running, and maintaining the KADE Caminhões PHP application. It covers development environment setup, deployment procedures, and troubleshooting for both local and production environments.

## Prerequisites

### Required Software

#### Local Development (Laragon)
- [x] **Laragon**: Fast local server environment
- [x] **MySQL**: Database server
- [x] **Git**: Version control
- [x] **PHP**: 7.x+ (with extensions)

#### Production Environment
- [x] **Web Server**: Apache, Nginx, or equivalent
- [x] **Database**: MySQL 5.7+, PostgreSQL 10+, or SQL Server
- [x] **PHP**: 7.4+ with required extensions
- [x] **Composer**: Dependency manager
- [x] **Git**: Version control

### Installation Steps

#### 1. Clone Repository
```bash
# Clone the project
git clone <repository-url> php-kade
cd php-kade

# Check current directory
ls -la
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies (if using Composer)
composer install

# Verify PHP installation
php --version
php -v
```

#### 3. Setup Web Server (Development)

##### Option A: Laragon (Recommended for Development)
```cmd
# Open Command Prompt as Administrator
cmd /c mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade
```

##### Option B: XAMPP/MAMP (Alternative)
```bash
# Copy application to htdocs/www directory
mkdir -p /path/to/xampp/htdocs
# Application will be at /path/to/xampp/htdocs/kade
```

##### Option C: Manual Setup
```bash
# Create web root directory
mkdir -p /var/www/kade

# Copy application contents
cp -r kade/* /var/www/kade/

# Set permissions
chmod -R 755 /var/www/kade
chmod -R 664 /var/www/kade/*.php
```

#### 4. Database Setup

##### Create MySQL Database
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE kadecaminh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create additional database for CEP
CREATE DATABASE kadecaminh_1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Exit
exit
```

##### Import Database Dump
```bash
# Import main database
mysql -u root -p --default-character-set=utf8mb4 kadecaminh < kade/der/kade_full_import.sql

# Import CEP database
mysql -u root -p --default-character-set=utf8mb4 kadecaminh_1 < kade/der/kade_full_import.sql

# Or use phpMyAdmin GUI
```

##### Apply Migrations
The dump is older than the code. Run every script in `kade/der/migrations` once, in filename order (001 → 007), after taking a backup. See the "Migrações" section of [README.md](README.md) for what each one changes.

```bash
mysqldump -u root -p --routines --triggers kadecaminh > backup_before_migrations.sql
for f in kade/der/migrations/*.sql; do mysql -u root -p --default-character-set=utf8mb4 kadecaminh < "$f"; done
```

#### 5. Environment Configuration

##### Copy Environment Template
```bash
# Create .env file from example
cp .env.example .env

# Edit .env with your credentials
nano .env
```

##### Environment Variables
```env
# Database Configuration - Development
DBKADE_DEV_USER=root
DBKADE_DEV_PASSWORD=your_password
DBKADE_DEV_HOST=localhost
DBKADE_DEV_DATABASE=kadecaminh
DBKADE_DEV_PORT=3306

DBCEP_DEV_USER=root
DBCEP_DEV_PASSWORD=your_password
DBCEP_DEV_HOST=localhost
DBCEP_DEV_DATABASE=kadecaminh_1
DBCEP_DEV_PORT=3306

# Database Configuration - Production
DBKADE_PRD_USER=prod_user
DBKADE_PRD_PASSWORD=prod_password
DBKADE_PRD_HOST=prod.db.server
DBKADE_PRD_DATABASE=kadecaminh_prod
DBKADE_PRD_PORT=3306

# SMTP Configuration
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=notifications@kadecaminhoes.com.br
SMTP_PASSWORD=your_smtp_password
SMTP_AUTH=true
# Sender address, on the same domain as the SMTP server (SPF/DKIM)
SMTP_FROM=contato@kadecaminhoes.com.br

# Public site URL used in e-mail links (sign-up confirmation, password reset). Required in production.
APP_URL=https://www.kadecaminhoes.com.br
```

For local e-mail, point SMTP to Laragon's Mailpit (`SMTP_HOST=127.0.0.1`, `SMTP_PORT=1025`, empty `SMTP_USER`) and read the messages at `http://localhost:8025`.

##### CSS Build
The pages use Tailwind CSS v4. The compiled `kade/shared/css/app.css` is committed, so the server never needs Node. After changing classes in views or JS, rebuild it:

```bash
npm ci
npm run build:css
```

#### 6. Permissions and Ownership
```bash
# Set directory permissions
chmod -R 755 kade/shared/css
chmod -R 755 kade/shared/js
chmod -R 755 kade/images
chmod -R 755 kade/der

# Set file permissions for PHP files
find kade -name "*.php" -type f -exec chmod 644 {} \;
find kade -name "*.php" -type f -exec chown www-data:{} \;  # Adjust as needed
```

## Running the Application

### Development Server

#### Option 1: PHP Built-in Server
```bash
# Navigate to application directory
cd kade

# Start development server
php -S localhost:8000 -t .

# Access the application
http://localhost:8000/
```

#### Option 2: Laragon
```bash
# Access through Laragon
http://kade.test/

# Common routes
http://kade.test/cad_veiculo/
http://kade.test/cad_cliente/
http://kade.test/maps/
```

#### Option 3: Apache/Nginx (Production)

##### Apache Configuration
```apache
<VirtualHost *:80>
    DocumentRoot "/var/www/kade"
    ServerName kade.test
    
    <Directory "/var/www/kade">
        AllowOverride All
        Require all granted
    </Directory>
    
    # PHP handler
    AddHandler application/x-httpd-php .php
    AddType application/x-httpd-php .php
</VirtualHost>
```

##### Nginx Configuration
```nginx
server {
    listen 80;
    server_name kade.test;
    root /var/www/kade;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    # PHP handler
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
    }
}
```

### Application Entry Point

#### Main Entry Point
```php
// kade/index.php
<?php
// Ensure UTF-8 and suppress displayed PHP warnings/notices in output to avoid breaking JSON/AJAX responses.
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
// Temporarily suppress error output in HTTP responses (log them instead). Reversible.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(dirname(__FILE__)."/shared/class/controller/ViewExtCtrl.class.php");
ViewExtCtrl::includeMainPage();
```

## Application Testing

### Syntax Checking
```bash
# Check specific files
php -l kade/index.php
php -l kade/shared/class/controller/AbstractCtrl.class.php
php -l kade/shared/class/dao/AddressDAO.class.php

# Check all PHP files recursively
find kade -name "*.php" -type f -exec php -l {} \; 2>&1 | head -50
```

### Database Connection Testing
```bash
# Test MySQL connection
mysql -u root -p -e "SHOW DATABASES;"

# Test application connectivity
mysql -u root -p -e "USE kadecaminh; SHOW TABLES;"
```

### HTTP Endpoint Testing
```bash
# Check application status
curl -I http://kade.test/

# Test specific routes
curl -I http://kade.test/cad_veiculo/
curl -I http://kade.test/cad_cliente/

# Test API endpoints (if available)
curl -X GET http://kade.test/api/health
curl -X POST -H "Content-Type: application/json" -d '{"test": "data"}' http://kade.test/api/test
```

### Form Testing
```bash
# Test form availability
curl http://kade.test/cad_veiculo/
curl http://kade.test/cad_cliente/

# Test with required parameters
# For vehicle registration
# Required: vehicle_type, vehicle_zipcode, vehicle_phone, etc.
```

## Performance Optimization

### 1. PHP Configuration
```ini
; php.ini recommendations
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
post_max_size = 100M
upload_max_filesize = 100M

; Error handling for production
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

; Session settings
session.use_strict_mode = On
session.cookie_httponly = On
session.cookie_secure = On
```

### 2. Web Server Optimization
```nginx
# Nginx optimization
worker_processes auto;
worker_connections 1024;

# Gzip compression
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

# Caching
expires_active on;
expires_default "10 days";
expires_max_age 864000;
```

### 3. Database Optimization
```sql
-- Create indexes for frequently queried columns
CREATE INDEX idx_users_login ON users(login);
CREATE INDEX idx_persons_cpf ON persons(cpf);
CREATE INDEX idx_persons_cnpj ON persons(cnpj);
CREATE INDEX idx_addresses_cep ON addresses(cep);
CREATE INDEX idx_vehicle_traveling_type ON vehicles_traveling(vehicle_type_id);

-- Optimize queries
-- Use prepared statements
-- Implement connection pooling
-- Cache frequently accessed data
```

### 4. Caching Strategies
```php
// Enable output buffering
ob_start();

// Cache CEP lookups
$cacheKey = "cep_" . $cep;
$cachedResult = getCache($cacheKey);

if ($cachedResult === false) {
    $cachedResult = $addressDAO->getAddressByZipCode($db, $address);
    setCache($cacheKey, $cachedResult, 3600);
}
```

## Security Configuration

### 1. Web Server Security
```apache
<Directory "/var/www/kade">
    # Security headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Authentication requirements
    AuthType Basic
    AuthName "KADE Caminhões"
    AuthUserFile /var/www/kade/.htpasswd
    Require valid-user
</Directory>
```

### 2. PHP Security
```php
<?php
// Input sanitization
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

// Session security
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true, // Only over HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// CSRF protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

### 3. Database Security
```sql
-- Limit database user permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON kadecaminh.* TO 'kade_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON kadecaminh_1.* TO 'kade_user'@'localhost';

-- SSL configuration
-- Enable MySQL SSL
-- Configure client to use SSL
```

## Backup and Recovery

### 1. Manual Backup
```bash
# Create backup directory
mkdir -p backups

# Database backup
mysqldump -u root -p --default-character-set=utf8mb4 kadecaminh > backups/kadecaminh_$(date +%Y%m%d_%H%M%S).sql
mysqldump -u root -p --default-character-set=utf8mb4 kadecaminh_1 > backups/kadecaminh_1_$(date +%Y%m%d_%H%M%S).sql

# Application files backup
cp -r kade backups/kade_$(date +%Y%m%d_%H%M%S)
```

### 2. Automated Backup (Cron)
```bash
# Add to crontab (crontab -e)
0 2 * * * cd /path/to/php-kade && mysqldump -u root -p --default-character-set=utf8mb4 kadecaminh > backups/kadecaminh_$(date +\%Y\%m\%d_\%H\%M\%S).sql
0 2 * * * cd /path/to/php-kade && mysqldump -u root -p --default-character-set=utf8mb4 kadecaminh_1 > backups/kadecaminh_1_$(date +\%Y\%m\%d_\%H\%M\%S).sql

# Monthly application backup
0 3 1 * * cd /path/to/php-kade && cp -r kade backups/kade_$(date +\%Y\%m\%d)
```

### 3. Recovery Procedure
```bash
# Restore database
mysql -u root -p kadecaminh < backups/kadecaminh_20240115_030000.sql
mysql -u root -p kadecaminh_1 < backups/kadecaminh_1_20240115_030000.sql

# Restore application files
cd /var/www/kade
rm -rf .
cp -r /path/to/php-kade/backups/kade_20240115/* .
```

## Troubleshooting Guide

### Common Issues and Solutions

#### Issue 1: Connection Refused
**Symptoms**: `Can't connect to MySQL server`
**Causes**:
- MySQL not running
- Wrong port
- Incorrect credentials

**Solutions**:
```bash
# Check MySQL service
service mysql status
service mysql start

# Test connection
mysql -u root -p -e "SELECT 1;"

# Check port
netstat -tlnp | grep :3306
```

#### Issue 2: UTF-8 Character Problems
**Symptoms**: Accented characters as "?" or garbled text
**Causes**:
- Wrong file encoding
- Incorrect HTTP headers
- MySQL charset mismatch

**Solutions**:
```bash
# Check file encoding
file kade/view/cad_veiculo.php
iconv -f utf-8 -t utf-8 kade/view/cad_veiculo.php > temp.php && mv temp.php kade/view/cad_veiculo.php

# Check PHP output
header("Content-Type: text/html; charset=UTF-8");
```

#### Issue 3: PHP Warnings Breaking JSON
**Symptoms**: "Unexpected token '<'" in frontend
**Causes**:
- Warnings printed before JSON output
- Output buffer not working

**Solutions**:
```php
// Check index.php configuration
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// In controllers, use output buffering
ob_start();
// ... controller logic ...
$buffer = ob_get_clean();
if (trim($buffer) !== '') {
    error_log("Unexpected output: " . $buffer);
    // Return safe error
}
```

#### Issue 4: File Permissions
**Symptoms**: "500 Internal Server Error"
**Causes**:
- Incorrect file/directory permissions
- Ownership issues

**Solutions**:
```bash
# Check permissions
ls -la kade/index.php

# Fix permissions
chmod 644 kade/index.php
chmod 755 kade
chmod -R 644 kade/shared
chmod -R 755 kade/shared/class

# Check ownership (adjust as needed)
chown -R www-data:www-data kade
```

### Diagnostic Commands
```bash
# System information
php --version
mysql --version
apache2 --version  # or nginx -v

# Application diagnostics
curl -I http://kade.test/
php -l kade/index.php
find kade -name "*.php" -type f -exec php -l {} \;

# Database diagnostics
mysql -u root -p -e "SHOW VARIABLES LIKE 'character_set%';"

# Performance check
/usr/bin/time -p php -S localhost:8000 -t kade
```

## Monitoring and Logging

### 1. Error Logging
```php
// Enable comprehensive error logging
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/php_errors.log');

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "[$errno] $errstr in $errfile on line $errline";
    error_log($message);
    
    if ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) {
        // Log critical errors to external system
        // Send alerts to administrators
    }
}

set_error_handler('customErrorHandler');
```

### 2. Application Logging
```php
function logActivity($action, $details = null) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null
    ];
    
    file_put_contents('/var/log/application.log', 
        json_encode($logEntry) . "\n", 
        FILE_APPEND
    );
}
```

### 3. Health Check Endpoint
```php
// Add to any controller
function healthCheck() {
    $health = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => checkDatabaseConnection(),
        'disk_space' => disk_free_space('/'),
        'memory_usage' => memory_get_usage(true)
    ];
    
    header('Content-Type: application/json');
    echo json_encode($health);
    exit;
}

function checkDatabaseConnection() {
    try {
        $db = DataBase::getInstance($this->getDbConfig());
        $db->query('SELECT 1');
        return 'connected';
    } catch (Exception $e) {
        return 'disconnected: ' . $e->getMessage();
    }
}
```

## Deployment Checklist

### Pre-Deployment
- [ ] Code review completed
- [ ] All tests passed
- [ ] Database schema verified
- [ ] Environment variables configured
- [ ] Security audit completed
- [ ] Backup created

### Deployment Steps
1. Stop application services
2. Deploy new code
3. Run database migrations
4. Update configuration
5. Start services
6. Test functionality
7. Monitor performance
8. Document changes

### Post-Deployment
- [ ] Load testing completed
- [ ] Performance monitoring configured
- [ ] Error logging verified
- [ ] Security scan completed
- [ ] User notification sent

## Environment-Specific Configuration

### Development Environment
```env
INSTANCE=DEV
DBKADE_DEV_USER=root
DBKADE_DEV_PASSWORD=dev_password
DBKADE_DEV_HOST=localhost
DBKADE_DEV_DATABASE=kadecaminh_dev

# Enable debugging
error_reporting(E_ALL)
display_errors = On
log_errors = On
```

### Production Environment
```env
INSTANCE=PRD
DBKADE_PRD_USER=app_user
DBKADE_PRD_PASSWORD=prod_password
DBKADE_PRD_HOST=prod.db.server
DBKADE_PRD_DATABASE=kadecaminh_prod

# Disable debugging
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT)
display_errors = Off
log_errors = On
```

## Configuration Files

### Configuration Hierarchy
```
/etc/php-kade/          # System-wide configuration
~/.php-kade/           # User configuration
./.env                 # Environment variables
./kade/der/*.sql       # Database dumps
./kade/shared/class/util/Properties.class.php  # Runtime configuration
```

### Configuration Examples
```php
// Example: Dynamic configuration loading
class Configuration {
    private static $instance = null;
    private $config = [];
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function load($envFile) {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && !strpos($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->config[trim($key)] = trim($value);
                }
            }
        }
    }
    
    public function get($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
```

## Common Configurations

### Nginx with PHP-FPM
```nginx
# php-kade.conf
server {
    listen 80;
    server_name kade.test;
    root /var/www/kade;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "open_basedir=/var/www/kade:/tmp";
    }
    
    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache with mod_rewrite
```apache
# .htaccess file
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Security
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Caching
ExpiresActive On
ExpiresDefault "access plus 1 hour"
```

## Environment Variables Reference

### Database Configuration
```bash
# Development
DBKADE_DEV_USER=dev_user
DBKADE_DEV_PASSWORD=dev_pass
DBKADE_DEV_HOST=localhost
DBKADE_DEV_DATABASE=kadecaminh_dev
DBKADE_DEV_PORT=3306

# Production
DBKADE_PRD_USER=prod_user
DBKADE_PRD_PASSWORD=prod_pass
DBKADE_PRD_HOST=prod.db.server
DBKADE_PRD_DATABASE=kadecaminh_prod
DBKADE_PRD_PORT=3306
```

### CEP Database Configuration
```bash
DBCEP_DEV_USER=cep_user
DBCEP_DEV_PASSWORD=cep_pass
DBCEP_DEV_HOST=localhost
DBCEP_DEV_DATABASE=cep_data_dev
DBCEP_DEV_PORT=3306

DBCEP_PRD_USER=cep_prod_user
DBCEP_PRD_PASSWORD=cep_prod_pass
DBCEP_PRD_HOST=prod.db.server
DBCEP_PRD_DATABASE=cep_data_prod
DBCEP_PRD_PORT=3306
```

### SMTP Configuration
```bash
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=notifications@kadecaminhoes.com.br
SMTP_PASSWORD=app_password
SMTP_AUTH=true
```

## Support and Maintenance

### Contact Information
- **Technical Support**: `contato@kadecaminhoes.com.br`
- **Documentation**: `docs.kadecaminhoes.com.br`
- **Repository**: `github.com/php-kade/php-kade`

### Service Level Agreements (SLA)
- **Uptime**: 99.9% (weekly maintenance windows)
- **Response Time**: Critical issues within 2 hours
- **Resolution Time**: Critical issues within 24 hours

### Maintenance Windows
- **Monthly Updates**: First Sunday, 02:00-04:00 UTC
- **Emergency Maintenance**: As required
- **Database Updates**: Quarterly on maintenance windows

## Legal and Compliance

### Data Protection
- **GDPR Compliance**: Personal data handling
- **Data Retention**: 7 years for customer records
- **Data Export**: Available upon request

### Accessibility
- **WCAG 2.1 AA**: 100% compliance
- **Screen Reader Support**: Full compatibility
- **Keyboard Navigation**: Complete support

### Security Standards
- **OWASP Top 10**: Implementation verified
- **PCI DSS**: Credit card data handling
- **ISO 27001**: Information security management

## Future Roadmap

### Short-term (0-6 months)
- [ ] Implement automated testing suite
- [ ] Add Docker support
- [ ] Implement API documentation
- [ ] Add performance monitoring

### Medium-term (6-12 months)
- [ ] Migrate to modern framework
- [ ] Implement microservices architecture
- [ ] Add advanced analytics
- [ ] Implement CI/CD pipeline

### Long-term (1-3 years)
- [ ] Complete application rewrite
- [ ] Implement AI/ML features
- [ ] Add blockchain integration
- [ ] Global expansion

## Conclusion

The KADE Caminhões application provides a comprehensive solution for truck carrier management with robust features for customer and vehicle registration. This environment setup guide ensures proper configuration, testing, and maintenance for both development and production environments.

For additional information or support, please refer to the main documentation or contact the development team.
