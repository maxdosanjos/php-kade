# KADE Caminhões - Package Documentation

## Overview

KADE Caminhões is a PHP-based web application designed for truck carrier management. This package provides comprehensive documentation for setup, usage, development, and maintenance.

## Quick Start

### Installation (Laragon)

1. **Setup Web Root**
   ```cmd
   cmd /c mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade
   ```

2. **Import Database**
   - Use MySQL (Laragon)
   - Import `kade\der\kade_full_import.sql`
   - Use charset: `utf8mb4` for UTF-8 support

3. **Environment Configuration**
   ```bash
   # Copy .env.example to .env in repository root
   cp .env.example .env
   ```

4. **Access Application**
   - Navigate to: `http://kade.test/`
   - Or use PHP built-in: `php -S localhost:8000 -t kade`

## Architecture

### Directory Structure

```
.
├── kade/                    # Web application root
│   ├── index.php            # Front controller
│   ├── view/               # Views (routes)
│   │   ├── cad_veiculo.php  # Vehicle registration form
│   │   ├── cad_cliente.php  # Customer registration form
│   │   └── maps.php         # Map view (default route)
│   └── shared/             # Shared application components
│       ├── class/          # Core classes
│       │   ├── controller/ # Controllers
│       │   ├── dao/        # Data Access Objects
│       │   ├── vo/         # Value Objects
│       │   └── util/        # Utilities
│       ├── css/            # Stylesheets
│       ├── js/             # JavaScripts
│       └── view/           # Shared views
│   └── der/                # Database dumps and models
├── .env.example            # Environment template
└── README.md              # Documentation (this file)
```

### Core Components

#### Controllers
- **AbstractCtrl.class.php**: Base controller with database configuration and message handling
- **ViewExtCtrl.class.php**: Main view controller handling routing and mobile detection
- **CustumerRegCtrl.class.php**: Customer registration (web and mobile)
- **VehicleRegCtrl.class.php**: Vehicle registration (web and SMS)

#### Data Access Objects
- **AbstractCtrl.class.php**: Database configuration management
- **AddressDAO.class.php**: Address/CEP lookup with ViaCEP integration

### Technologies & Dependencies

- **PHP**: 7.x+ (tested on 8.x)
- **MySQL/SQL Server**: Database backend
- **Laragon**: Local development environment (recommended)
- **Tailwind CSS**: Frontend styling
- **jQuery**: DOM manipulation

## Development Guide

### Code Conventions

#### File Naming
- Controllers: `*Ctrl.class.php` (e.g., `VehicleRegCtrl.class.php`)
- Data Access: `*DAO.class.php` (e.g., `AddressDAO.class.php`)
- Value Objects: `*VO.class.php` (e.g., `AddressVO.class.php`)
- Views: `lowercase_underscore.php` (e.g., `cad_veiculo.php`)

#### PHP Standards
```php
// Always require_once for includes
require_once (dirname (dirname ( __FILE__ )) . "/util/Util.class.php");

// Classes follow naming conventions
class VehicleRegCtrl extends AbstractCtrl { ... }

// Methods use descriptive names
public function getRequestBean(array $_data) { ... }
```

### Development Workflow

1. **Setup Environment**
   ```bash
   # Install dependencies
   composer install
   
   # Set up database
   mysql -u root -p < kade/der/kade_full_import.sql
   
   # Configure .env file
   ```

2. **Development Process**
   ```bash
   # Check PHP syntax
   php -l kade/shared/class/controller/VehicleRegCtrl.class.php
   
   # Test database connection
   curl -I http://kade.test/
   
   # Validate UTF-8 headers
   curl -I http://kade.test/cad_veiculo/
   ```

3. **Code Validation**
   ```bash
   # Lint modified files before commit
   php -l kade/shared/class/dao/AddressDAO.class.php
   
   # Test main application flows
   # - Customer registration
   # - Vehicle registration  
   # - CEP lookup
   # - Login/logout
   ```

### Key Features

#### Customer Registration
- Supports both individual (CPF) and corporate (CNPJ) customers
- Email confirmation with MD5-based verification
- CAPTCHA protection against bots
- Address autocomplete via CEP lookup

#### Vehicle Registration
- SMS integration for mobile submissions
- Real-time CEP lookup with ViaCEP fallback
- IP-based security checks for SMS
- Comprehensive validation

#### Address Management
- Local database CEP lookup via stored procedure `procurar_cep`
- Fallback to ViaCEP public API
- Auto-correction to 8-digit numeric format

## Configuration

### Environment Variables (`.env`)

```env
# Database Configuration
DBKADE_DEV_USER=root
DBKADE_DEV_PASSWORD=
DBKADE_DEV_HOST=localhost
DBKADE_DEV_DATABASE=kadecaminh
DBKADE_DEV_PORT=3306

DBCEP_DEV_USER=root
DBCEP_DEV_PASSWORD=
DBCEP_DEV_HOST=localhost
DBCEP_DEV_DATABASE=kadecaminh_1
DBCEP_DEV_PORT=3306

DBKADE_PRD_USER=
DBKADE_PRD_PASSWORD=
DBKADE_PRD_HOST=
DBKADE_PRD_DATABASE=
DBKADE_PRD_PORT=3306

DBCEP_PRD_USER=
DBCEP_PRD_PASSWORD=
DBCEP_PRD_HOST=
DBCEP_PRD_DATABASE=
DBCEP_PRD_PORT=3306

# SMTP Configuration
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASSWORD=
SMTP_AUTH=true
```

