<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammingLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'color'];

    public function frameworks()
    {
        return $this->hasMany(Framework::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_programming_language');
    }
}
