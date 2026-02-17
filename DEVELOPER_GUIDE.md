# Faveo Helpdesk - Developer Guide

Welcome to the Faveo Helpdesk developer guide! This document provides comprehensive information for full-stack developers who want to contribute to or extend the Faveo Helpdesk system.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Architecture Overview](#architecture-overview)
3. [Development Environment Setup](#development-environment-setup)
4. [Project Structure](#project-structure)
5. [Development Workflow](#development-workflow)
6. [Testing](#testing)
7. [API Development](#api-development)
8. [Frontend Development](#frontend-development)
9. [Database Management](#database-management)
10. [Plugin Development](#plugin-development)
11. [Code Standards](#code-standards)
12. [Debugging](#debugging)
13. [Contributing](#contributing)

## Getting Started

Faveo Helpdesk is a comprehensive customer support platform built with Laravel 9.46+ and PHP 8.1+. It provides businesses with an automated helpdesk system to manage customer support efficiently.

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP**: Version 8.1 or higher
- **Database**: MySQL 8.0.x or MariaDB 10.6.x
- **Web Server**: Apache / IIS / Nginx
- **Composer**: Latest version for dependency management
- **Node.js & NPM**: For frontend asset compilation
- **Git**: For version control

### Required PHP Extensions

- Imap
- Mbstring
- Mcrypt
- OpenSSL
- PDO
- Tokenizer
- XML
- Zip

## Architecture Overview

### Technology Stack

**Backend:**
- Laravel 9.46+ (PHP Framework)
- MySQL/MariaDB (Database)
- JWT Authentication (tymon/jwt-auth)
- Laravel Socialite (OAuth)
- Redis/Predis (Queue & Cache)

**Frontend:**
- Laravel Blade (Templating)
- Vue.js & JavaScript
- Laravel Mix with Webpack
- AdminLTE Theme
- CKEditor (Rich Text)
- DataTables (yajra/laravel-datatables)

**Key Features:**
- RESTful API (v1 & v2)
- Multi-role authentication (Admin, Agent, Client)
- Plugin system for extensibility
- Queue-based email processing
- Knowledge Base module
- Workflow automation engine
- Multi-channel notifications

### MVC Structure

Faveo follows Laravel's MVC pattern:

- **Models**: Located in `app/Model/` - Eloquent ORM models for database interaction
- **Views**: Located in `resources/views/` - Blade templates for UI
- **Controllers**: Located in `app/Http/Controllers/` - Organized by user role
  - `Admin/` - Administrative functions
  - `Agent/` - Agent/staff operations
  - `Client/` - Customer-facing operations
  - `Common/` - Shared functionality

## Development Environment Setup

### 1. Clone the Repository

```bash
git clone https://github.com/fmansifilho/Faveo.git
cd Faveo
```

### 2. Validate Your Environment (Optional but Recommended)

Run the environment checker to ensure your system meets all requirements:

```bash
php dev-check.php
```

This script will check:
- PHP version and required extensions
- Composer, Node.js, NPM, and Git installation
- Directory permissions
- Dependency installation status
- Environment configuration

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install Node Dependencies

```bash
npm install
```

### 5. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and configure your database and other settings:

```env
APP_NAME=Faveo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=faveo
DB_USERNAME=root
DB_PASSWORD=your_password

# Queue Configuration
QUEUE_CONNECTION=sync  # Use 'redis' for production

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed the Database (Optional)

```bash
php artisan db:seed
```

### 9. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run prod
```

Watch for changes during development:
```bash
npm run watch
```

### 10. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### 11. Configure Pretty URLs

Ensure your web server is configured for pretty URLs (mod_rewrite for Apache).

## Project Structure

### Core Directories

```
/app
├── Api/              # API Controllers (v1 & v2)
├── Console/          # Artisan commands
├── Events/           # Event classes
├── Exceptions/       # Custom exception handlers
├── Http/
│   ├── Controllers/  # MVC Controllers
│   ├── Middleware/   # HTTP middleware
│   └── Requests/     # Form request validation
├── Jobs/             # Queue job handlers
├── Listeners/        # Event listeners
├── Model/            # Eloquent models
│   └── helpdesk/    # Helpdesk-specific models
├── Plugins/          # Plugin system
├── Policies/         # Authorization policies
└── Providers/        # Service providers

/resources
├── js/               # JavaScript files
├── css/              # Stylesheets
├── lang/             # Translation files
└── views/            # Blade templates
    ├── themes/       # Theme templates
    ├── installer/    # Installer views
    └── ...

/database
├── migrations/       # Database migrations (100+)
├── seeders/          # Database seeders
└── factories/        # Model factories

/routes
├── web.php          # Web routes
├── api.php          # API routes
├── console.php      # Console commands
├── installer.php    # Installer routes
└── update.php       # Update routes

/tests
├── Unit/            # Unit tests
├── Feature/         # Feature tests
└── DuskTestCase.php # Browser tests

/public
├── lb-faveo/        # Frontend assets
├── uploads/         # File uploads
└── index.php        # Entry point

/config              # Configuration files
/storage             # Logs, cache, uploads
/vendor              # Composer dependencies
```

### Key Models

Located in `app/Model/helpdesk/`:

- `Ticket` - Support tickets
- `Agent` - Support agents
- `Department` - Organizational departments
- `Category` - Ticket categories
- `Priority` - Priority levels
- `Status` - Ticket statuses
- `Workflow` - Automation workflows
- `Sla_plan` - SLA policies
- `Kb_article` - Knowledge base articles
- `User_assign_organization` - Organization assignments

## Development Workflow

### Git Flow

This project uses [git-flow](https://github.com/nvie/gitflow) branching model:

- `master` - Production-ready code
- `development` - Integration branch
- `feature/*` - New features
- `hotfix/*` - Production fixes
- `release/*` - Release preparation

### Creating a New Feature

1. Create a feature branch from `development`:
```bash
git checkout development
git pull origin development
git checkout -b feature/your-feature-name
```

2. Make your changes following [code standards](#code-standards)

3. Write tests for your changes

4. Commit with meaningful messages:
```bash
git add .
git commit -m "Add feature: brief description"
```

5. Push to your fork:
```bash
git push origin feature/your-feature-name
```

6. Create a pull request to `development` branch

### Code Review Process

All pull requests must:
- Pass StyleCI checks (PSR-2 coding standard)
- Include relevant tests
- Follow SemVer for version changes
- Have a coherent commit history
- Include documentation updates if needed

## Testing

### Running Tests

Faveo uses PHPUnit for testing:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Structure

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test example functionality
     */
    public function test_example_feature()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
```

### Browser Testing with Dusk

```bash
# Install Dusk
php artisan dusk:install

# Run Dusk tests
php artisan dusk
```

### Database Testing

Use the `RefreshDatabase` trait to reset database between tests:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_ticket()
    {
        // Test code here
    }
}
```

## API Development

### API Versions

Faveo provides two API versions:

- **v1** (`/api/v1/*`) - Legacy API
- **v2** (`/api/v2/*`) - Current API

### Authentication

API uses JWT (JSON Web Tokens) for authentication:

```bash
# Get token
POST /api/v1/authenticate
{
    "email": "user@example.com",
    "password": "password"
}

# Use token in requests
Authorization: Bearer {your-token}
```

### API Controllers

Located in `app/Api/`:

```
/app/Api
├── v1/
│   ├── ApiController.php
│   ├── TokenAuthController.php
│   └── ...
└── v2/
    └── ...
```

### Creating a New API Endpoint

1. Create controller method:

```php
<?php

namespace App\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketApiController extends Controller
{
    /**
     * Get ticket by ID
     */
    public function show($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
```

2. Add route in `routes/api.php`:

```php
Route::middleware('auth:api')->group(function () {
    Route::get('/v2/tickets/{id}', 'Api\v2\TicketApiController@show');
});
```

### API Best Practices

- Always return JSON responses
- Use proper HTTP status codes
- Include error handling
- Validate input data
- Use API versioning for breaking changes
- Document endpoints thoroughly

## Frontend Development

### Asset Compilation

Faveo uses Laravel Mix for asset compilation:

```javascript
// webpack.mix.js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');
```

### Build Commands

```bash
# Development build
npm run dev

# Production build (minified)
npm run prod

# Watch for changes
npm run watch

# Hot module replacement
npm run hot
```

### Blade Templates

Create reusable components:

```blade
{{-- resources/views/components/alert.blade.php --}}
<div class="alert alert-{{ $type }}">
    {{ $slot }}
</div>

{{-- Usage --}}
<x-alert type="success">
    Operation completed successfully!
</x-alert>
```

### JavaScript Integration

Include JavaScript in Blade templates:

```blade
@section('scripts')
<script>
    $(document).ready(function() {
        // Your JavaScript code
    });
</script>
@endsection
```

### Using DataTables

Faveo uses Yajra DataTables for server-side processing:

```javascript
$('#tickets-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/api/tickets',
    columns: [
        { data: 'id', name: 'id' },
        { data: 'title', name: 'title' },
        { data: 'status', name: 'status' }
    ]
});
```

## Database Management

### Creating Migrations

```bash
php artisan make:migration create_tickets_table
```

Migration example:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('status_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
```

### Model Relationships

```php
<?php

namespace App\Model\helpdesk;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['title', 'description', 'user_id', 'status_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function status()
    {
        return $this->belongsTo('App\Model\helpdesk\Status');
    }

    public function replies()
    {
        return $this->hasMany('App\Model\helpdesk\TicketReply');
    }
}
```

### Query Optimization

Use eager loading to prevent N+1 queries:

```php
// Bad - N+1 query problem
$tickets = Ticket::all();
foreach ($tickets as $ticket) {
    echo $ticket->user->name; // Queries user for each ticket
}

// Good - Eager loading
$tickets = Ticket::with('user', 'status')->get();
foreach ($tickets as $ticket) {
    echo $ticket->user->name; // No additional queries
}
```

## Plugin Development

Faveo supports a plugin architecture for extending functionality.

### Plugin Structure

```
/app/Plugins/YourPlugin
├── Controllers/
├── Models/
├── Views/
├── Routes/
│   └── web.php
├── Config/
│   └── config.php
├── Database/
│   └── migrations/
└── PluginServiceProvider.php
```

### Creating a Plugin

1. Create plugin directory structure
2. Create service provider:

```php
<?php

namespace App\Plugins\YourPlugin;

use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/Views', 'your-plugin');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
    }

    public function register()
    {
        // Register plugin services
    }
}
```

3. Register in `config/app.php`:

```php
'providers' => [
    // ...
    App\Plugins\YourPlugin\PluginServiceProvider::class,
],
```

### Plugin Best Practices

- Follow PSR-2 coding standards
- Include comprehensive documentation
- Write tests for plugin functionality
- Use events and listeners for integration
- Avoid modifying core files
- Use configuration files for settings

## Code Standards

### PSR-2 Coding Standard

Faveo follows [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md):

- Use 4 spaces for indentation
- Opening braces for classes and methods on new line
- Control structure braces on same line
- Property and constant visibility required

### Laravel Best Practices

- Use Eloquent ORM instead of raw queries
- Use migration and seeders for database changes
- Follow RESTful conventions for routes
- Use form requests for validation
- Use events and listeners for decoupled code
- Use queues for long-running tasks

### Code Formatting

StyleCI automatically checks and fixes code style issues. You can also use Laravel Pint:

```bash
./vendor/bin/pint
```

### Naming Conventions

- **Controllers**: Singular, PascalCase (e.g., `TicketController`)
- **Models**: Singular, PascalCase (e.g., `Ticket`)
- **Tables**: Plural, snake_case (e.g., `tickets`)
- **Migrations**: Descriptive (e.g., `create_tickets_table`)
- **Methods**: camelCase (e.g., `createTicket`)
- **Variables**: camelCase (e.g., `$ticketId`)

## Debugging

### Laravel Debugbar

Enable in `.env`:

```env
DEBUGBAR_ENABLED=true
```

Access at bottom of pages for query analysis, performance metrics, and more.

### Laravel Telescope

For advanced debugging (if installed):

```bash
php artisan telescope:install
php artisan migrate
```

Access at `/telescope`

### Logging

Use Laravel's logging system:

```php
use Illuminate\Support\Facades\Log;

Log::info('Ticket created', ['ticket_id' => $ticket->id]);
Log::warning('Potential issue detected');
Log::error('Error occurred', ['exception' => $e->getMessage()]);
```

View logs in `storage/logs/laravel.log`

### Error Reporting

Faveo uses [Bugsnag](https://www.bugsnag.com/) for production error tracking. Disable during development:

```env
APP_ENV=local
APP_DEBUG=true
```

Or disable from admin panel: "Error logs and debugging" option.

### Artisan Tinker

Interactive shell for debugging:

```bash
php artisan tinker

>>> $ticket = App\Model\helpdesk\Ticket::find(1)
>>> $ticket->title
>>> $users = App\User::all()
```

## Contributing

### Before Submitting a Pull Request

1. **Follow the coding standards** - PSR-2 and Laravel conventions
2. **Write tests** - Ensure existing tests pass and add new ones
3. **Update documentation** - If you change behavior or API
4. **Use semantic commits** - Clear, meaningful commit messages
5. **Squash commits** - If you have multiple intermediate commits
6. **Rebase if needed** - Avoid merge conflicts

### Contribution Guidelines

Please review the [CONTRIBUTING.md](CONTRIBUTING.md) file for detailed guidelines.

### Issue Reporting

Report issues on [GitHub Issues](https://github.com/ladybirdweb/faveo-helpdesk/issues):

- Provide clear title and description
- Include steps to reproduce
- Specify environment (PHP version, database, OS)
- Include error messages and logs
- Mention what you expected to happen

### Pull Request Process

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Ensure all tests pass
6. Update documentation
7. Submit pull request to `development` branch
8. Wait for code review
9. Address feedback if any
10. Merge after approval

## Additional Resources

### Documentation

- [Faveo User Manual](https://github.com/ladybirdweb/faveo-helpdesk/wiki)
- [Faveo API Documentation](https://github.com/ladybirdweb/faveo-helpdesk/wiki/API-Documentation)
- [Faveo Event List](https://github.com/ladybirdweb/faveo-helpdesk/wiki/Faveo-Event-List)
- [Plugin Creation Guide](https://github.com/ladybirdweb/faveo-helpdesk/wiki/Faveo-Plugin-creation-guide)
- [Laravel 9 Documentation](https://laravel.com/docs/9.x)

### Community

- [LinkedIn Group](https://www.linkedin.com/groups/8429668)
- [Slack Community](https://faveocommunity.slack.com/)
- [GitHub Discussions](https://github.com/ladybirdweb/faveo-helpdesk/issues)

### Support

- **Community Edition**: GitHub Issues
- **Commercial Support**: [Contact Form](http://www.faveohelpdesk.com/contact-us/)
- **Security Issues**: support@faveohelpdesk.com

## License

Faveo Helpdesk is open-source software licensed under the [OSL-3.0 License](LICENSE).

---

**Happy Coding! 🚀**

If you have questions or need help, don't hesitate to reach out to the community or check the existing documentation.
