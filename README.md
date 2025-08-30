# ERP Store Management System

A comprehensive Enterprise Resource Planning (ERP) system built with Laravel 11 and Livewire 3, designed for warehouse and inventory management with multi-role access control.

## 🚀 Features

### Core Functionality
- **Inventory Management**: Complete item tracking with categories and subcategories
- **Receiving Management**: Streamlined goods receiving process
- **Requisition System**: Internal request and approval workflow
- **Trust Management**: Trust-based item allocation system
- **Transfer Management**: Inter-department item transfers
- **Supplier Management**: Vendor relationship and performance tracking
- **Department Management**: Organizational structure management
- **User Role Management**: Multi-level access control system

### Advanced Features
- **Real-time Monitoring**: Livewire-powered dynamic interfaces
- **Reporting & Analytics**: Comprehensive inventory and financial reports
- **Export Capabilities**: PDF and Excel export functionality
- **Multi-language Support**: Arabic and English localization
- **Backup Management**: Automated system backup and recovery
- **Document Search**: Advanced search across all transactions
- **Dashboard Analytics**: Real-time insights and metrics

## 🛠️ Technology Stack

### Backend
- **PHP 8.2+** with **Laravel 11.31**
- **Livewire 3.6** for reactive components
- **MySQL 8.0** database
- **Redis** for caching and sessions
- **Laravel Sanctum** for API authentication

### Frontend
- **Tailwind CSS 3.4** for styling
- **Alpine.js** for interactive components
- **SweetAlert2** for enhanced user notifications
- **Vite** for asset compilation

### Additional Packages
- **Laravel Excel** for data import/export
- **Laravel DomPDF** for PDF generation
- **Laravel Backup** for system backup management
- **Laravel Localization** for multi-language support

## 📋 Requirements

- PHP 8.2 or higher
- Composer 2.0+
- Node.js 18+ and npm
- MySQL 8.0+
- Redis (optional, for enhanced performance)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd ERP-store
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_store
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets
```bash
npm run build
```

### 6. Start the Application
```bash
php artisan serve
```

## 🐳 Docker Setup

The project includes Docker configuration for easy deployment:

```bash
# Start all services
docker-compose up -d

# Access the application
open http://localhost:8000
```

## 👥 User Roles & Permissions

### System Administrator
- Full system access
- User and role management
- System backup management
- All warehouse operations

### Warehouse Manager
- Inventory management
- Receiving and requisition oversight
- Supplier management
- Department coordination

### Receiving Clerk
- Goods receiving
- Receiving search and reports
- Basic inventory operations

### Requisition Clerk
- Requisition creation
- Transfer management
- Basic reporting access

### Trust Clerk
- Trust-based allocations
- Trust management
- Related reporting

### Inventory Controller
- Inventory monitoring
- Item reports
- Stock level management

### Store Keeper
- Basic inventory operations
- Item monitoring
- Limited reporting access

### Department Manager
- Department-specific operations
- Department reporting
- Basic inventory access

### Accountant
- Financial reporting
- Inventory valuation
- Audit trail access

### Auditor
- Read-only access to reports
- Audit trail review
- Compliance monitoring

## 📊 Database Structure

The system includes the following main entities:
- **Users** with role-based access control
- **Items** with category and subcategory classification
- **Departments** for organizational structure
- **Suppliers** for vendor management
- **Receivings** for goods receiving
- **Requisitions** for internal requests
- **Trusts** for trust-based allocations
- **Categories** and **Subcategories** for item classification

## 🔧 Development

### Available Commands
```bash
# Development server with all services
composer run dev

# Code quality checks
composer run phpcs
composer run phpcbf

# Testing
php artisan test
```

### Code Style
The project follows PSR-12 coding standards with automatic formatting available through the provided scripts.

## 🌐 Localization

The application supports multiple languages:
- **English** (default)
- **Arabic** (RTL support)

Language switching is available through the `LocaleController`.

## 📈 Reporting & Analytics

### Available Reports
- **Inventory Reports**: Stock levels, movements, and valuations
- **Department Reports**: Department-specific analytics
- **Supplier Reports**: Vendor performance and analysis
- **Item Reports**: Detailed item tracking and history
- **Export Reports**: Customizable data export options

### Export Formats
- **PDF**: Using Laravel DomPDF
- **Excel**: Using Laravel Excel

## 🔒 Security Features

- **Role-based Access Control**: Granular permissions system
- **Authentication**: Laravel Breeze with email verification
- **CSRF Protection**: Built-in Laravel security
- **Input Validation**: Comprehensive request validation
- **SQL Injection Protection**: Eloquent ORM security
- **XSS Protection**: Blade template escaping

## 📝 API Documentation

The system includes Laravel Sanctum for API authentication, enabling:
- RESTful API endpoints
- Token-based authentication
- Rate limiting
- CORS configuration

## 🧪 Testing

The project includes comprehensive testing setup:
- **PHPUnit** for backend testing
- **Feature tests** for user workflows
- **Unit tests** for individual components
- **Database testing** with SQLite

## 📦 Deployment

### Production Considerations
- Configure production database
- Set up proper caching (Redis recommended)
- Configure queue workers for background jobs
- Set up automated backups
- Configure proper logging
- Set up SSL certificates

### Environment Variables
Ensure all production environment variables are properly configured in your `.env` file.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code quality checks
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support and questions:
- Check the Laravel documentation
- Review the Livewire documentation
- Open an issue in the repository

## 🔄 Changelog

### Version 1.0.0
- Initial release with core ERP functionality
- Multi-role access control system
- Complete inventory management
- Reporting and analytics
- Multi-language support
- Docker deployment configuration

---

**Built with ❤️ using Laravel and Livewire**