### Properties Configuration

`kade/shared/class/util/Properties.class.php` handles:
- Environment detection (DEV/PRD based on HTTP_HOST)
- Database connection settings
- SMS server configuration
- Job expiration settings

### Database Configuration

Key tables:
- `users`: System user accounts
- `persons`: Person/Entity data (CPF/CNPJ)
- `clients`: Client mappings
- `addresses`: Address data with CEP lookup
- `vehicles_traveling`: Vehicle registration records
- `vehicle_types`: Vehicle type catalog

## API Reference

### Controllers

#### CustumerRegCtrl
- **Actions**: `save`, `saveInter`, `edit`, `getCaptcha`
- **Endpoints**: `cad_cliente/` (web), SMS endpoints
- **Features**: Registration, editing, email confirmation

#### VehicleRegCtrl
- **Actions**: `save`, `saveSMS`, `getCaptcha`
- **Endpoints**: `cad_veiculo/` (web), SMS endpoints
- **Features**: Vehicle registration, mobile support

### DAO Methods

#### AddressDAO
- `getAddressByZipCode($dbConfig, $address)`: CEP lookup with fallback
- `loadDBConfigCep()`: Load CEP database configuration
- `getListUf()`: Return Brazilian state list

## Troubleshooting

### Common Issues

#### Issue: JSON Response Corruption
**Symptoms**: "Unexpected token '<'" in frontend JSON
**Cause**: PHP warnings/notices printed before JSON output
**Solution**:
```bash
# Check problematic files
php -l kade/shared/class/controller/CustumerRegCtrl.class.php

# Review recent changes for:
# - Uninitialized variables
# - Missing isset() checks
# - Global output interference
```

#### Issue: CEP Lookup Failures
**Symptoms**: Database connection errors during address lookup
**Cause**: `dbcep_dev` configuration mismatch
**Solution**:
```php
// Check Properties.class.php
// Update dbcep_dev.* keys to match your Laragon credentials
```

#### Issue: UTF-8 Character Problems
**Symptoms**: Accented characters appearing as "?" or garbled text
**Cause**: Incorrect file encoding
**Solution**:
- Ensure all .php files saved as UTF-8 without BOM
- Verify Apache/Laragon charset headers
- Use `mb_internal_encoding('UTF-8')` consistently

### Development Tips

1. **Anti-Bot Measures**
   - Honeypot field (`hp_name`)
   - Minimum 3-second form fill time (`form_ts`)
   - CAPTCHA with 5-minute expiry

2. **Security**
   - All passwords should be encrypted in production
   - Use environment variables for credentials
   - Validate all external inputs

3. **Performance**
   - Use output buffering to prevent warnings
   - Local CEP lookup reduces API dependency
   - Database connection pooling for frequent queries

## Future Improvements

### Suggested Enhancements

1. **Modernization**
   - Replace PHP with a modern framework (Laravel, Symfony)
   - Implement RESTful API
   - Add TypeScript/JavaScript frontend

2. **Security**
   - Implement OAuth 2.0 for authentication
   - Add rate limiting
   - Integrate reCAPTCHA/hCaptcha

3. **Database**
   - Convert to PostgreSQL/MySQL 8+
   - Implement connection pooling
   - Add database migration tools

4. **Frontend**
   - Replace Tailwind with modern framework
   - Add responsive design improvements
   - Implement PWA capabilities

## Contributing

### Local Development Setup

1. **Prerequisites**
   - Git
   - Laragon or XAMPP
   - MySQL client

2. **Repository Setup**
   ```bash
   # Clone the repository
   git clone <repository-url>
   cd php-kade
   
   # Install local web server shortcut
   cmd /c mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade
   ```

3. **Database Setup**
   ```bash
   # Import the database
   mysql -u root -p < kade/der/kade_full_import.sql
   
   # Set up environment
   cp .env.example .env
   # Edit .env with your credentials
   ```

4. **Testing & Validation**
   ```bash
   # Run PHP lint on all modified files
   find kade/shared/class -name "*.class.php" -exec php -l {} \;
   
   # Manual testing
   # 1. Test customer registration flow
   # 2. Test vehicle registration
   # 3. Test CEP lookup functionality
   # 4. Test login/logout
   ```

### Code Quality Standards

- **Linting**: Always run `php -l` on modified files
- **Validation**: Test form submissions thoroughly
- **Security**: Review for SQL injection and XSS vulnerabilities
- **Performance**: Profile database queries and API calls
- **Documentation**: Update README and comments for new code

### Commit Guidelines

```commit
<type>(<scope>): <description>

<body>

<footer>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code formatting (no functional changes)
- `refactor`: Code restructuring
- `test`: Test additions/modifications
- `chore`: Maintenance tasks

### Code of Conduct

1. **Be Respectful**: Treat all contributors with courtesy
2. **Code Quality**: Maintain high standards and consistency
3. **Testing**: Never commit without testing changes
4. **Documentation**: Update documentation for all changes
5. **Security**: Prioritize security and privacy concerns

## License

This project is part of the KADE Caminhões application. Please refer to the main application license for usage terms.

## Support

For technical issues, contact the development team at `contato@kadecaminhoes.com.br`.

For documentation questions, please refer to this guide or contact the technical writers.
