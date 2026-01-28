# Orders Module - Inertia Views Structure

This guide describes the Vue/Inertia component structure needed for the Orders admin panel module.

## Directory Structure

```
resources/views/Admin/Order/
├── Index.vue                 # Orders list view
├── Show.vue                  # Order details view
├── Components/
│   ├── OrdersTable.vue       # Reusable orders table
│   ├── OrderStatusBadge.vue  # Status display component
│   ├── PaymentStatusBadge.vue # Payment status display
│   ├── OrderFilter.vue       # Filter panel
│   ├── OrderStats.vue        # Statistics cards
│   ├── OrderTimeline.vue     # Order status timeline
│   ├── CustomerInfo.vue      # Customer details card
│   ├── OrderItems.vue        # Order items table
│   ├── ShippingInfo.vue      # Shipping information
│   ├── PaymentInfo.vue       # Payment details
│   ├── InvoicePanel.vue      # Invoice section
│   ├── RefundPanel.vue       # Refund processing
│   ├── NotesPanel.vue        # Order notes
│   └── BulkActions.vue       # Bulk action toolbar
```

## Component Specifications

### Index.vue (Orders List)
**Purpose**: Display paginated list of all orders with filters and bulk actions

**Props**: None

**Data from Controller**:
```javascript
{
  orders: {
    data: [],           // paginated orders
    links: {},          // pagination links
    meta: {}            // pagination meta
  },
  statistics: {
    total_orders: 0,
    pending_orders: 0,
    processing_orders: 0,
    shipped_orders: 0,
    delivered_orders: 0,
    cancelled_orders: 0,
    total_revenue: 0,
    pending_revenue: 0
  },
  filters: {
    status: null,
    payment_status: null,
    search: null,
    from_date: null,
    to_date: null,
    min_amount: null,
    max_amount: null
  },
  orderStatuses: {},      // All available order statuses
  paymentStatuses: {}     // All available payment statuses
}
```

**Features**:
- Display orders in table format
- Show order number, customer, amount, status, payment status, date
- Filter by status, payment status, date range, amount
- Search by order number or customer
- Pagination controls
- Bulk select checkboxes
- Action buttons (View, Edit, Delete)
- Bulk update status dropdown
- Export to CSV button
- Sort by column

### Show.vue (Order Details)
**Purpose**: Display complete order details with management options

**Props**: None

**Data from Controller**:
```javascript
{
  order: {
    id: 0,
    order_number: '',
    customer: { /* customer data */ },
    items: [],
    address: {},
    total_amount: 0,
    status: '',
    payment_status: '',
    payment_method: '',
    notes: '',
    created_at: '',
    paid_at: null
  },
  orderStatuses: {},
  paymentStatuses: {},
  nextPossibleStatuses: {},
  canCancel: false,
  canRefund: false,
  canShip: false
}
```

**Features**:
- Display complete order information
- Customer details section
- Shipping address section
- Order items list with prices
- Order timeline showing status changes
- Current status with option to change to next possible statuses
- Payment information and status
- Refund button (if eligible)
- Cancel button (if eligible)
- Ship button (if eligible)
- Invoice section with download/view/send options
- Notes section (editable)
- Back to orders button

### OrdersTable.vue (Reusable Table)
**Props**:
```javascript
{
  orders: Array,           // Orders to display
  loading: Boolean,        // Loading state
  selectable: Boolean,     // Show checkboxes
  hoverable: Boolean       // Hover effects
}
```

**Emits**:
```javascript
{
  'select': (order) => {},
  'view': (id) => {},
  'delete': (id) => {},
  'row-click': (order) => {}
}
```

### OrderStatusBadge.vue (Status Display)
**Props**:
```javascript
{
  status: String,        // Order status
  size: String,         // 'sm', 'md', 'lg'
  clickable: Boolean    // Editable on click
}
```

**Emits**:
```javascript
{
  'change': (newStatus) => {}
}
```

### OrderFilter.vue (Filter Panel)
**Props**:
```javascript
{
  filters: Object,
  orderStatuses: Object,
  paymentStatuses: Object,
  loading: Boolean
}
```

