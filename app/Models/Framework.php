<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Framework extends Model
{
    use HasFactory;

    protected $fillable = ['programming_language_id', 'name', 'slug', 'icon'];

    public function language()
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'framework_project');
    }
}
