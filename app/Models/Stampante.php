<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stampante extends Model
{
    use HasFactory;

    protected $table = 'stampanti';

    public function casse(): HasMany
    {
        return $this->hasMany(Cassa::class);
    }

    public function postazioni(): HasMany
    {
        return $this->hasMany(Postazione::class);
    }
}
