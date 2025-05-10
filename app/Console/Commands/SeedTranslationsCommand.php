<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class SeedTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-translations-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locales = ['en', 'fr', 'es'];
        $tags = ['web', 'mobile', 'desktop'];

        // Seed Locales
        foreach ($locales as $code) {
            \App\Models\Locale::firstOrCreate(['code' => $code], ['name' => $code]);
        }

        // Seed Tags
        foreach ($tags as $tag) {
            \App\Models\Tag::firstOrCreate(['name' => $tag]);
        }

        // Seed 100 translations (in chunks)
        $chunkSize = 10;
        $totalRecords = 100;

        for ($i = 0; $i < $totalRecords; $i += $chunkSize) {
            // Correct way to use factories
            $translations = \App\Models\Translation::factory()->count($chunkSize)->create();

            // Attach random tags (1-3 per translation)
            $translations->each(function ($translation) {
                $translation->tags()->attach(
                    \App\Models\Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                );
            });
        }

        $this->info("Successfully seeded $totalRecords translations!");
    }
}
