# GameZone Marketplace

A professional multi-language gaming marketplace and top-up platform built with Laravel 10, Bootstrap 5, and Alpine.js.

## Features

- 🎮 **Multi-Game Support** - Support for popular games like PUBG Mobile, Free Fire, Mobile Legends, etc.
- 💳 **Multiple Payment Methods** - Stripe, PayPal integration
- 🌍 **Multi-Language** - English and Arabic support with RTL layout
- 🌙 **Dark/Light Theme** - User preference with system detection
- 📱 **Responsive Design** - Mobile-first approach with Bootstrap 5
- 🔐 **Firebase Authentication** - Google, GitHub, Email/Password authentication
- 👨‍💼 **Admin Panel** - Complete CRUD operations with analytics
- 📊 **Real-time Notifications** - Laravel Echo + Pusher integration
- 🎫 **Support System** - Ticket-based customer support
- 📈 **Analytics Dashboard** - Sales, users, and performance metrics

## Technology Stack

- **Backend**: Laravel 10, MySQL, Redis
- **Frontend**: Blade Templates, Bootstrap 5, Alpine.js
- **Authentication**: Firebase Auth + Laravel Sanctum
- **Payments**: Stripe, PayPal
- **Real-time**: Laravel Echo, Pusher
- **Storage**: Firebase Storage
- **Build Tools**: Vite
- **Testing**: PHPUnit, Laravel Dusk

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Redis (optional, for caching)

### Step 1: Clone the Repository

\`\`\`bash
git clone https://github.com/your-username/gamezone-marketplace.git
cd gamezone-marketplace
\`\`\`

### Step 2: Install Dependencies

\`\`\`bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
\`\`\`

### Step 3: Environment Configuration

\`\`\`bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
\`\`\`

Edit `.env` file with your configuration:

\`\`\`env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gamezone_marketplace
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Firebase Configuration
FIREBASE_PROJECT_ID=your-firebase-project-id
FIREBASE_CREDENTIALS_PATH=storage/firebase-credentials.json

# Firebase Web Config
VITE_FIREBASE_API_KEY=your-web-api-key
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-firebase-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789
VITE_FIREBASE_APP_ID=1:123456789:web:abcdef123456

# Pusher Configuration
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=mt1

# Payment Gateways
STRIPE_KEY=pk_test_your_stripe_publishable_key
STRIPE_SECRET=sk_test_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_stripe_webhook_secret

PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox
\`\`\`

### Step 4: Firebase Setup

1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com/)
2. Enable Authentication with Email/Password, Google, and GitHub providers
3. Create a Firebase Storage bucket
4. Download the service account key JSON file
5. Place it at `storage/firebase-credentials.json`

\`\`\`bash
# Create storage directory if it doesn't exist
mkdir -p storage
\`\`\`

### Step 5: Database Setup

\`\`\`bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
\`\`\`

This will create:
- Roles and permissions
- Admin user (admin@gamezone.com / password)
- Sample games and services
- Demo game accounts

### Step 6: Storage Setup

\`\`\`bash
# Create symbolic link for public storage
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
\`\`\`

### Step 7: Build Assets

\`\`\`bash
# Development build
npm run dev

# Production build
npm run build
\`\`\`

### Step 8: Start the Application

\`\`\`bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite dev server (for development)
npm run dev
\`\`\`

Visit `http://localhost:8000` to access the application.

## Default Credentials

### Admin Access
- **Email**: admin@gamezone.com
- **Password**: password

### Test User
- **Email**: user@gamezone.com
- **Password**: password

## API Documentation

The application provides a comprehensive REST API. Import the Postman collection from `docs/GameZone-API.postman_collection.json` for complete API documentation.

