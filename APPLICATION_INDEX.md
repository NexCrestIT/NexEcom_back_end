# NexCrest E-Commerce Application - Complete Index & Study Guide

**Generated**: January 8, 2026  
**Application**: NexCrest E-Commerce Backend  
**Framework**: Laravel 12.0  
**Database**: MySQL (configured via .env)  
**Current Status**: Active Development

---

## 📋 Table of Contents

1. [Application Overview](#application-overview)
2. [Technology Stack](#technology-stack)
3. [Architecture](#architecture)
4. [Core Entities & Data Models](#core-entities--data-models)
5. [API Endpoints](#api-endpoints)
6. [File Structure](#file-structure)
7. [Database Schema](#database-schema)
8. [Authentication & Authorization](#authentication--authorization)
9. [Key Repositories](#key-repositories)
10. [Key Controllers](#key-controllers)
11. [Routes](#routes)
12. [Development Workflow](#development-workflow)
13. [Current Features](#current-features)
14. [Code Quality Standards](#code-quality-standards)

---

## 🎯 Application Overview

**NexCrest E-Commerce** is a full-featured e-commerce admin panel and API built with Laravel 12.0 and Vue 3. The application provides:

- **Admin Dashboard**: Comprehensive management interface for all e-commerce operations
- **Product Management**: Full CRUD with attributes, inventory, pricing, and discounts
- **Inventory System**: Multi-location stock tracking with movement history
- **User Management**: Admin users with role-based permissions and 2FA
- **Customer API**: Public API endpoints for customer registration, authentication, and shopping cart
- **Pricing System**: Multiple price lists, rules, and history tracking
- **Marketing Tools**: Flash sales, discounts, and product categorization

### Key Statistics
- **21 Core Models** (Admin + Customer models)
- **17 Admin Controllers** with full CRUD operations
- **6 API Controllers** for customer-facing endpoints
- **20 Repository Classes** implementing business logic
- **35+ Database Migrations**
- **Modern Vue 3 + Inertia.js Frontend** with PrimeVue & Flowbite UI

---

## 🛠️ Technology Stack

### Backend
| Technology | Version | Purpose |
|-----------|---------|---------|
| Laravel | 12.0 | Web Framework |
| PHP | ^8.2 | Language |
| Laravel Fortify | ^1.30 | Authentication |
| Spatie Permissions | ^6.23 | RBAC |
| Laravel Sanctum | ^4.2 | API Token Auth |
| Inertia.js | ^2.0 | Frontend Integration |

### Frontend
| Technology | Version | Purpose |
|-----------|---------|---------|
| Vue | 3.5.13 | UI Framework |
| TypeScript | ^5.2.2 | Type Safety |
| Tailwind CSS | 4.1.1 | Styling |
| PrimeVue | 4.4.1 | Component Library |
| Flowbite Vue | 0.2.2 | Additional Components |
| Vite | 7.0.4 | Build Tool |

### Development
| Tool | Version | Purpose |
|------|---------|---------|
| Pest PHP | 3.8 | Testing |
| Laravel Pint | ^1.24 | PHP Code Quality |
| ESLint | ^9.17.0 | JS Linting |
| Prettier | ^3.4.2 | Code Formatting |

### Database
| Component | Value |
|-----------|-------|
| Connection | MySQL |
| Host | 127.0.0.1:3306 |
| Database | nexcrest_ecommerce |
| State | Active (35+ migrations) |

---

## 🏗️ Architecture

### Design Patterns Used

#### 1. **Repository Pattern**
- All data access goes through repository classes
- Location: `app/Repositories/Admin/` and `app/Repositories/Api/`
- Separates data access from business logic
- Example: `ProductRepository` handles all product queries

#### 2. **Service Layer**
- Business logic encapsulated in repositories
- Controllers remain thin, delegating to repositories
- Reusable across controllers and API endpoints

#### 3. **Observer Pattern**
- Model events handle automatic logic
- Example: Auto-slug generation on Product/Brand creation
- Example: Auto-SKU generation for products

#### 4. **Trait Pattern**
- `Toast` trait: Flash message handling across controllers
- `HasRoles`: User role functionality (from Spatie)
- `SoftDeletes`: Data retention with soft deletes

#### 5. **Factory Pattern**
- Database factories for testing: `database/factories/`
- Model factories: `UserFactory.php`

### Layered Architecture

```
┌─────────────────────────────────────┐
│      Vue 3 + Inertia.js Frontend    │
│  (resources/js/pages, components)   │
├─────────────────────────────────────┤
│      HTTP Controllers Layer         │
│  (app/Http/Controllers)             │
│  - AdminController (thin)           │
│  - ApiController (thin)             │
├─────────────────────────────────────┤
│     Repository/Service Layer        │
│  (app/Repositories)                 │
│  - Business Logic                   │
│  - Data Access                      │
│  - Validation                       │
├─────────────────────────────────────┤
│      Eloquent Models Layer          │
│  (app/Models)                       │
│  - Relationships                    │
│  - Scopes                           │
│  - Accessors/Mutators               │
├─────────────────────────────────────┤
│      Database Layer                 │
│  - MySQL Database                   │
│  - 35+ Migrations                   │
│  - Seeders                          │
└─────────────────────────────────────┘
```

---

## 📊 Core Entities & Data Models

### Product Ecosystem (6 models)
1. **Product** - `app/Models/Admin/Product/Product.php`
   - Primary product entity
   - Relationships: Category, Brand, Collection, Gender, ScentFamily, Tags, Labels, Attributes, Discounts
   - Features: Pricing, Inventory, SEO, Images
   - Fillable: 50+ fields including pricing tiers, dimensions, tax info

2. **Category** - `app/Models/Admin/Category/Category.php`
   - Hierarchical structure (parent_id)
   - Soft deletes support
   - SEO fields (meta_title, meta_description, meta_keywords)

3. **Brand** - `app/Models/Admin/Brand/Brand.php`
   - Brand information
   - Features: is_featured, sort_order, logo
   - Auto-generates unique slugs

4. **Collection** - `app/Models/Admin/Collection/Collection.php`
   - Product collections
   - Features: is_featured, sort_order

5. **Gender** - `app/Models/Admin/Gender/Gender.php`
   - Gender categorization (Male, Female, Unisex)
   - Status tracking

6. **ScentFamily** - `app/Models/Admin/ScentFamily/ScentFamily.php`
   - Fragrance categorization
   - Status tracking

### Attribute & Tagging System (4 models)
7. **Attribute** - `app/Models/Admin/Attribute/Attribute.php`
   - Flexible attribute types: text, number, select, multiselect, boolean, date, textarea
   - Filterable and searchable
   - Related to AttributeValue

8. **AttributeValue** - `app/Models/Admin/Attribute/AttributeValue.php`
   - Predefined values for select/multiselect attributes
   - Related to Attribute

9. **Tag** - `app/Models/Admin/Tag/Tag.php`
   - Flexible tagging system
   - Status tracking (is_active)
   - Many-to-many with Product

10. **Label** - `app/Models/Admin/label/Label.php`
    - Product labels
    - Many-to-many with Product

### Pricing & Discount System (6 models)
11. **PriceList** - `app/Models/Admin/Price/PriceList.php`
    - Multiple pricing tiers
    - Default price list flag
    - Related to PriceRule

12. **PriceRule** - `app/Models/Admin/Price/PriceRule.php`
    - Pricing rules
    - Related to PriceList

13. **PriceHistory** - `app/Models/Admin/Price/PriceHistory.php`
    - Audit trail of price changes
    - Tracks old/new prices

14. **Discount** - `app/Models/Admin/Discount/Discount.php`
    - Discount management
    - Types: percentage, fixed, buy_x_get_y
    - Date range support
    - Many-to-many with Product

15. **FlashSale** - `app/Models/Admin/FlashSale/FlashSale.php`
    - Time-limited sales
    - Featured flag
    - Many-to-many with Product

16. **Option** - `app/Models/Admin/Option/Option.php`
    - Product options (size, color, etc.)
    - Required/optional flags
    - Sort order

### Inventory System (2 models)
17. **Inventory** - `app/Models/Admin/Inventory/Inventory.php`
    - Multi-location inventory
    - Quantities: quantity, reserved_quantity, available_quantity
    - Low stock tracking
    - Batch and expiry date support

18. **StockMovement** - `app/Models/Admin/Inventory/StockMovement.php`
    - Audit trail for inventory changes
    - Types: in, out, adjustment, transfer

### User Management (2 models)
19. **User** - `app/Models/User.php`
    - Admin user
    - Laravel Fortify authentication
    - Spatie role/permission support
    - 2FA columns

20. **Customer** - `app/Models/Customer.php`
    - E-commerce customer
    - Laravel Sanctum token auth
    - Email/phone authentication
    - Verification code support
    - Related to Cart

### Shopping System (1 model)
21. **Cart** - `app/Models/Cart.php`
    - Shopping cart items
    - Belongs to Customer and Product
    - Quantity and price tracking
    - Attributes support for product variants

---

## 🔌 API Endpoints

### Customer Authentication API (Public)
```
POST   /api/v1/customers/register          # Register new customer
POST   /api/v1/customers/login             # Login customer
POST   /api/v1/customers/logout            # Logout (auth required)
GET    /api/v1/customers/me                # Get current customer (auth required)
```

### Cart API (Protected - Sanctum)
```
GET    /api/v1/cart                        # Get cart items
GET    /api/v1/cart/summary                # Get cart summary
POST   /api/v1/cart                        # Add item to cart
PUT    /api/v1/cart/{id}                   # Update cart item
DELETE /api/v1/cart/{id}                   # Delete cart item
DELETE /api/v1/cart                        # Clear cart
```

### Products API (Public)
```
GET    /api/v1/products                    # List all products
GET    /api/v1/products/featured           # Get featured products
GET    /api/v1/products/new                # Get new products
GET    /api/v1/products/bestsellers        # Get bestseller products
GET    /api/v1/products/{id}               # Get single product
```

### Brands API (Public)
```
GET    /api/v1/brands                      # List brands (with filters)
GET    /api/v1/brands/{id}                 # Get single brand
```

### Genders API (Public)
```
GET    /api/v1/genders                     # List genders
GET    /api/v1/genders/{id}                # Get single gender
```

### Scent Families API (Public)
```
GET    /api/v1/scent-families              # List scent families
GET    /api/v1/scent-families/{id}         # Get single scent family
```

### Response Format
All API responses follow standard JSON format:
```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": { /* response data */ },
    "errors": { /* validation errors if any */ }
}
```

---

## 📁 File Structure

### Backend Structure

#### Models (`app/Models/`)
```
Models/
├── User.php                          # Admin user
├── Customer.php                      # E-commerce customer
├── Cart.php                          # Shopping cart
└── Admin/
    ├── Product/
    │   └── Product.php               # Main product model
    ├── Category/
    │   └── Category.php              # Product category
    ├── Brand/
    │   └── Brand.php                 # Product brand
    ├── Collection/
    │   └── Collection.php            # Product collection
    ├── Attribute/
    │   ├── Attribute.php             # Attribute definition
    │   └── AttributeValue.php        # Attribute values
    ├── Gender/
    │   └── Gender.php                # Gender categorization
    ├── ScentFamily/
    │   └── ScentFamily.php           # Scent categorization
    ├── Tag/
    │   └── Tag.php                   # Product tags
    ├── label/
    │   └── Label.php                 # Product labels
    ├── Discount/
    │   └── Discount.php              # Discount rules
    ├── FlashSale/
    │   └── FlashSale.php             # Flash sales
    ├── Price/
    │   ├── PriceList.php             # Price lists
    │   ├── PriceRule.php             # Price rules
    │   └── PriceHistory.php          # Price audit trail
    ├── Inventory/
    │   ├── Inventory.php             # Stock levels
    │   └── StockMovement.php         # Inventory audit
    └── Option/
        └── Option.php                # Product options
```

#### Controllers - Admin (`app/Http/Controllers/Admin/`)
```
Admin/
├── Product/ProductController.php         # Product CRUD
├── Category/CategoryController.php       # Category CRUD
├── Brand/BrandController.php             # Brand CRUD
├── Collection/CollectionController.php   # Collection CRUD
├── Attribute/AttributeController.php     # Attribute CRUD
├── Gender/GenderController.php           # Gender CRUD
├── ScentFamily/ScentFamilyController.php # Scent Family CRUD
├── Tag/TagController.php                 # Tag CRUD
├── Label/LabelController.php             # Label CRUD
├── Discount/DiscountController.php       # Discount CRUD
├── FlashSale/FlashSaleController.php     # Flash Sale CRUD
├── Price/PriceListController.php         # Price List CRUD
├── Inventory/InventoryController.php     # Inventory CRUD
├── Option/OptionController.php           # Option CRUD
├── User/UserController.php               # User CRUD
├── Customer/CustomerController.php       # Customer CRUD
└── Role/RoleController.php               # Role CRUD
```

#### Controllers - API (`app/Http/Controllers/Api/`)
```
Api/
├── ProductController.php                 # Product listing API
├── BrandController.php                   # Brand API
├── GenderController.php                  # Gender API
├── ScentFamilyController.php             # Scent Family API
├── CustomerAuthController.php            # Customer auth
└── CartController.php                    # Shopping cart
```

#### Repositories - Admin (`app/Repositories/Admin/`)
```
Admin/
├── Product/ProductRepository.php         # Product queries + logic
├── Category/CategoryRepository.php       # Category queries + logic
├── Brand/BrandRepository.php             # Brand queries + logic
├── Collection/CollectionRepository.php   # Collection queries + logic
├── Attribute/
│   ├── AttributeRepository.php
│   └── AttributeValueRepository.php
├── Gender/GenderRepository.php
├── ScentFamily/ScentFamilyRepository.php
├── Tag/TagRepository.php
├── Label/LabelRepository.php
├── Discount/DiscountRepository.php
├── FlashSale/FlashSaleRepository.php
├── Price/PriceListRepository.php
├── Inventory/InventoryRepository.php
├── Option/OptionRepository.php
├── User/UserRepository.php
├── Customer/CustomerRepository.php
└── Role/RoleRepository.php
```

#### Repositories - API (`app/Repositories/Api/`)
```
Api/
├── ProductRepository.php                 # Product queries for API
└── CartRepository.php                    # Cart logic
```

#### Routes (`routes/`)
```
routes/
├── web.php                              # Main admin routes
├── api.php                              # API routes
├── console.php                          # Artisan commands
└── settings.php                         # Settings routes
```

#### Database (`database/`)
```
database/
├── migrations/                          # 35+ migration files
│   ├── User/Permission tables
│   ├── Product ecosystem
│   ├── Attribute system
│   ├── Pricing system
│   ├── Inventory system
│   ├── Category system
│   ├── Gender/ScentFamily
│   └── Customer/Cart/Tokens
├── factories/
│   └── UserFactory.php
└── seeders/                             # Database seeders
```

#### Configuration (`config/`)
```
config/
├── app.php                              # Application config
├── auth.php                             # Auth config (guards, providers)
├── database.php                         # Database config
├── fortify.php                          # Fortify config
├── filesystems.php                      # Storage config
├── cache.php                            # Cache config
├── session.php                          # Session config
├── permission.php                       # Spatie permission config
├── sanctum.php                          # Sanctum API token config
├── services.php                         # Third-party services
└── mail.php                             # Mail driver config
```

---

## 💾 Database Schema

### Core Tables (35+ Migrations)

#### Users & Authentication
- `users` - Admin users (Laravel Fortify)
- `password_reset_tokens` - Password reset
- `personal_access_tokens` - Sanctum tokens
- `roles` - Spatie roles
- `permissions` - Spatie permissions
- `role_has_permissions` - Role permissions
- `model_has_roles` - User roles
- `model_has_permissions` - User permissions

#### Customers & Shopping
- `customers` - E-commerce customers
- `carts` - Shopping cart items
- `customers_password_reset_tokens` - Customer password reset

#### Products
- `products` - Main product table (50+ columns)
- `categories` - Product categories
- `brands` - Product brands
- `collections` - Product collections
- `genders` - Gender categorization
- `scent_families` - Scent categorization

#### Attributes & Tagging
- `attributes` - Attribute definitions
- `attribute_values` - Predefined attribute values
- `product_attribute` - Product-attribute associations (pivot)
- `tags` - Tags
- `product_tag` - Product-tag associations (pivot)
- `labels` - Product labels
- `product_label` - Product-label associations (pivot)

#### Pricing
- `price_lists` - Multiple price tiers
- `price_rules` - Pricing rules
- `price_history` - Price change audit trail

#### Discounts & Promotions
- `discounts` - Discount rules
- `product_discount` - Product-discount associations (pivot)
- `flash_sales` - Flash sale events
- `flash_sale_product` - Flash sale-product associations (pivot)

#### Inventory
- `inventory` - Stock levels
- `stock_movements` - Inventory audit trail

#### Options
- `options` - Product options (size, color, etc.)

#### System
- `cache` - Cache table
- `jobs` - Queue jobs
- `sessions` - Session storage

---

## 🔐 Authentication & Authorization

### Admin Authentication (Web Guard)
- **Driver**: Laravel Fortify
- **Features**: Email/password, 2FA support
- **Guard Name**: 'web'
- **Provider**: 'users'
- **Sessions**: Database-driven
- **Routes**: Auto-generated by Fortify

### Customer Authentication (API)
- **Driver**: Laravel Sanctum
- **Features**: Sanctum tokens
- **Guard Name**: 'sanctum' (for API)
- **Provider**: 'customers'
- **Token Generation**: `Customer::createToken()`
- **Usage**: Bearer token in Authorization header

### Authorization (Spatie Permissions)
- **Library**: spatie/laravel-permission
- **Features**: Roles & Permissions
- **Models**: HasRoles & HasPermissions traits
- **Database**: roles, permissions, role_has_permissions, model_has_roles, model_has_permissions
- **Implementation**: Used in User model for admin authorization

### Auth Configuration
```php
// Location: config/auth.php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'sanctum' => ['driver' => 'sanctum', 'provider' => 'customers'],
],
'providers' => [
    'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
    'customers' => ['driver' => 'eloquent', 'model' => App\Models\Customer::class],
],
```

---

## 🔧 Key Repositories

### ProductRepository (`app/Repositories/Admin/Product/ProductRepository.php`)
**Purpose**: Handle all product-related database queries and logic

**Key Methods**:
- `getAllProducts(array $filters)` - Get products with filtering
- `getProductById(int $id)` - Get single product
- `createProduct(array $data)` - Create product
- `updateProduct(Product $product, array $data)` - Update product
- `deleteProduct(Product $product)` - Soft delete
- `toggleStatus(Product $product)` - Toggle is_active
- `toggleFeatured(Product $product)` - Toggle is_featured
- `getStatistics()` - Product statistics
- `getAllProductsWithRelations()` - Load all relationships

**Filters Supported**:
- `is_active`, `is_featured` - Status filters
- `category_id`, `brand_id`, `collection_id` - Category filters
- `stock_status` - in_stock, out_of_stock, low_stock
- `min_price`, `max_price` - Price range
- `search` - Full-text search (name, sku, description)

### CartRepository (`app/Repositories/Api/CartRepository.php`)
**Purpose**: Handle shopping cart operations

**Key Methods**:
- `getCartItems(int $customerId)` - Get customer's cart items
- `getCartSummary(int $customerId)` - Get cart totals
- `addToCart(int $customerId, array $data)` - Add/update cart item
- `updateCartItem(int $cartId, array $data)` - Modify quantity/price
- `removeFromCart(int $cartId)` - Delete cart item
- `clearCart(int $customerId)` - Clear all items

**Validation**:
- Product must exist and be active
- Stock availability checking
- Quantity validation

### CategoryRepository
**Purpose**: Category management with hierarchical support

**Features**: Parent/child relationships, featured categories, SEO fields

### BrandRepository
**Purpose**: Brand management with featured flag and sort order

**Features**: Logo URL handling, active status, featured flag

---

## 🎮 Key Controllers

### ProductController (`app/Http/Controllers/Admin/Product/ProductController.php`)
**REST Actions**: index, create, store, show, edit, update, destroy

**Additional Actions**:
- `toggleStatus(Product $product)` - Toggle is_active
- `toggleFeatured(Product $product)` - Toggle is_featured
- `adjustStock(Product $product)` - Update inventory
- `bulkDelete()` - Delete multiple products

**Request Handling**:
- Filters from query params: is_active, is_featured, category_id, brand_id, etc.
- Inertia responses with related data (categories, brands, collections, etc.)
- File uploads for product images

### CustomerAuthController (`app/Http/Controllers/Api/CustomerAuthController.php`)
**Actions**:
- `register(Request $request)` - Register new customer
- `login(Request $request)` - Authenticate customer
- `logout()` - Revoke token
- `me()` - Get current customer

**Validation**:
- Email/phone uniqueness
- Password confirmation
- At least email or phone required
- Phone format validation

**Response**: JSON with Sanctum token

### CartController (`app/Http/Controllers/Api/CartController.php`)
**Actions**:
- `index()` - Get cart items
- `summary()` - Get cart summary
- `store(Request $request)` - Add to cart
- `update(Request $request, Cart $cart)` - Update item
- `destroy(Cart $cart)` - Remove item
- `clear()` - Clear cart

---

## 🛣️ Routes

### Admin Routes (All protected by auth middleware)
Prefix: `/admin` | Name: `admin.*`

**Resource Routes** (standard RESTful):
- Products, Categories, Brands, Collections, Tags, Labels, Attributes, Options, Discounts, Flash Sales, Inventory, Price Lists, Users, Roles, Customers, Genders, Scent Families

**Custom Routes**:
- Toggle status: `POST /admin/{resource}/{id}/toggle-status`
- Toggle featured: `POST /admin/{resource}/{id}/toggle-featured`
- Toggle required: `POST /admin/{resource}/{id}/toggle-required`
- Update sort order: `POST /admin/{resource}/sort-order`
- Bulk delete: `POST /admin/{resource}/bulk-delete`
- Dropdown data: `GET /admin/{resource}-dropdown`
- Stock adjustment: `POST /admin/inventory/{id}/adjust-stock`
- Stock movements: `GET /admin/inventory/stock-movements`
- Low stock: `GET /admin/inventory/low-stock`

### API Routes (Public & Protected)
Prefix: `/api/v1`

**Customer Auth**: register, login, logout, me
**Cart**: index, store, update, destroy, clear, summary
**Products**: index, featured, new, bestsellers, show
**Brands**: index, show
**Genders**: index, show
**Scent Families**: index, show

---

## 🚀 Development Workflow

### Setup Instructions
```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Database setup
php artisan migrate
php artisan db:seed

# 4. Build frontend
npm run build

# 5. Run development server
php artisan serve
```

### Development Commands
```bash
# Run in development (concurrent: server, queue, vite)
composer run dev

# Vite dev server only
npm run dev

# Build for production
npm run build

# Format code
npm run format

# Run linting
npm run lint
```

### Testing
```bash
# Run Pest tests
composer test

# Run with coverage
php artisan pest --coverage

# Watch mode
php artisan pest --watch
```

### Code Quality
```bash
# PHP formatting (Pint)
./vendor/bin/pint

# JavaScript linting & formatting
npm run lint
npm run format
```

### Database Management
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Reset database
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name
```

---

## ✨ Current Features

### ✅ Implemented Features

#### 1. Product Management
- Full CRUD operations
- 50+ product fields (pricing, dimensions, SEO, etc.)
- Image gallery support
- Category assignment
- Brand association
- Collection grouping
- Gender & Scent Family categorization
- Tags and Labels
- Attributes with custom values
- Bulk operations
- Toggle status/featured
- Stock quantity tracking
- Multiple pricing tiers
- View/sale counters
- SEO optimization

#### 2. Inventory Management
- Multi-location inventory tracking
- Stock movement audit trail
- Low stock alerts
- Batch tracking
- Reserved quantity management
- Stock adjustment operations
- Movement types: in, out, adjustment, transfer

#### 3. Pricing System
- Multiple price lists
- Price rules and strategies
- Price history tracking
- Default price list support

#### 4. Discount & Promotions
- Flexible discount types: percentage, fixed, buy_x_get_y
- Date-based discounts
- Product-specific discounts
- Featured discount flag
- Bulk operations

#### 5. Flash Sales
- Time-limited sales
- Featured flag
- Product associations
- Sort order management

#### 6. Category Management
- Hierarchical categories (parent/child)
- Featured flag
- Soft deletes with restore
- SEO fields
- Bulk operations

#### 7. User & Role Management
- Admin user CRUD
- Role-based permissions (Spatie)
- Permission management
- 2FA support
- Password management

#### 8. Attribute System
- Flexible attribute types
- Filterable attributes
- Searchable attributes
- Predefined attribute values
- Product-attribute associations

#### 9. Customer Authentication (API)
- Customer registration (email/phone)
- Customer login
- Bearer token authentication (Sanctum)
- Token logout
- Customer profile retrieval

#### 10. Shopping Cart (API)
- Add to cart
- Update quantity
- Remove from cart
- Clear cart
- Cart summary
- Stock availability checking

#### 11. Frontend Features
- Admin dashboard
- Vue 3 + Inertia.js SPA experience
- PrimeVue component library
- Flowbite components
- Dark mode support
- Responsive design
- Toast notifications
- Form validation

---

## 📝 Code Quality Standards

### PHP Code Quality
- **Framework**: Laravel 12.0 best practices
- **PHP Version**: ^8.2 (typed properties, match expressions, etc.)
- **Style**: PSR-12 (enforced by Pint)
- **Naming**: Camel case for methods/properties, PascalCase for classes
- **Documentation**: PHPDoc for public methods

### Frontend Code Quality
- **Language**: TypeScript for type safety
- **Style**: ESLint + Prettier
- **Framework**: Vue 3 Composition API recommended
- **Components**: Single-file components (.vue)
- **Naming**: PascalCase for components, camelCase for props/methods

### Database Standards
- **Naming**: snake_case for tables/columns
- **IDs**: Unsigned integer primary keys
- **Timestamps**: created_at, updated_at
- **Soft Deletes**: deleted_at column for important tables
- **Foreign Keys**: Explicit foreign key constraints with CASCADE rules

### API Standards
- **Response Format**: Consistent JSON wrapper with success, message, data, errors
- **HTTP Status Codes**: Proper status codes (200, 201, 400, 401, 404, 422, 500)
- **Validation**: Form request validation with custom messages
- **Authentication**: Bearer token in Authorization header
- **Rate Limiting**: Sanctum default rate limiting

### Testing Standards
- **Framework**: Pest PHP
- **Location**: `tests/` directory
- **Naming**: Test class names end with 'Test'
- **Assertions**: Clear, descriptive assertions
- **Setup/Teardown**: Use SetUp/TearDown or beforeEach/afterEach
- **Mocking**: Mockery for external dependencies

---

## 🎯 Key Files to Study

### Essential Reading Order
1. **Configuration**
   - `config/auth.php` - Auth guards and providers
   - `.env` - Environment configuration
   - `config/app.php` - Application settings

2. **Models**
   - `app/Models/Admin/Product/Product.php` - Core product model
   - `app/Models/Customer.php` - Customer model
   - `app/Models/User.php` - Admin user model

3. **Repositories**
   - `app/Repositories/Admin/Product/ProductRepository.php` - Main repository pattern example
   - `app/Repositories/Api/CartRepository.php` - API repository example

4. **Controllers**
   - `app/Http/Controllers/Admin/Product/ProductController.php` - Admin CRUD example
   - `app/Http/Controllers/Api/CustomerAuthController.php` - API example

5. **Routes**
   - `routes/api.php` - API routes
   - `routes/web.php` - Admin routes

6. **Frontend** (if needed)
   - `resources/js/pages/Admin/Product/` - Admin pages
   - `resources/js/components/` - Reusable components

---

## 🔍 Quick Reference

### Environment Variables
```
APP_URL=http://192.168.1.11:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexcrest_ecommerce
DB_USERNAME=root
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Key Model Traits
- `SoftDeletes` - Soft delete support
- `HasRoles` - Spatie role functionality
- `HasPermissions` - Spatie permission functionality
- `HasApiTokens` - Sanctum token support

### Common Scopes
- `Product::active()` - Active products
- `Product::featured()` - Featured products
- `Product::inStock()` - In stock products
- `Product::lowStock()` - Low stock products
- `Product::ordered()` - Ordered by sort_order

### Common Query Methods
```php
// Product queries
$products = Product::with(['category', 'brand', 'tags'])
    ->where('is_active', true)
    ->orderBy('sort_order')
    ->paginate(15);

// Category queries
$categories = Category::where('is_active', true)
    ->with('children')
    ->get();

// Brand queries
$brands = Brand::where('is_active', true)
    ->where('is_featured', true)
    ->orderBy('sort_order')
    ->get();
```

---

## 🚦 Next Steps & Recommendations

### For Immediate Development
1. Review the ProductRepository and ProductController pattern - it's the baseline for other CRUD operations
2. Understand the Repository pattern as it's used throughout the application
3. Familiarize yourself with the database schema, especially the pivot tables
4. Study the API authentication flow in CustomerAuthController

### For Feature Addition
1. Copy an existing repository and controller (e.g., BrandRepository → NewFeatureRepository)
2. Create migration for new table if needed
3. Create model with relationships
4. Implement repository with CRUD methods
5. Create controller that delegates to repository
6. Create routes in routes/web.php or routes/api.php
7. Create Vue components for frontend

### For Bug Fixes
1. Check the relevant repository for business logic
2. Look at the controller for request handling
3. Review the model for relationships and scopes
4. Test with the provided test patterns

---

## 📞 Support Resources

### Documentation
- APPLICATION_OVERVIEW.md - Detailed overview
- API_DOCUMENTATION.md - API endpoint documentation
- CUSTOMER_AUTHENTICATION.md - Customer auth guide
- BEARER_TOKEN_AUTH.md - Token usage guide

### Quick Links
- Models: `app/Models/` and `app/Models/Admin/`
- Controllers: `app/Http/Controllers/`
- Repositories: `app/Repositories/`
- Routes: `routes/`
- Database: `database/migrations/`

---

**Last Updated**: January 8, 2026  
**Application Version**: NexCrest E-Commerce v1.0  
**Framework**: Laravel 12.0 + Vue 3  
**Status**: Active Development
