# NexCrest E-Commerce Application Overview

## Application Architecture

### Technology Stack

**Backend:**
- **Framework**: Laravel 12.0
- **PHP Version**: ^8.2
- **Authentication**: Laravel Fortify (with 2FA support)
- **Authorization**: Spatie Laravel Permission (Roles & Permissions)
- **Frontend Integration**: Inertia.js 2.0
- **Database**: SQLite (development), supports other databases

**Frontend:**
- **Framework**: Vue 3.5.13 with TypeScript
- **UI Libraries**: 
  - PrimeVue 4.4.1 (Aura theme with dark mode)
  - Flowbite Vue 0.2.2
- **Styling**: Tailwind CSS 4.1.1
- **Icons**: 
  - PrimeIcons 7.0.0
  - Lucide Vue Next 0.468.0
- **Build Tool**: Vite 7.0.4
- **State Management**: Inertia.js (server-driven state)

**Development Tools:**
- **Testing**: Pest PHP 3.8
- **Code Quality**: Laravel Pint, ESLint, Prettier
- **Package Management**: Composer (PHP), npm (Node.js)

## Application Structure

### Backend Architecture

#### 1. **Repository Pattern**
The application follows a Repository pattern for data access:
- All repositories located in `app/Repositories/Admin/`
- Repositories handle:
  - Data retrieval with filtering
  - Business logic
  - Data validation
  - Database operations

**Key Repositories:**
- ProductRepository
- CategoryRepository
- BrandRepository
- CollectionRepository
- InventoryRepository
- DiscountRepository
- FlashSaleRepository
- AttributeRepository
- TagRepository
- LabelRepository
- UserRepository
- RoleRepository
- GenderRepository
- ScentFamilyRepository
- OptionRepository
- PriceListRepository

#### 2. **Controller Layer**
- Controllers in `app/Http/Controllers/Admin/`
- Controllers are thin, delegate to repositories
- Use `Toast` trait for flash messages
- Return Inertia responses for Vue components

#### 3. **Model Layer**
- Models organized in `app/Models/Admin/`
- Rich models with:
  - Relationships (belongsTo, hasMany, belongsToMany)
  - Scopes (active, featured, ordered, etc.)
  - Accessors/Mutators
  - Soft deletes support
  - Auto-slug generation
  - Image URL handling

**Key Models:**
- Product (with extensive relationships)
- Category (hierarchical with parent/children)
- Brand, Collection, Tag, Label
- Attribute & AttributeValue
- Inventory & StockMovement
- Discount, FlashSale
- PriceList, PriceRule, PriceHistory
- Gender, ScentFamily
- Option
- User (with roles/permissions)

## Database Schema

### Core Tables

1. **Products** (`products`)
   - Comprehensive product management
   - Pricing (price, compare_at_price, cost_price)
   - Inventory tracking (stock_quantity, track_inventory, low_stock_threshold)
   - Images (main_image, gallery_images)
   - Relationships: category, brand, collection, gender, scent_family
   - Status flags: is_active, is_featured, is_new, is_bestseller
   - SEO fields: meta_title, meta_description, meta_keywords
   - Physical attributes: weight, dimensions, shipping info

2. **Categories** (`categories`)
   - Hierarchical structure (parent_id)
   - Soft deletes
   - Featured categories
   - SEO fields

3. **Brands** (`brands`)
   - Brand management with featured flag
   - Sort order support

4. **Collections** (`collections`)
   - Product collections
   - Featured and sort order

5. **Attributes & Attribute Values**
   - Flexible attribute system
   - Types: text, number, select, multiselect, boolean, date, textarea
   - Filterable and searchable attributes
   - Product-attribute many-to-many with pivot data

6. **Inventory** (`inventory`)
   - Multi-location support
   - Quantity tracking (quantity, reserved_quantity, available_quantity)
   - Low stock alerts
   - Batch tracking and expiry dates

7. **Stock Movements** (`stock_movements`)
   - Audit trail for inventory changes
   - Movement types: in, out, adjustment, transfer

8. **Discounts** (`discounts`)
   - Discount management
   - Types: percentage, fixed, buy_x_get_y
   - Date range support
   - Product associations

