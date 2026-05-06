<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LanugageChecker extends Command
{
    protected $signature = 'check:translation';

    protected $description = 'This command will check translation files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $mainDirectory = resource_path('langMain/en');
        $mainDirectoryFiles = $this->getFilesList($mainDirectory);
        $languages = glob(resource_path('lang') . '/*', GLOB_ONLYDIR);

        foreach ($languages as $language) {
            $this->checkForLanuage($language, $mainDirectoryFiles, $mainDirectory);
        }
    }

    public function checkForLanuage($directory, $files, $mainDirectory)
    {
        foreach ($files as $file => $directoryFiles) {

            $this->makeFile($directory . '/' . $file, $mainDirectory . '/' . $file);
            if (gettype($directoryFiles) == 'array') {
                $this->checkForLanuage($directory  . $file, $directoryFiles, $mainDirectory  . $file);
            } else {
                $this->transFile($mainDirectory . $file, $directory . $file);
            }
        }
    }

    public function transFile($mainPath, $translatedPath)
    {
        $this->info('checking ' . $translatedPath . ' ....');
        if (!is_dir($mainPath)) {
            $mainWords = require $mainPath;

            $otherWords = require $translatedPath;
            $otherWords = $this->translateArray($mainWords, $otherWords);

            $this->UpdateFile($translatedPath, $otherWords);
            $this->info('File : ' . $translatedPath . '. Had been translated succesfully');
        }
    }

    public function translateArray($mainWords, $otherWords)
    {
        foreach ($mainWords as $key => $word) {
            if (!array_key_exists($key, $otherWords) || gettype($mainWords[$key]) != gettype($otherWords[$key])) {
                $otherWords[$key] = $mainWords[$key];
            }
            if (is_array($mainWords[$key])) {
                $otherWords[$key] = $this->translateArray($mainWords[$key], $otherWords[$key]);
            } else {
                if (!isset($otherWords[$key])) {
                    $otherWords[$key] = $mainWords[$key];
                }
            }
        }
        return $otherWords;
    }

    public function makeFile($file, $mainFile)
    {

        if (!File::exists($file)) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "") {
                File::copyDirectory($mainFile, $file);
            } else {

                File::copy($mainFile, $file);
            }
        }
    }

    public function UpdateFile($fullPath, $content)
    {
        $content = '<?php return ' . var_export($content, true) . ';';

        File::put($fullPath, $content);
    }

    public function getFiles($langPath)
    {
        $files   = glob($langPath . '/*');
        return $files;
    }

    public function getFilesList($path)
    {
        $FilesList = [];





        $files = $this->getFiles($path);
        foreach ($files as $file) {
            $fileName = preg_replace('/^' . preg_quote($path, '/') . '/', '', $file);
            if (!isset($FilesList[$fileName])) {
                $FilesList[$fileName] = [];
            }
            $FilesList[$fileName] = true;

            if (is_dir($file)) {
                $FilesList[$fileName] = $this->getFilesList($file);
            }
        }

        return $FilesList;
    }
}
