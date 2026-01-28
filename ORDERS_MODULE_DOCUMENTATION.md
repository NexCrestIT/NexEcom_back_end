# Orders Module Documentation

## Overview
The Orders Module is a comprehensive system for managing e-commerce orders in the admin panel. It provides complete order lifecycle management, payment handling, shipment tracking, and customer support features.

## Features

### 1. Order Management
- **View all orders** with pagination and filtering
- **Order details view** with customer information, items, and status
- **Order status tracking** with valid transitions (pending → processing → shipped → delivered)
- **Payment status management** (pending, completed, failed, refunded)
- **Order notes and comments** for internal communication
- **Bulk order operations** for status updates

### 2. Status Management
- **Order Statuses**:
  - Pending: Initial order state
  - Processing: Order is being prepared
  - Shipped: Order is in transit
  - Delivered: Order received by customer
  - Cancelled: Order has been cancelled
  - Returned: Order has been returned

- **Payment Statuses**:
  - Pending: Payment not yet received
  - Completed: Payment received and confirmed
  - Failed: Payment transaction failed
  - Refunded: Payment has been refunded to customer

### 3. Advanced Filtering
- Filter by order status
- Filter by payment status
- Search by order number or customer name
- Filter by date range
- Filter by amount range

### 4. Analytics & Reporting
- Order statistics dashboard
- Revenue tracking
- Status distribution charts
- Payment method analytics
- Top customers by order value
- Recent orders list
- CSV export functionality

### 5. Customer Service Features
- **Refund Processing**: Create refunds with custom amounts
- **Order Notes**: Add internal notes for reference
- **Invoice Generation**: Create and download invoices
- **Shipment Tracking**: Track shipments with carriers

### 6. Payment Integration
- Razorpay integration ready
- Multiple payment method support
- Payment history tracking
- Refund management

## File Structure

```
app/
├── Http/Controllers/Admin/Order/
│   └── OrderController.php           # Main admin order controller
├── Http/Controllers/Api/
│   └── OrderController.php           # API endpoints for customers
├── Repositories/Admin/Order/
│   └── OrderRepository.php           # Order data operations
├── Services/
│   ├── OrderStatusService.php        # Order status management
│   ├── InvoiceService.php            # Invoice generation
│   └── ShipmentService.php           # Shipment tracking
└── Models/
    └── Order.php                     # Order eloquent model

routes/web.php                        # Admin panel routes
```

## Routes

### Admin Routes
All routes are prefixed with `/admin` and require authentication.

```
GET    /admin/orders                          # List all orders
GET    /admin/orders/{id}                     # View order details
POST   /admin/orders/{id}/update-status       # Update order status
POST   /admin/orders/{id}/update-payment-status  # Update payment status
POST   /admin/orders/{id}/update-notes        # Update order notes
POST   /admin/orders/{id}/process-refund      # Process refund
POST   /admin/orders/bulk-update-status       # Bulk status update
DELETE /admin/orders/{id}                     # Delete order
GET    /admin/orders/export/csv               # Export orders to CSV
GET    /admin/orders/dashboard/stats          # Get dashboard statistics
```

### API Routes
Accessible for authenticated customers:

```
GET    /api/orders                    # Get customer's orders
GET    /api/orders/{id}               # Get order details
POST   /api/orders/{id}/cancel        # Cancel order
POST   /api/orders/{id}/return        # Request return
GET    /api/orders/{id}/invoice       # Download invoice
GET    /api/orders/stats              # Get order statistics
```

## Usage Examples

### Updating Order Status
```php
// Via API
POST /admin/orders/1/update-status
{
    "status": "processing",
    "notes": "Order is being prepared"
}

// Via Service
use App\Services\OrderStatusService;

$order = Order::find(1);
if (OrderStatusService::isValidTransition($order->status, 'processing')) {
    $order->update(['status' => 'processing']);
}
```

### Processing Refund
```php
// Via API
POST /admin/orders/1/process-refund
{
    "amount": 100.00,  // Optional, defaults to total amount
    "reason": "Customer requested refund"
}

// Via Service
use App\Services\OrderStatusService;

$order = Order::find(1);
if (OrderStatusService::canRefund($order)) {
    OrderStatusService::processRefund($order, 100.00);
}
```

### Generating Invoice
```php
use App\Services\InvoiceService;

$order = Order::find(1);
$invoiceData = InvoiceService::generateInvoiceData($order);
$invoiceHtml = InvoiceService::getInvoiceHTML($order);
```

