<?php

namespace App\Console\Commands;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Console\Command;
use Faker\Factory;

class SeedTranslationsCommand extends Command
{
    protected $signature = 'app:seed-translations-command';
    protected $description = 'Seed translations with locales and tags';

    public function handle()
    {
        $faker = Factory::create();

        $locales = ['en', 'fr', 'es'];
        foreach ($locales as $code) {
            Locale::firstOrCreate(['code' => $code], ['name' => $code]);
        }

        $tags = ['web', 'mobile', 'desktop'];
        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag]);
        }

        $uniqueKeys = [];
        for ($i = 0; $i < 30; $i++) {
            $uniqueKeys[] = 'key_' . $faker->unique()->word;
        }

        foreach ($uniqueKeys as $key) {
            foreach ($locales as $localeCode) {
                $translation = Translation::create([
                    'key' => $key,
                    'locale_id' => Locale::where('code', $localeCode)->first()->id,
                    'content' => "[$localeCode] Content for $key",
                ]);

                // Attach 1-3 random tags
                $translation->tags()->attach(
                    Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                );
            }
        }

        $this->info("Successfully seeded 90 translations (30 keys × 3 locales)!");
    }
}
