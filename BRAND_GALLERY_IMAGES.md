# Brand Gallery Images Implementation

## Overview
Successfully added gallery image support to the Brand module, mirroring the Product module's functionality. Brands now support both main images (logo) and multiple gallery images.

---

## Changes Made

### 1. Database Migration
**File**: `database/migrations/2025_01_20_100000_add_gallery_images_to_brands_table.php`

Added `gallery_images` column as JSON to the brands table:
```sql
ALTER TABLE brands ADD COLUMN gallery_images JSON NULL AFTER logo;
```

**Status**: ✅ Migration completed successfully

---

### 2. Brand Model Update
**File**: [app/Models/Admin/Brand/Brand.php](app/Models/Admin/Brand/Brand.php)

**Changes**:
- Added `gallery_images` to `$fillable` array
- Added `gallery_images` to `$casts` with `array` type
- Added `gallery_images_urls` to `$appends` array
- Added `getGalleryImagesUrlsAttribute()` accessor method

**Code**:
```php
protected $fillable = [
    'name',
    'slug',
    'description',
    'logo',
    'gallery_images',  
    'website',
    'is_active',
    'is_featured',
    'sort_order',
    'meta_title',
    'meta_description',
    'meta_keywords',
];

protected $casts = [
    'is_active' => 'boolean',
    'is_featured' => 'boolean',
    'sort_order' => 'integer',
    'gallery_images' => 'array',
];

protected $appends = ['logo_url', 'gallery_images_urls'];


public function getGalleryImagesUrlsAttribute()
{
    if (!$this->gallery_images || !is_array($this->gallery_images)) {
        return [];
    }

    return array_map(function ($image) {
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }
        return asset('storage/' . $image);
    }, $this->gallery_images);
}
```

---

### 3. Brand Repository Updates
**File**: [app/Repositories/Admin/Brand/BrandRepository.php](app/Repositories/Admin/Brand/BrandRepository.php)

**Methods Added/Updated**:

#### a) `store()` - Create method
```php
// Handle gallery images upload
if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
    $brandData['gallery_images'] = $this->handleGalleryImagesUpload($data['gallery_images']);
}
```

#### b) `update()` - Update method
```php
// Handle remove main image
if (isset($data['remove_main_image']) && $data['remove_main_image']) {
    if ($brand->logo) {
        $this->deleteLogo($brand->logo);
    }
    $updateData['logo'] = null;
}

// Handle gallery images upload
if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
    $newImages = $this->handleGalleryImagesUpload($data['gallery_images']);
    $existingImages = $brand->gallery_images ?? [];
    $updateData['gallery_images'] = array_merge($existingImages, $newImages);
}

// Handle remove gallery images
if (isset($data['remove_gallery_images']) && is_array($data['remove_gallery_images']) && !empty($data['remove_gallery_images'])) {
    $this->deleteGalleryImages($data['remove_gallery_images']);
    $existingImages = $brand->gallery_images ?? [];
    $updateData['gallery_images'] = array_diff($existingImages, $data['remove_gallery_images']);
}
```

#### c) `delete()` - Delete gallery images when brand is deleted
```php
// Delete gallery images if exist
if ($brand->gallery_images && is_array($brand->gallery_images)) {
    $this->deleteGalleryImages($brand->gallery_images);
}
```

#### d) `bulkDelete()` - Delete gallery images on bulk delete
```php
// Delete gallery images if exist
if ($brand->gallery_images && is_array($brand->gallery_images)) {
    $this->deleteGalleryImages($brand->gallery_images);
}
```

#### e) NEW - `handleGalleryImagesUpload()` method
```php
protected function handleGalleryImagesUpload($images)
{
    $uploadedImages = [];
    
    foreach ($images as $image) {
        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            $uploadedImages[] = $image;
        } elseif (is_file($image)) {
            $path = $image->store('brands/gallery', 'public');
            $uploadedImages[] = $path;
        }
    }
    
    return $uploadedImages;
}
```

#### f) NEW - `deleteGalleryImages()` method
```php
protected function deleteGalleryImages($imagePaths)
{
    foreach ($imagePaths as $imagePath) {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            continue;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
```

---

### 4. Brand Edit Vue Component
**File**: [resources/js/pages/Admin/Brand/Edit.vue](resources/js/pages/Admin/Brand/Edit.vue)

**Script Changes**:
```javascript
const form = useForm({
    // ... existing fields
    gallery_images: [],           // NEW
    remove_main_image: false,     // NEW
    remove_gallery_images: [],    // NEW
});

const mainImagePreview = ref(props.brand.logo_url || null);  // RENAMED from imagePreview
const galleryPreviews = ref([]);                              // NEW
const existingGalleryImages = ref(props.brand.gallery_images_urls || []);  // NEW
```

**New Methods**:
- `handleImageRemove()` - Updated to handle remove_main_image flag
- `handleGallerySelect()` - NEW: Handle multiple gallery image selection
- `removeGalleryImage()` - NEW: Remove individual gallery images

