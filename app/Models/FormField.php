<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'placeholder',
        'help_text',
        'is_required',
        'options',
        'order',
        'conditional_logic',
        'config',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'conditional_logic' => 'array',
        'config' => 'array',
        'order' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