**Emits**:
```javascript
{
  'filter': (filters) => {},
  'reset': () => {}
}
```

**Features**:
- Status filter dropdown
- Payment status filter dropdown
- Date range picker (from_date, to_date)
- Amount range inputs (min_amount, max_amount)
- Search input
- Apply filters button
- Reset filters button
- Active filters display

### OrderStats.vue (Statistics Cards)
**Props**:
```javascript
{
  statistics: Object,
  loading: Boolean
}
```

**Features**:
- Display statistics cards:
  - Total Orders
  - Pending Orders
  - Processing Orders
  - Shipped Orders
  - Delivered Orders
  - Cancelled Orders
  - Total Revenue
  - Pending Revenue
- Each card shows count/amount
- Click to filter orders by that status

### OrderTimeline.vue (Status Timeline)
**Props**:
```javascript
{
  order: Object,
  statuses: Object
}
```

**Features**:
- Display timeline of status changes
- Show current status with icon
- Show possible next statuses
- Display dates of transitions
- Color-coded status indicators

### CustomerInfo.vue (Customer Card)
**Props**:
```javascript
{
  customer: Object
}
```

**Features**:
- Display customer name
- Customer email
- Customer phone
- Link to customer profile
- Customer status (active/inactive)
- Previous orders count

### OrderItems.vue (Items Table)
**Props**:
```javascript
{
  items: Array,
  editable: Boolean
}
```

**Features**:
- Product name and SKU
- Quantity ordered
- Unit price
- Total item price
- Product image thumbnail
- Link to product
- Subtotal, tax, total calculations

### ShippingInfo.vue (Shipping Details)
**Props**:
```javascript
{
  address: Object,
  order: Object
}
```

**Features**:
- Display full shipping address
- Shipping method
- Tracking number (if shipped)
- Estimated delivery date
- Tracking link
- Edit address button (if not shipped)

### PaymentInfo.vue (Payment Details)
**Props**:
```javascript
{
  order: Object,
  paymentStatuses: Object
}
```

**Features**:
- Payment method
- Payment status with badge
- Razorpay payment ID (if applicable)
- Amount paid
- Payment date
- Refund button (if applicable)
- Change payment status (admin only)

### InvoicePanel.vue (Invoice Section)
**Props**:
```javascript
{
  order: Object
}
```

**Features**:
- Generate invoice button
- Download PDF button
- View invoice button
- Email invoice button
- Invoice preview modal
- Invoice number display

### RefundPanel.vue (Refund Processing)
**Props**:
```javascript
{
  order: Object,
  canRefund: Boolean
}
```

**Features**:
- Refund amount input (defaults to full amount)
- Refund reason textarea
- Process refund button
- Refund history display
- Confirmation dialog

### NotesPanel.vue (Order Notes)
**Props**:
```javascript
{
  order: Object
}
```

**Features**:
- Display current notes
- Edit notes textarea
- Save notes button
- Cancel button
- Auto-save functionality
- Note timestamp

### BulkActions.vue (Bulk Toolbar)
**Props**:
```javascript
{
  selectedCount: Number,
  orderStatuses: Object
}
```

**Emits**:
```javascript
{
  'bulk-update-status': (status) => {},
  'bulk-delete': () => {},
  'cancel': () => {}
}
```

**Features**:
- Show selected items count
- Bulk status update dropdown
- Bulk delete button (with confirmation)
- Cancel selection button

## Data Flow

### Index to Show
```
OrdersTable (click) → router.visit('/admin/orders/{id}') → Show.vue
```

### Filter Operations
```
OrderFilter (change) → useForm().get() → rerender with ?query=value → Index.vue
```

### Status Update
```
OrderStatusBadge (click) → POST /admin/orders/{id}/update-status → refresh order
```

### Bulk Operations
```
BulkActions (submit) → POST /admin/orders/bulk-update-status → redirect to index
```

## Example: Index.vue Template Structure