9. **Flash Sales** (`flash_sales`)
   - Time-limited sales
   - Featured flash sales
   - Product associations

10. **Price Lists** (`price_lists`)
    - Multiple pricing tiers
    - Default price list support
    - Price rules and history

11. **Tags & Labels**
    - Flexible tagging system
    - Product associations

12. **Options** (`options`)
    - Product options (e.g., size, color)
    - Required/optional flags
    - Sort order

13. **Genders** (`genders`)
    - Gender categorization for products

14. **Scent Families** (`scent_families`)
    - Fragrance categorization

15. **Users & Roles**
    - Laravel authentication
    - Spatie permissions (roles & permissions)
    - Two-factor authentication support

### Pivot Tables
- `product_tag` - Product to Tag
- `product_label` - Product to Label
- `product_attribute` - Product to Attribute (with value_id and custom value)
- `product_discount` - Product to Discount
- `flash_sale_product` - Flash Sale to Product

## Frontend Architecture

### Page Structure
- Pages in `resources/js/pages/`
- Organized by feature:
  - `Admin/` - Admin panel pages (CRUD for all entities)
  - `Auth/` - Authentication pages
  - `settings/` - User settings pages
  - `Dashboard.vue` - Main dashboard
  - `Welcome.vue` - Landing page

### Component Structure
- Reusable components in `resources/js/components/`
- UI components in `resources/js/components/ui/` (shadcn-style components)
- Layout components in `resources/js/layouts/`

**Key Components:**
- `AppShell.vue` - Main application shell
- `AppSidebar.vue` - Navigation sidebar
- `AppHeader.vue` - Top header
- `ToastNotifications.vue` - Toast notification system
- Various UI components (Button, Card, Dialog, Input, etc.)

### Layouts
- `AppLayout.vue` - Main application layout
- `AuthLayout.vue` - Authentication layout
- `AppSidebarLayout.vue` - Sidebar layout
- `AppHeaderLayout.vue` - Header layout
- Settings layouts

### Routing
- Laravel routes in `routes/web.php` and `routes/settings.php`
- Inertia.js handles client-side routing
- Wayfinder plugin for route generation

## Key Features

### 1. Product Management
- Full CRUD operations
- Rich product attributes
- Image gallery support
- Inventory tracking
- Stock management
- Product variants via attributes
- SEO optimization
- Featured/new/bestseller flags

### 2. Category Management
- Hierarchical categories (parent/child)
- Category tree navigation
- Soft deletes with restore
- Featured categories
- SEO fields

### 3. Inventory Management
- Multi-location inventory
- Stock movement tracking
- Low stock alerts
- Batch tracking
- Reserved quantity management

### 4. Pricing System
- Multiple price lists
- Price rules
- Price history tracking
- Default price list

### 5. Discount System
- Multiple discount types
- Date-based discounts
- Product-specific discounts
- Percentage and fixed discounts

### 6. Flash Sales
- Time-limited sales
- Featured flash sales
- Product associations

### 7. Attribute System
- Flexible attribute types
- Filterable attributes
- Searchable attributes
- Product-attribute associations

### 8. User & Role Management
- User CRUD
- Role-based permissions (Spatie)
- Password management
- Two-factor authentication

### 9. Brand & Collection Management
- Brand management with featured flag
- Collection management
- Sort order support

### 10. Tag & Label System
- Flexible tagging
- Product labeling
- Status management

## Authentication & Authorization

### Authentication
- Laravel Fortify
- Email/password authentication
- Two-factor authentication (2FA)
- Email verification
- Password reset
- Remember me functionality

### Authorization
- Spatie Laravel Permission
- Role-based access control (RBAC)
- Permission-based authorization
- Guard: 'web'

## API & Routes

### Route Structure
- `/` - Welcome page
- `/dashboard` - Main dashboard (auth required)
- `/admin/*` - Admin panel routes (auth required)
- `/settings/*` - User settings (auth required)
- `/admin/*` - Fortify auth routes (login, register, etc.)

