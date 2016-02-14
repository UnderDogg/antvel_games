<?php

/**
 * Antvel - Seeder
 * Users Admin.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use App\Business;
use App\Person;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        //create basic admin user
        $faker = Faker::create();
        Person::create([
            'first_name' => 'Admin',
            'last_name'  => 'root',
            'birthday'   => $faker->dateTimeBetween('-40 years', '-16 years'),
            'sex'        => 'male',
            'home_phone'   => $faker->phoneNumber,
            'user'       => [
                'nickname'    => 'admin',
                'email'       => 'admin@admin.com',
                'role'        => 'admin',
                'type'        => 'trusted',
                'password'    => \Hash::make('admin'),
                'pic_url'     => '/img/pt-default/'.$faker->numberBetween(1, 20).'.jpg',
                'twitter'     => '@websarrollo',
                'facebook'    => 'websarrollo',
                'preferences' => '{"product_viewed":[],"product_purchased":[],"product_shared":[],"product_categories":[],"my_searches":[]}',
            ],
        ]);

        //developer (admin)
        $admin = Person::create([
            'first_name' => 'AntVel',
            'last_name'  => 'Developer',
            'birthday'   => $faker->dateTimeBetween('-40 years', '-16 years'),
            'sex'        => 'male',
            'home_phone'   => $faker->phoneNumber,
            'user'       => [
                'nickname' => 'dev',
                'email'    => 'dev@antvel.com',
                'role'     => 'admin',
                'type'     => 'trusted',
                'password' => \Hash::make('123456'),
                'pic_url'  => '/img/pt-default/'.$faker->numberBetween(1, 20).'.jpg',
                'twitter'  => '@websarrollo',
                'facebook' => 'websarrollo',
            ],
        ]);
        //seller
        $company_name = 'antvel seller';
        $seller = Business::create([
            'business_name' => $company_name,
            'creation_date' => $faker->date(),
            'local_phone'   => $faker->phoneNumber,
            'user'          => [
                'nickname' => 'antvelseller',
                'email'    => 'seller@antvel.com',
                'password' => Hash::make('123456'),
                'pic_url'  => '/img/pt-default/'.$faker->numberBetween(1, 20).'.jpg',
                'twitter'  => '@seller',
                'facebook' => $company_name,
            ],
        ])->user;
        //buyer

		$buyer = Person::create([
            'first_name' => $faker->firstName,
            'last_name'  => $faker->lastName,
            'birthday'   => $faker->dateTimeBetween('-40 years', '-16 years'),
            'sex'        => 'male',
            'home_phone'   => $faker->phoneNumber,
            'user'       => [
                'nickname' => 'antvelbuyer',
                'email'    => 'buyer@antvel.com',
                'password' => Hash::make('123456'),
                'pic_url'  => '/img/pt-default/'.$faker->numberBetween(1, 20).'.jpg',
                'twitter'  => '@buyer',
                'facebook' => 'buyer',
            ],
        ])->user;

    }
}
