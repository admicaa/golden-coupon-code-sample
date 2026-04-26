<?php

use App\Models\Languages;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class mainLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $languages = [
            [
                'name' => 'English',
                'shortcut' => 'GB'
            ],
            [
                'name' => 'Arabic',
                'shortcut' => 'AR',
            ],

        ];
        $readMePath = database_path('seeds/defaults/home.md');
        foreach ($languages as $language) {
            $file = new UploadedFile($readMePath, File::name($readMePath));

            $lang = Languages::updateOrCreate($language, $language);
            if (!File::exists(front_storage($lang->shortcut . '/home.md'))) {
                Storage::disk('front')->putFileAs($lang->shortcut, $file, 'home.md');
            }
            if ($lang->shortcut != 'en') {
                if (!File::exists(resource_path('lang/' . $lang->shortcut))) {
                    File::makeDirectory(resource_path('lang/' . $lang->shortcut));
                    File::copy(resource_path('langMain/test.gitignore'), resource_path('lang/' . $lang->shortcut . '/.gitignore'));
                    File::copyDirectory(resource_path('langMain/en'), resource_path('lang/' . $lang->shortcut));
                }
            }
        }
    }
}
