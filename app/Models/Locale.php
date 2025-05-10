<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
