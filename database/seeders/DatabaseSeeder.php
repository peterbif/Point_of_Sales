<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

     $faker = Faker::create();

      $userId =  DB::table('users')->insertGetId([
            'username' => 'peterbif',
            'email' => 'peterbifme@gmail.com',
            'password' => bcrypt('Pe12te34@r'),
            'role' => 'superadmin2',
            'active' => 1,
           'created_at' => null,
            'updated_at' =>  now(),

        ]);
        
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'peterbifme2@gmail.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
            'active' => 1,
            'created_at' => now(),

            'updated_at' =>  now(),

        ]);



        DB::table('users')->insert([
            'username' => 'cashier',
            'email' => 'peterbifme3@gmail.com',
            'password' => bcrypt('123456'),
            'role' => 'cashier',
            'active' => 1,
            'created_at' => now(),

            'updated_at' =>  now(),

        ]);

        // insert dummy product category
        $dummyProducts = [
            'Rice' => [
                [
                    'name' => 'Pork Rice',
                    'inventory' => 5,
                    'unit_price' => 2000.00,
                    'wholesales_price'=> 1500.00,
                    'stock_alert_days'=> 60,
                    'stock_alert_qty_very_low'=> 5,
                    'stock_alert_qty_low'=> 10,
                ],
                [
                    'name' => 'Chicken Rice',
                    'inventory' => 3,
                    'unit_price' => 4500,
                    'wholesales_price'=> 15.00,
                      'stock_alert_days'=> 30,
                    'stock_alert_qty_very_low'=> 5,
                    'stock_alert_qty_low'=> 10,

                ],
                [
                    'name' => 'Fried Rice',
                    'inventory' => 7,
                    'unit_price' => 3800,
                    'wholesales_price'=> 3200.00,
                    'stock_alert_days'=> 20,
                    'stock_alert_qty_very_low'=> 5,
                    'stock_alert_qty_low'=> 10,

                ]
            ],
            'Noodle' => [
                [
                    'name' => 'Beef Noodle Soup',
                    'inventory' => 5,
                    'unit_price' => 1100.00,
                    'wholesales_price'=> 1000.00,
                    'stock_alert_days'=> 30,
                    'stock_alert_qty_very_low'=> 5,
                    'stock_alert_qty_low'=> 10,

                ],
                [
                    'name' => 'Seafood Noodle Soup',
                    'inventory' => 3,
                    'unit_price' => 1500.00,
                    'wholesales_price' => 1250.00,
                    'stock_alert_days'=> 10,
                    'stock_alert_qty_very_low'=> 5,
                    'stock_alert_qty_low'=> 10,


                ]
            ],
          
      
        ];

        

        foreach ($dummyProducts as $key => $value) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'name' => $key,
                'created_at' => now(),

                'updated_at' =>  now(),

            ]);
            foreach ($value as $product) {
            $productId  =  DB::table('products')->insertGetId([
                    'name' => $product['name'],
                   'barcode' => $faker->unique()->numerify('############'),
                   'expiry_date' => $faker->dateTimeBetween('now', '+3 months'),
                    'inventory' => $product['inventory'],
                    'unit_price' => $product['unit_price'],
                    'wholesales_price'=> $product['wholesales_price'],
                    'stock_alert_days'=> $product['stock_alert_days'],
                    'stock_alert_qty_very_low'=> $product['stock_alert_qty_very_low'],
                    'stock_alert_qty_low'=> $product['stock_alert_qty_low'],

                    'product_category_id' => $categoryId,
                    'created_at' => now(),

                    'updated_at' =>  now(),

                ]);


                DB::table('product_inventories')->insert([
                    'product_id' =>     $productId ,
                    'inventory' => $product['inventory'],
                    'user_id' => $userId,
                    
                ]);

            }

           
        
        }

        // Dummy Table
        // for ($i = 1; $i <= 10; $i++) {
        //     DB::table('tables')->insert([
        //         'name' => str_pad($i, 3, '0', STR_PAD_LEFT),
        //         'created_at' => now(),

        //         'updated_at' =>  now(),

        //     ]);
        // }
    }
}
