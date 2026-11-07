<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'order', 'is_active'];

    protected $appends = ['name_fr', 'description_fr'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function topics()
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function getNameFrAttribute()
    {
        return $this->name;
    }

    public function getDescriptionFrAttribute()
    {
        return $this->description;
    }
}