```vue
<template>
  <div class="orders-container">
    <!-- Header -->
    <div class="page-header">
      <h1>Orders</h1>
      <button @click="exportCSV" class="btn btn-secondary">
        Export to CSV
      </button>
    </div>

    <!-- Statistics Cards -->
    <OrderStats :statistics="statistics" />

    <!-- Filters -->
    <OrderFilter
      :filters="filters"
      :order-statuses="orderStatuses"
      :payment-statuses="paymentStatuses"
      @filter="applyFilter"
      @reset="resetFilter"
    />

    <!-- Bulk Actions (visible only when items selected) -->
    <BulkActions
      v-if="selectedOrders.length > 0"
      :selected-count="selectedOrders.length"
      :order-statuses="orderStatuses"
      @bulk-update-status="bulkUpdateStatus"
      @bulk-delete="bulkDelete"
      @cancel="selectedOrders = []"
    />

    <!-- Orders Table -->
    <OrdersTable
      :orders="orders.data"
      :loading="loading"
      selectable
      @select="toggleSelect"
      @view="viewOrder"
      @delete="deleteOrder"
    />

    <!-- Pagination -->
    <Pagination :links="orders.links" />
  </div>
</template>
```

## Example: Show.vue Template Structure

```vue
<template>
  <div class="order-details">
    <!-- Header -->
    <div class="page-header">
      <router-link to="/admin/orders">← Back to Orders</router-link>
      <h1>{{ order.order_number }}</h1>
      <div class="actions">
        <button v-if="canShip" @click="shipOrder" class="btn btn-primary">
          Ship Order
        </button>
        <button v-if="canCancel" @click="cancelOrder" class="btn btn-danger">
          Cancel Order
        </button>
      </div>
    </div>

    <div class="order-grid">
      <!-- Left Column -->
      <div class="left-column">
        <!-- Customer Info -->
        <CustomerInfo :customer="order.customer" />

        <!-- Shipping Info -->
        <ShippingInfo :address="order.address" :order="order" />

        <!-- Order Timeline -->
        <OrderTimeline :order="order" :statuses="orderStatuses" />
      </div>

      <!-- Right Column -->
      <div class="right-column">
        <!-- Order Status Card -->
        <div class="card">
          <h3>Order Status</h3>
          <OrderStatusBadge :status="order.status" />
          <select v-if="nextPossibleStatuses" v-model="newStatus">
            <option>Change Status...</option>
            <option v-for="(label, status) in nextPossibleStatuses" :key="status" :value="status">
              {{ label }}
            </option>
          </select>
          <button @click="updateStatus" class="btn btn-sm btn-primary">
            Update Status
          </button>
        </div>

        <!-- Payment Info -->
        <PaymentInfo :order="order" :payment-statuses="paymentStatuses" />

        <!-- Invoice Panel -->
        <InvoicePanel :order="order" />

        <!-- Refund Panel (if eligible) -->
        <RefundPanel v-if="canRefund" :order="order" />
      </div>
    </div>

    <!-- Order Items -->
    <OrderItems :items="order.items" />

    <!-- Notes Panel -->
    <NotesPanel :order="order" />
  </div>
</template>
```

## Styling Recommendations

Use a UI framework like:
- **Tailwind CSS** - Utility-first CSS framework
- **Bootstrap** - Component-based framework
- **Vuetify** - Vue component framework
- Or existing project's CSS framework

## State Management

Use Inertia's built-in functionality:
- Form state: `useForm()`
- Page props: Accessed directly in template
- Reactive updates: Use `v-model` with form helpers
- No need for Vuex/Pinia for simple CRUD

## Accessibility

Ensure components:
- Have proper ARIA labels
- Support keyboard navigation
- Have sufficient color contrast
- Include proper form labels
- Have descriptive link text

## Performance

- Use pagination for large lists
- Lazy load images
- Debounce search/filter inputs
- Memoize computed properties
- Virtual scrolling for very large lists

## Testing

Write tests for:
- Filter functionality
- Status transitions
- Bulk operations
- Form submissions
- API error handling
- Permission checks
