<?php

namespace Database\Seeders;

use App\Models\Admin\Attribute\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Color',
                'slug' => 'color',
                'description' => 'Product color options',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Size',
                'slug' => 'size',
                'description' => 'Product size options',
                'type' => 'select',
                'is_required' => true,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'description' => 'Product material type',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Weight',
                'slug' => 'weight',
                'description' => 'Product weight in kg',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_searchable' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Warranty',
                'slug' => 'warranty',
                'description' => 'Product warranty period',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_searchable' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($attributes as $attributeData) {
            Attribute::updateOrCreate(
                ['slug' => $attributeData['slug']],
                $attributeData
            );
        }
    }
}
