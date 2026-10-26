<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:remind-deadlines')->dailyAt('09:00');
Schedule::command('assignment:remind-deadlines')->dailyAt('09:00');
Schedule::command('study:send-reminders')->everyMinute()->withoutOverlapping();

Artisan::command('flashcards:check-import', function () {
    $missing = array_values(array_filter(
        ['ctype', 'dom', 'fileinfo', 'gd', 'iconv', 'libxml', 'mbstring', 'simplexml', 'xml', 'xmlreader', 'xmlwriter', 'zip'],
        fn ($extension) => ! extension_loaded($extension)
    ));
    if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) || $missing !== []) {
        $this->error('Excel import is unavailable. Install locked Composer dependencies and required PHP extensions.');
        if ($missing !== []) {
            $this->error('Missing PHP extensions: '.implode(', ', $missing));
        }
        return 1;
    }
    $this->info('Excel and CSV import dependencies are ready.');
    return 0;
})->purpose('Read-only check of flashcard Excel import dependencies');
