<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('translation_tag_pivot', 'tag_translation');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('tag_translation', 'translation_tag_pivot');
    }
};
