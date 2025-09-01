<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicine;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $medicines = [
            // Analgesik dan Antipiretik
            [
                'name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'strength' => '500mg',
                'unit' => 'tablet',
                'stock_quantity' => 100,
                'minimum_stock' => 20,
                'description' => 'Obat pereda nyeri dan penurun demam',
                'is_active' => true,
            ],
            [
                'name' => 'Ibuprofen',
                'generic_name' => 'Ibuprofen',
                'strength' => '400mg',
                'unit' => 'tablet',
                'stock_quantity' => 80,
                'minimum_stock' => 15,
                'description' => 'Anti-inflamasi non-steroid (NSAID)',
                'is_active' => true,
            ],
            [
                'name' => 'Asam Mefenamat',
                'generic_name' => 'Mefenamic Acid',
                'strength' => '500mg',
                'unit' => 'kapsul',
                'stock_quantity' => 60,
                'minimum_stock' => 10,
                'description' => 'Obat anti-inflamasi dan pereda nyeri',
                'is_active' => true,
            ],

            // Antibiotik
            [
                'name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'strength' => '500mg',
                'unit' => 'kapsul',
                'stock_quantity' => 120,
                'minimum_stock' => 25,
                'description' => 'Antibiotik spektrum luas',
                'is_active' => true,
            ],
            [
                'name' => 'Cotrimoxazole',
                'generic_name' => 'Sulfamethoxazole + Trimethoprim',
                'strength' => '480mg',
                'unit' => 'tablet',
                'stock_quantity' => 90,
                'minimum_stock' => 20,
                'description' => 'Antibiotik kombinasi',
                'is_active' => true,
            ],

            // Obat Saluran Pencernaan
            [
                'name' => 'Antasida',
                'generic_name' => 'Aluminum Hydroxide + Magnesium Hydroxide',
                'strength' => '200mg',
                'unit' => 'tablet',
                'stock_quantity' => 150,
                'minimum_stock' => 30,
                'description' => 'Obat maag dan asam lambung',
                'is_active' => true,
            ],
            [
                'name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'strength' => '20mg',
                'unit' => 'kapsul',
                'stock_quantity' => 70,
                'minimum_stock' => 15,
                'description' => 'Penghambat pompa proton untuk asam lambung',
                'is_active' => true,
            ],
            [
                'name' => 'Loperamide',
                'generic_name' => 'Loperamide HCl',
                'strength' => '2mg',
                'unit' => 'kapsul',
                'stock_quantity' => 50,
                'minimum_stock' => 10,
                'description' => 'Obat anti-diare',
                'is_active' => true,
            ],
            [
                'name' => 'ORS',
                'generic_name' => 'Oral Rehydration Salt',
                'strength' => '200ml',
                'unit' => 'sachet',
                'stock_quantity' => 200,
                'minimum_stock' => 50,
                'description' => 'Garam rehidrasi oral untuk diare',
                'is_active' => true,
            ],

            // Obat Batuk dan Flu
            [
                'name' => 'Dextromethorphan',
                'generic_name' => 'Dextromethorphan HBr',
                'strength' => '15mg',
                'unit' => 'tablet',
                'stock_quantity' => 80,
                'minimum_stock' => 15,
                'description' => 'Obat batuk kering',
                'is_active' => true,
            ],
            [
                'name' => 'Guaifenesin',
                'generic_name' => 'Guaifenesin',
                'strength' => '100mg',
                'unit' => 'tablet',
                'stock_quantity' => 75,
                'minimum_stock' => 15,
                'description' => 'Ekspektoran untuk batuk berdahak',
                'is_active' => true,
            ],
            [
                'name' => 'Sirup Obat Batuk',
                'generic_name' => 'Bromhexine HCl',
                'strength' => '60ml',
                'unit' => 'botol',
                'stock_quantity' => 40,
                'minimum_stock' => 8,
                'description' => 'Sirup obat batuk untuk anak',
                'is_active' => true,
            ],

            // Vitamin dan Suplemen
            [
                'name' => 'Vitamin B Complex',
                'generic_name' => 'Vitamin B Complex',
                'strength' => '50mg',
                'unit' => 'tablet',
                'stock_quantity' => 100,
                'minimum_stock' => 20,
                'description' => 'Suplemen vitamin B kompleks',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin C',
                'generic_name' => 'Ascorbic Acid',
                'strength' => '500mg',
                'unit' => 'tablet',
                'stock_quantity' => 120,
                'minimum_stock' => 25,
                'description' => 'Suplemen vitamin C',
                'is_active' => true,
            ],
            [
                'name' => 'Tablet Zat Besi',
                'generic_name' => 'Ferrous Sulfate',
                'strength' => '200mg',
                'unit' => 'tablet',
                'stock_quantity' => 90,
                'minimum_stock' => 20,
                'description' => 'Suplemen zat besi untuk anemia',
                'is_active' => true,
            ],

            // Obat Topikal
            [
                'name' => 'Betadine',
                'generic_name' => 'Povidone Iodine',
                'strength' => '10%',
                'unit' => 'botol',
                'stock_quantity' => 30,
                'minimum_stock' => 5,
                'description' => 'Antiseptik untuk luka',
                'is_active' => true,
            ],
            [
                'name' => 'Salep Mata Chloramphenicol',
                'generic_name' => 'Chloramphenicol',
                'strength' => '1%',
                'unit' => 'tube',
                'stock_quantity' => 25,
                'minimum_stock' => 5,
                'description' => 'Salep antibiotik untuk mata',
                'is_active' => true,
            ],

            // Obat Hipertensi
            [
                'name' => 'Amlodipine',
                'generic_name' => 'Amlodipine Besylate',
                'strength' => '5mg',
                'unit' => 'tablet',
                'stock_quantity' => 100,
                'minimum_stock' => 20,
                'description' => 'Obat hipertensi golongan CCB',
                'is_active' => true,
            ],
            [
                'name' => 'Captopril',
                'generic_name' => 'Captopril',
                'strength' => '25mg',
                'unit' => 'tablet',
                'stock_quantity' => 80,
                'minimum_stock' => 15,
                'description' => 'Obat hipertensi golongan ACE inhibitor',
                'is_active' => true,
            ],

            // Obat Diabetes
            [
                'name' => 'Metformin',
                'generic_name' => 'Metformin HCl',
                'strength' => '500mg',
                'unit' => 'tablet',
                'stock_quantity' => 150,
                'minimum_stock' => 30,
                'description' => 'Obat diabetes tipe 2',
                'is_active' => true,
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }
    }
}