### Creating Shipment
```php
use App\Services\ShipmentService;

$order = Order::find(1);
$shipment = ShipmentService::createShipment($order, [
    'carrier' => ShipmentService::CARRIER_FEDEX,
    'estimated_delivery' => '2024-02-01'
]);

$tracking = ShipmentService::getTrackingInfo($shipment['tracking_number']);
```

## Service Classes

### OrderStatusService
Manages order and payment statuses with validation.

**Methods:**
- `getOrderStatuses()`: Get all available order statuses
- `getPaymentStatuses()`: Get all payment statuses
- `getStatusColor($status)`: Get UI color for status
- `isValidTransition($current, $new)`: Check if status transition is allowed
- `getNextPossibleStatuses($status)`: Get available next statuses
- `canRefund($order)`: Check if order can be refunded
- `canCancel($order)`: Check if order can be cancelled
- `canShip($order)`: Check if order can be shipped
- `processRefund($order, $amount)`: Process refund

### OrderRepository
Handles all database operations for orders.

**Methods:**
- `getPaginatedOrders($perPage, $filters)`: Get paginated orders with filters
- `getOrderById($id)`: Get single order with relationships
- `updateOrderStatus($id, $status, $notes)`: Update order status
- `updatePaymentStatus($id, $status)`: Update payment status
- `getStatistics()`: Get order statistics
- `getRevenueByDateRange($from, $to)`: Revenue analytics
- `getStatusDistribution()`: Status breakdown
- `getTopCustomers($limit)`: Top customers by spend
- `bulkUpdateStatus($ids, $status)`: Bulk update orders

### InvoiceService
Generates and manages invoices.

**Methods:**
- `generateInvoiceData($order)`: Generate invoice data
- `generateInvoiceNumber($order)`: Create unique invoice number
- `generatePDF($order)`: Generate PDF (with library integration)
- `getInvoiceHTML($order)`: Get HTML version
- `sendInvoiceToCustomer($order)`: Email invoice

### ShipmentService
Manages shipments and tracking.

**Methods:**
- `getCarriers()`: Get supported carriers
- `generateTrackingNumber($order)`: Create tracking number
- `createShipment($order, $data)`: Create shipment
- `getTrackingInfo($trackingNumber)`: Get tracking details
- `updateShipmentStatus($order, $status)`: Update shipment
- `calculateShippingCost($order, $carrier)`: Calculate shipping
- `generateShippingLabel($order, $carrier)`: Create shipping label

## Database Requirements

Ensure your `orders` table has these columns:
- `id` (primary key)
- `customer_id` (foreign key)
- `address_id` (foreign key)
- `order_number` (unique)
- `total_amount` (decimal)
- `status` (enum/string)
- `payment_status` (enum/string)
- `payment_method` (string)
- `razorpay_order_id` (string)
- `razorpay_payment_id` (string)
- `notes` (text)
- `paid_at` (timestamp)
- `created_at`, `updated_at` (timestamps)

## Filtering Options

### Order Status Filter
```
pending | processing | shipped | delivered | cancelled | returned
```

### Payment Status Filter
```
pending | completed | failed | refunded
```

### Date Range Filter
```
from_date: YYYY-MM-DD
to_date: YYYY-MM-DD
```

### Amount Range Filter
```
min_amount: number
max_amount: number
```

## Authentication & Authorization

All admin routes require authentication. Currently no role-based checks, but can be added via middleware:

```php
Route::resource('orders', OrderController::class)
    ->middleware('role:admin,manager');
```

## Future Enhancements

1. **Payment Gateway Integration**
   - Full Razorpay integration
   - Stripe support
   - PayPal integration

2. **Notifications**
   - Email notifications for status changes
   - SMS alerts for customers
   - Admin notifications for important events

3. **Returns & Exchanges**
   - Return request management
   - Exchange processing
   - Return shipping labels

4. **Reporting**
   - Advanced analytics dashboard
   - Custom report generation
   - Excel export with formatting

5. **Automation**
   - Auto status updates based on time
   - Automatic refunds
   - Scheduled notifications

6. **Multi-Channel Integration**
   - Shopify integration
   - Amazon integration
   - eBay integration

## Troubleshooting

### Order Status Not Updating
- Check if transition is valid using `OrderStatusService::isValidTransition()`
- Verify order exists and is accessible
- Check error logs for exceptions

### Invoice Generation Issues
- Ensure order has associated items
- Verify customer and address information
- Check for null values in product data

### Shipment Tracking Not Working
- Verify carrier is supported
- Check tracking number format
- Ensure order has valid address

## Support & Contact
For issues or questions, please contact the development team or create an issue in the repository.
