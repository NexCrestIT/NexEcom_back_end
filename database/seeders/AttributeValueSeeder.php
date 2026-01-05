<?php

namespace Database\Seeders;

use App\Models\Admin\Attribute\Attribute;
use App\Models\Admin\Attribute\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Color attribute values
        $colorAttribute = Attribute::where('slug', 'color')->first();
        if ($colorAttribute) {
            $colors = [
                ['value' => 'Red', 'display_value' => 'Red', 'color_code' => '#FF0000', 'sort_order' => 1],
                ['value' => 'Blue', 'display_value' => 'Blue', 'color_code' => '#0000FF', 'sort_order' => 2],
                ['value' => 'Green', 'display_value' => 'Green', 'color_code' => '#00FF00', 'sort_order' => 3],
                ['value' => 'Yellow', 'display_value' => 'Yellow', 'color_code' => '#FFFF00', 'sort_order' => 4],
                ['value' => 'Black', 'display_value' => 'Black', 'color_code' => '#000000', 'sort_order' => 5],
                ['value' => 'White', 'display_value' => 'White', 'color_code' => '#FFFFFF', 'sort_order' => 6],
                ['value' => 'Gray', 'display_value' => 'Gray', 'color_code' => '#808080', 'sort_order' => 7],
                ['value' => 'Purple', 'display_value' => 'Purple', 'color_code' => '#800080', 'sort_order' => 8],
            ];

            foreach ($colors as $color) {
                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $colorAttribute->id,
                        'value' => $color['value'],
                    ],
                    array_merge($color, [
                        'attribute_id' => $colorAttribute->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        // Size attribute values
        $sizeAttribute = Attribute::where('slug', 'size')->first();
        if ($sizeAttribute) {
            $sizes = [
                ['value' => 'XS', 'display_value' => 'Extra Small', 'sort_order' => 1],
                ['value' => 'S', 'display_value' => 'Small', 'sort_order' => 2],
                ['value' => 'M', 'display_value' => 'Medium', 'sort_order' => 3],
                ['value' => 'L', 'display_value' => 'Large', 'sort_order' => 4],
                ['value' => 'XL', 'display_value' => 'Extra Large', 'sort_order' => 5],
                ['value' => 'XXL', 'display_value' => 'Double Extra Large', 'sort_order' => 6],
            ];

            foreach ($sizes as $size) {
                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $sizeAttribute->id,
                        'value' => $size['value'],
                    ],
                    array_merge($size, [
                        'attribute_id' => $sizeAttribute->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        // Material attribute values
        $materialAttribute = Attribute::where('slug', 'material')->first();
        if ($materialAttribute) {
            $materials = [
                ['value' => 'Cotton', 'display_value' => 'Cotton', 'sort_order' => 1],
                ['value' => 'Polyester', 'display_value' => 'Polyester', 'sort_order' => 2],
                ['value' => 'Leather', 'display_value' => 'Leather', 'sort_order' => 3],
                ['value' => 'Metal', 'display_value' => 'Metal', 'sort_order' => 4],
                ['value' => 'Plastic', 'display_value' => 'Plastic', 'sort_order' => 5],
                ['value' => 'Wood', 'display_value' => 'Wood', 'sort_order' => 6],
            ];

            foreach ($materials as $material) {
                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $materialAttribute->id,
                        'value' => $material['value'],
                    ],
                    array_merge($material, [
                        'attribute_id' => $materialAttribute->id,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