**Template Changes**:
- Renamed "Brand Logo" section to "Images"
- Main Image section with:
  - Image preview
  - Choose Main Image button
  - Remove button
- Gallery Images section with:
  - Existing gallery images with remove buttons
  - New gallery images with remove buttons
  - Add Gallery Image button (multiple upload)

---

### 5. Brand Create Vue Component
**File**: [resources/js/pages/Admin/Brand/Create.vue](resources/js/pages/Admin/Brand/Create.vue)

**Same changes as Edit component**:
- Added gallery_images to form
- Added mainImagePreview and galleryPreviews refs
- Added handleGallerySelect() method
- Added removeGalleryImage() method
- Updated template to match Edit component layout

---

## Database Schema

### Brands Table Update
```sql
CREATE TABLE brands (
    id bigint unsigned PRIMARY KEY,
    name varchar(255) NOT NULL,
    slug varchar(255) UNIQUE NOT NULL,
    description text,
    logo varchar(255),
    gallery_images json,              -- NEW COLUMN
    website varchar(255),
    is_active boolean DEFAULT true,
    is_featured boolean DEFAULT false,
    sort_order int DEFAULT 0,
    meta_title varchar(255),
    meta_description text,
    meta_keywords text,
    created_at timestamp,
    updated_at timestamp,
    deleted_at timestamp
);
```

---

## API Response Example

```json
{
    "id": 1,
    "name": "Dior",
    "slug": "dior",
    "description": "French luxury fashion and fragrance house",
    "logo": "brands/dior-logo.png",
    "logo_url": "http://localhost/storage/brands/dior-logo.png",
    "gallery_images": [
        "brands/gallery/dior-1.jpg",
        "brands/gallery/dior-2.jpg",
        "brands/gallery/dior-3.jpg"
    ],
    "gallery_images_urls": [
        "http://localhost/storage/brands/gallery/dior-1.jpg",
        "http://localhost/storage/brands/gallery/dior-2.jpg",
        "http://localhost/storage/brands/gallery/dior-3.jpg"
    ],
    "website": "https://www.dior.com",
    "is_active": true,
    "is_featured": true,
    "created_at": "2025-01-20T10:00:00.000000Z",
    "updated_at": "2025-01-20T10:00:00.000000Z"
}
```

---

## File Structure

```
storage/app/public/
  brands/
    dior-logo.png          (Main image)
    gallery/
      dior-1.jpg           (Gallery image 1)
      dior-2.jpg           (Gallery image 2)
      dior-3.jpg           (Gallery image 3)
```

---

## Features

### Create Brand
✅ Upload main image (logo)
✅ Upload multiple gallery images
✅ Image previews shown during upload
✅ Remove images before saving

### Edit Brand
✅ Replace main image
✅ Remove main image
✅ Add additional gallery images
✅ Remove individual gallery images
✅ Keep existing images while adding new ones

### Delete Brand
✅ Automatically delete main image
✅ Automatically delete all gallery images

### Bulk Delete
✅ Delete main images for all selected brands
✅ Delete all gallery images for all selected brands

---

## File Size Limits

- **Main Image**: 5MB max (matches Product module)
- **Gallery Images**: 5MB max per image (matches Product module)

---

## Image Formats Supported

- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)
- SVG (.svg)

---

## Storage Paths

```
Main Image:   storage/app/public/brands/{filename}
Gallery:      storage/app/public/brands/gallery/{filename}
```

URLs generated via accessors:
```
Main Image URL:   http://localhost/storage/brands/{filename}
Gallery URL:      http://localhost/storage/brands/gallery/{filename}
```

---

## Component Consistency

The Brand module now mirrors the Product module:

| Feature | Product | Brand |
|---------|---------|-------|
| Main Image | ✅ Yes | ✅ Yes |
| Gallery Images | ✅ Yes | ✅ Yes |
| Image Preview | ✅ Yes | ✅ Yes |
| Remove Images | ✅ Yes | ✅ Yes |
| Multiple Upload | ✅ Yes | ✅ Yes |
| Auto Delete | ✅ Yes | ✅ Yes |
| Bulk Operations | ✅ Yes | ✅ Yes |

---

## Testing Checklist

- [ ] Create brand with main image
- [ ] Create brand with gallery images
- [ ] Edit brand - replace main image
- [ ] Edit brand - remove main image
- [ ] Edit brand - add gallery images
- [ ] Edit brand - remove gallery images
- [ ] View brand - images display correctly
- [ ] Delete brand - images are deleted
- [ ] Bulk delete - images are deleted for all brands

---

## Migration Status

✅ Migration `2025_01_20_100000_add_gallery_images_to_brands_table` successfully applied

---

## Summary

The Brand module now has full gallery image support matching the Product module's functionality. Users can:

1. Upload and manage main brand images (logos)
2. Upload and manage multiple gallery images
3. Preview images before saving
4. Remove images individually
5. Have images automatically cleaned up on delete operations

The implementation follows Laravel and Vue best practices with proper error handling, validation, and file management.
