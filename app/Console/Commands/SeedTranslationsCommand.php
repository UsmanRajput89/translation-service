<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Locale;
use App\Models\Tag;
use Faker\Factory;

class SeedTranslationsCommand extends Command
{
    protected $signature = 'app:seed-translations-command';
    protected $description = 'Seed translations with 100k+ entries';

    public function handle()
    {
        $this->info('Seeding started...');
        $faker = Factory::create();

        // Step 1: Seed locales
        $locales = [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
        ];

        $localeMap = [];
        foreach ($locales as $code => $name) {
            $locale = Locale::firstOrCreate(['code' => $code], ['name' => $name]);
            $localeMap[$code] = $locale->id;
        }

        // Step 2: Seed tags
        $tags = ['web', 'mobile', 'desktop'];
        foreach ($tags as $tag) {
            Tag::firstOrCreate(['name' => $tag]);
        }
        $tagIds = Tag::pluck('id')->all();

        // Step 3: Prepare batch insert
        $totalKeys = 1000; // unique keys per batch
        $repeat = 34; // to reach 100k+
        $translations = [];
        $pivot = [];
        $now = now();

        $this->output->progressStart($totalKeys * $repeat);

        for ($batch = 0; $batch < $repeat; $batch++) {
            for ($i = 0; $i < $totalKeys; $i++) {
                $key = "key_{$batch}_{$i}";

                foreach ($localeMap as $code => $localeId) {
                    $content = $faker->sentence();

                    $translations[] = [
                        'key' => $key,
                        'locale_id' => $localeId,
                        'content' => $content,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Attach 1-3 random tags
                    $selectedTags = collect($tagIds)->shuffle()->take(rand(1, 3));
                    foreach ($selectedTags as $tagId) {
                        $pivot[] = [
                            'tag_id' => $tagId,
                            'key' => $key,
                            'locale_id' => $localeId,
                        ];
                    }
                }

                // Insert in batches of 1000
                if (count($translations) >= 1000) {
                    $this->insertTranslations($translations, $pivot);
                    $translations = [];
                    $pivot = [];
                }

                $this->output->progressAdvance();
            }
        }

        // Insert remaining
        if (count($translations)) {
            $this->insertTranslations($translations, $pivot);
        }

        $this->output->progressFinish();
        $this->info('Done seeding 100k+ translations!');
    }

    protected function insertTranslations(array $translations, array $pivot)
    {
        DB::table('translations')->insert($translations);

        // Get inserted translations by key + locale_id
        $inserted = DB::table('translations')
            ->whereIn('key', array_column($translations, 'key'))
            ->get(['id', 'key', 'locale_id']);

        $pivotRows = [];

        foreach ($pivot as $p) {
            $translation = $inserted->first(function ($t) use ($p) {
                return $t->key === $p['key'] && $t->locale_id == $p['locale_id'];
            });

            if ($translation) {
                $pivotRows[] = [
                    'translation_id' => $translation->id,
                    'tag_id' => $p['tag_id'],
                ];
            }
        }

        DB::table('tag_translation')->insert($pivotRows);
    }
}