### Admin Routes
All admin routes prefixed with `/admin` and named `admin.*`:
- Products: CRUD + toggle status/featured, update stock, sort order, bulk delete
- Categories: CRUD + toggle status/featured, move, restore, sort order
- Brands: CRUD + toggle status/featured, sort order, bulk delete
- Collections: CRUD + toggle status/featured, sort order, bulk delete
- Tags: CRUD + toggle status, bulk delete, dropdown
- Labels: CRUD + bulk delete
- Attributes: CRUD + toggle status/filterable, sort order, bulk delete
- Options: CRUD + toggle status/required, sort order, bulk delete
- Discounts: CRUD + toggle status, sort order, bulk delete
- Flash Sales: CRUD + toggle status/featured, sort order, bulk delete
- Inventory: CRUD + adjust stock, stock movements, low stock alerts
- Price Lists: CRUD + toggle status, set default, sort order
- Users: CRUD + update password
- Roles: CRUD

## Development Workflow

### Setup
```bash
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
```

### Development
```bash
composer run dev  # Runs server, queue, and vite concurrently
```

### Testing
```bash
composer test  # Runs Pest tests
```

### Code Quality
- **PHP**: Laravel Pint
- **JavaScript/TypeScript**: ESLint + Prettier
- **Formatting**: `npm run format`

## File Organization

### Backend
```
app/
├── Actions/Fortify/        # Fortify custom actions
├── Http/
│   ├── Controllers/Admin/  # Admin controllers
│   ├── Middleware/         # Custom middleware
│   └── Requests/           # Form requests
├── Models/Admin/           # Eloquent models
├── Providers/              # Service providers
├── Repositories/Admin/     # Repository classes
└── Traits/                 # Reusable traits

database/
├── migrations/             # Database migrations
└── seeders/                # Database seeders

routes/
├── web.php                 # Main routes
└── settings.php            # Settings routes
```

### Frontend
```
resources/js/
├── actions/                # Auto-generated route actions
├── components/             # Vue components
│   └── ui/                 # UI component library
├── composables/            # Vue composables
├── config/                 # Configuration files
├── layouts/                # Layout components
├── pages/                  # Inertia pages
├── routes/                 # Auto-generated routes
├── types/                  # TypeScript types
└── wayfinder/             # Auto-generated wayfinder
```

## Key Design Patterns

1. **Repository Pattern**: Data access abstraction
2. **Service Layer**: Business logic in repositories
3. **Observer Pattern**: Model events (slug generation, etc.)
4. **Trait Pattern**: Reusable functionality (Toast, HasRoles)
5. **Factory Pattern**: Model factories for testing
6. **Strategy Pattern**: Different attribute types

## Security Features

- CSRF protection (Laravel built-in)
- SQL injection protection (Eloquent ORM)
- XSS protection (Vue.js auto-escaping)
- Password hashing (bcrypt)
- Two-factor authentication
- Role-based access control
- Rate limiting (Fortify)

## Performance Considerations

- Eager loading relationships (with())
- Database indexes on foreign keys and frequently queried columns
- Soft deletes for data retention
- Image optimization (storage paths)
- Vite for fast frontend builds
- Inertia.js for SPA-like experience without API overhead

## Extensibility

- Modular repository pattern allows easy feature additions
- Flexible attribute system for custom product properties
- Plugin-ready architecture (Laravel service providers)
- Component-based frontend (easy to add new UI components)
- Migration-based schema changes

## Current Status

The application appears to be a **comprehensive e-commerce admin panel** with:
- ✅ Complete product management system
- ✅ Inventory tracking
- ✅ Pricing and discount management
- ✅ User and role management
- ✅ Modern Vue 3 + Inertia.js frontend
- ✅ Responsive UI with PrimeVue and Flowbite
- ✅ Dark mode support
- ✅ Two-factor authentication
- ✅ Comprehensive admin CRUD operations

## Next Steps for Development

Potential areas for expansion:
1. Customer-facing storefront (public routes)
2. Shopping cart functionality
3. Order management system
4. Payment gateway integration
5. Shipping management
6. Customer reviews and ratings
7. Wishlist functionality
8. Product recommendations
9. Analytics and reporting
10. Email notifications
11. API endpoints for mobile apps
12. Multi-language support
13. Multi-currency support

