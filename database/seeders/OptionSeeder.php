<?php

namespace Database\Seeders;

use App\Models\Admin\Option\Option;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'name' => 'Shipping Method',
                'slug' => 'shipping-method',
                'description' => 'Choose your preferred shipping method',
                'type' => 'select',
                'value' => json_encode([
                    ['value' => 'standard', 'label' => 'Standard Shipping'],
                    ['value' => 'express', 'label' => 'Express Shipping'],
                    ['value' => 'overnight', 'label' => 'Overnight Shipping'],
                ]),
                'is_active' => true,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Payment Method',
                'slug' => 'payment-method',
                'description' => 'Select payment method',
                'type' => 'radio',
                'value' => json_encode([
                    ['value' => 'credit_card', 'label' => 'Credit Card'],
                    ['value' => 'debit_card', 'label' => 'Debit Card'],
                    ['value' => 'paypal', 'label' => 'PayPal'],
                    ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ]),
                'is_active' => true,
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gift Wrap',
                'slug' => 'gift-wrap',
                'description' => 'Add gift wrapping to your order',
                'type' => 'checkbox',
                'value' => json_encode([
                    ['value' => 'yes', 'label' => 'Yes, add gift wrap'],
                ]),
                'is_active' => true,
                'is_required' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Special Instructions',
                'slug' => 'special-instructions',
                'description' => 'Any special instructions for your order',
                'type' => 'text',
                'value' => '',
                'is_active' => true,
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Delivery Date Preference',
                'slug' => 'delivery-date-preference',
                'description' => 'Preferred delivery date',
                'type' => 'text',
                'value' => '',
                'is_active' => true,
                'is_required' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Product Customization',
                'slug' => 'product-customization',
                'description' => 'Customization options for products',
                'type' => 'multiselect',
                'value' => json_encode([
                    ['value' => 'engraving', 'label' => 'Add Engraving'],
                    ['value' => 'monogram', 'label' => 'Add Monogram'],
                    ['value' => 'gift_message', 'label' => 'Add Gift Message'],
                ]),
                'is_active' => true,
                'is_required' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($options as $optionData) {
            Option::updateOrCreate(
                ['slug' => $optionData['slug']],
                $optionData
            );
        }
    }
}
