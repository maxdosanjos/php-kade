# Quick Reference Guide

## Installation & Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Setup Web Server (Laragon)
```cmd
cmd /c mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade
```

### 3. Import Database
```sql
mysql -u root -p --default-character-set=utf8mb4 < kade/der/kade_full_import.sql
```

### 4. Configure Environment
```bash
cp .env.example .env
# Edit .env with your database credentials
```

## Core Commands

### Development
```bash
# Run PHP built-in server
php -S localhost:8000 -t kade

# Check syntax of specific files
php -l kade/shared/class/controller/VehicleRegCtrl.class.php
php -l kade/shared/class/dao/AddressDAO.class.php
```

### Validation
```bash
# Test database connectivity
mysql -u root -p -e "SHOW DATABASES;"

# Check UTF-8 headers
curl -I http://kade.test/
curl -I http://kade.test/cad_veiculo/

# Test CEP lookup
curl -X POST -H "Content-Type: application/json" -d '{"cep": "01310100"}' http://kade.test/api/cep
```

## Key Classes & Methods

### Controllers
```php
// Customer Registration
CustumerRegCtrl::onSaveWeb()   // Web form handling
CustumerRegCtrl::onSaveInter()  // AJAX/interval handling

// Vehicle Registration  
VehicleRegCtrl::onSaveWeb()     // Web form handling
VehicleRegCtrl::onSaveSMS()     // SMS handling
```

### DAO Methods
```php
// Address Lookup
$addressDAO = AddressDAO::getInstance();
$addressDAO->getAddressByZipCode($db, $address);

// Configuration
$addressDAO->loadDBConfigCep();  // Load CEP DB config
```

### Utilities
```php
// Database
$db = DataBase::getInstance($this->getDbConfig());

// Validation
Validation::isCPF($cpf);
Validation::isCNPJ($cnpj);
Validation::isEMail($email);

// Utilities
Util::getClientIP();
DateTimeCustom::format('Y-m-d H:i:s');
```

## File Structure

```
PROJECT ROOT/
├── kade/
│   ├── index.php                    # Entry point
│   ├── view/                       # Views
│   │   ├── cad_veiculo.php          # Vehicle registration
│   │   ├── cad_cliente.php          # Customer registration
│   │   └── maps.php                 # Map view (default)
│   └── shared/
│       ├── class/                  # Core classes
│       │   ├── controller/         # Controllers
│       │   ├── dao/                # Data Access Objects
│       │   ├── vo/                 # Value Objects
│       │   └── util/                # Utilities
│       ├── css/                    # Styles
│       └── js/                     # Scripts
│   └── der/                        # Database dumps
├── .env.example                     # Environment template
└── DOCUMENTATION.md                 # This guide
```

## Error Codes

### Common HTTP Statuses
- `200 OK`: Successful request
- `400 Bad Request`: Invalid form data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Access denied
- `500 Internal Server Error`: System error

### Application Errors
```php
// ValidationException
"Dados incompletos!"           # Incomplete data
"CPF vazio!"                   # Empty CPF
"CPF inválido!"                # Invalid CPF format
"Login já existente!"          # Duplicate login
"CPF já utilizado!"            # Duplicate CPF
"CNPJ já utilizado!"           # Duplicate CNPJ

// Database Errors
"Erro ao criar Endereço!"      # Address creation failed
"CEP não encontrado"            # CEP not found
"Conexão inválida"             # Database connection failed
```

## Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `DBKADE_DEV_USER` | Database username | `root` |
| `DBKADE_DEV_PASSWORD` | Database password | `` |
| `DBKADE_DEV_HOST` | Database host | `localhost` |
| `DBKADE_DEV_DATABASE` | Database name | `kadecaminh` |
| `DBKADE_DEV_PORT` | Database port | `3306` |

## Project Commands

### Main Entry Points
```bash
# Access the application
http://kade.test/

# Specific pages
http://kade.test/cad_veiculo/
http://kade.test/cad_cliente/
http://kade.test/login/
```

### Background Jobs
```bash
# Expire messages job
# Configure via Properties.class.php
# "job_msg_expired.interval_job_expired" => "16"
# "job_msg_expired.file_job_expired" => "cront_exp.txt"
```

## Security Features

1. **Anti-Bot Protection**
   - Honeypot field (`hp_name`)
   - Minimum 3-second form fill time
   - CAPTCHA with expiration

2. **Input Validation**
   - CPF/CNPJ format validation
   - Email format validation
   - Required field checking

3. **Authentication**
   - Session-based authentication
   - Email confirmation
   - Password hashing (where applicable)

## Database Schema (Key Tables)

### users
- id (PK)
- person_id (FK)
- login
- password
- status

### persons
- id (PK)
- type (PF/PJ)
- name
- cpf/cnpj
- email
- phone
- address_id (FK)

### addresses
- id (PK)
- cep
- street
- number
- neighborhood
- city
- state
- state_nm

### vehicles_traveling
- id (PK)
- person_contact_id (FK)
- vehicle_type_id (FK)
- address_id (FK)
- source (WEB/SMS)
- ip

## Performance Tips

1. **Query Optimization**
   - Use indexes on frequently queried columns
   - Implement connection pooling
   - Cache frequently accessed data

2. **Output Management**
   - Use output buffering to prevent warnings
   - Compress database queries
   - Implement caching for CEP lookups

3. **Memory Management**
   - Close database connections after use
   - Use weak references where appropriate
   - Monitor memory usage

## Migration Guide

### Upgrading from Legacy Versions

1. **Database Migration**
   ```sql
   -- Update database structure
   ALTER TABLE vehicles_traveling ADD COLUMN source ENUM('WEB', 'SMS') DEFAULT 'WEB';
   ALTER TABLE vehicles_traveling ADD COLUMN ip VARCHAR(45);
   ```

2. **Configuration Migration**
   ```bash
   # Update .env.example with new variables
   # Migrate existing properties to new structure
   ```

3. **Code Updates**
   ```php
   // Update controller calls
   $ctrl = new CustumerRegCtrl();
   $ctrl->onSaveWeb();  // or onSaveInter() for AJAX
   ```

## FAQ

### Q: How do I update the CEP database?
A: Update the `dbcep_dev` credentials in `.env` and restart. The system will automatically fallback to ViaCEP if local lookup fails.

### Q: How do I test the application?
A: Use `php -l` to check syntax, and manually test the main flows:
- Customer registration
- Vehicle registration  
- CEP lookup
- Login/logout

### Q: How do I fix UTF-8 issues?
A: Ensure all files are saved as UTF-8 without BOM. Check Apache/Laragon charset headers.

### Q: What are the supported browsers?
A: Modern browsers (Chrome, Firefox, Safari, Edge). Internet Explorer may have limited support.

### Q: How do I extend the application?
A: Follow the MVC pattern:
- Views: Add new `.php` files in `kade/view/`
- Controllers: Extend `AbstractCtrl` or `ViewExtCtrl`
- DAOs: Extend existing DAO classes or create new ones
