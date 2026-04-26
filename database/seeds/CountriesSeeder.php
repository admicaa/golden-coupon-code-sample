<?php

use App\Models\Country;
use App\Models\Languages;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $countries = [
            [
                'name' => 'Egypt',
                'iso' => 'EG',
                'image_path' => '/images/eg.svg'
            ],
            [
                'name' => 'United Arab Emirates',
                'iso' => 'AE',
                'image_path' => '/images/ae.svg'
            ],
            [
                'name' => 'Turkey',
                'iso' => 'TR',
                'image_path' => '/images/tr.svg'
            ],
        ];
        $languages = Languages::all();
        foreach ($countries as $country) {
            $countryy = Country::updateOrCreate(['iso' => $country['iso']], ['image_path' => $country['image_path']]);
            foreach ($languages as $language) {
                $countryy->names()->firstOrCreate([
                    'language' => $language->shortcut,
                    'name' => $country['name']
                ]);
            }
        }
    }
}