### Base URL
\`\`\`
http://localhost:8000/api/v1
\`\`\`

### Authentication
The API uses Laravel Sanctum for authentication. Include the bearer token in the Authorization header:

\`\`\`
Authorization: Bearer your-token-here
\`\`\`

### Key Endpoints

#### Public Endpoints
- `GET /games` - List all games
- `GET /games/{id}` - Get game details
- `GET /services` - List all services
- `GET /accounts` - List all game accounts
- `POST /contact` - Submit contact form

#### Authenticated Endpoints
- `GET /orders` - List user orders
- `POST /orders` - Create new order
- `GET /cart` - Get cart contents
- `POST /cart` - Add item to cart
- `GET /support-tickets` - List support tickets

#### Admin Endpoints
- `GET /admin/users` - List users
- `POST /admin/users` - Create user
- `GET /admin/orders` - List all orders
- `PATCH /admin/orders/{id}/status` - Update order status

## Testing

### Unit Tests

\`\`\`bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
\`\`\`

### Browser Tests (Laravel Dusk)

\`\`\`bash
# Install Chrome driver
php artisan dusk:install

# Run Dusk tests
php artisan dusk

# Run specific Dusk test
php artisan dusk tests/Browser/AuthenticationTest.php
\`\`\`

### Test Database

Tests use a separate SQLite database. Configure in `phpunit.xml`:

\`\`\`xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
\`\`\`

## Deployment

### Production Environment

1. **Server Requirements**
   - PHP 8.1+ with required extensions
   - MySQL 8.0+
   - Redis (recommended)
   - SSL certificate

2. **Environment Configuration**
   \`\`\`bash
   # Set production environment
   APP_ENV=production
   APP_DEBUG=false
   
   # Configure production database
   DB_HOST=your-production-host
   DB_DATABASE=your-production-database
   
   # Set production URLs
   APP_URL=https://yourdomain.com
   \`\`\`

3. **Build and Deploy**
   \`\`\`bash
   # Install production dependencies
   composer install --optimize-autoloader --no-dev
   
   # Build production assets
   npm run build
   
   # Cache configuration
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Run migrations
   php artisan migrate --force
   \`\`\`

### GitHub Actions CI/CD

The repository includes GitHub Actions workflows:

- **CI Pipeline** (`.github/workflows/ci.yml`): Runs tests on every push
- **Deployment Pipeline** (`.github/workflows/deploy.yml`): Deploys to production on main branch

Configure the following secrets in your GitHub repository:
- `SERVER_HOST` - Production server IP
- `SERVER_USER` - SSH username
- `SERVER_KEY` - SSH private key
- `DB_PASSWORD` - Production database password

## Project Structure

\`\`\`
gamezone-marketplace/
├── app/
│   ├── Http/Controllers/          # API and web controllers
│   ├── Models/                    # Eloquent models
│   ├── Policies/                  # Authorization policies
│   ├── Services/                  # Business logic services
│   └── Events/                    # Event classes
├── database/
│   ├── migrations/                # Database migrations
│   ├── seeders/                   # Database seeders
│   └── factories/                 # Model factories
├── resources/
│   ├── views/                     # Blade templates
│   ├── js/                        # JavaScript files
│   ├── sass/                      # SCSS stylesheets
│   └── lang/                      # Language files
├── routes/
│   ├── web.php                    # Web routes
│   ├── api.php                    # API routes
│   └── channels.php               # Broadcast channels
├── tests/
│   ├── Feature/                   # Feature tests
│   ├── Unit/                      # Unit tests
│   └── Browser/                   # Dusk browser tests
└── docs/                          # Documentation
\`\`\`

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation for API changes
- Use conventional commit messages

## Security

### Security Features

- CSRF protection on all forms
- SQL injection prevention with Eloquent ORM
- XSS protection with Blade templating
- Rate limiting on API endpoints
- Input validation and sanitization
- Secure password hashing with bcrypt

### Reporting Security Issues

Please report security vulnerabilities to security@gamezone.com. Do not create public GitHub issues for security problems.

## Performance

### Optimization Features

- Database query optimization with eager loading
- Redis caching for frequently accessed data
- Asset minification and compression
- CDN integration for static assets
- Image optimization and lazy loading

### Monitoring

- Application performance monitoring with Laravel Telescope (development)
- Error tracking with Laravel Log
- Database query monitoring
- Real-time analytics dashboard

## Support

### Documentation
- [API Documentation](docs/api.md)
- [Deployment Guide](docs/deployment.md)
- [Troubleshooting](docs/troubleshooting.md)

### Community
- [Discord Server](https://discord.gg/gamezone)
- [GitHub Discussions](https://github.com/your-username/gamezone-marketplace/discussions)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/gamezone)

### Commercial Support
For commercial support and custom development, contact us at support@gamezone.com

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [Bootstrap](https://getbootstrap.com) - CSS framework
- [Alpine.js](https://alpinejs.dev) - JavaScript framework
- [Firebase](https://firebase.google.com) - Authentication and storage
- [Stripe](https://stripe.com) - Payment processing
- [Pusher](https://pusher.com) - Real-time features

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history.

---

**GameZone Marketplace** - Built with ❤️ for the gaming community
