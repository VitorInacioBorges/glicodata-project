<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireModel extends Model
{
    use HasUuids;

    protected $table = 'questionnaires';

    protected $fillable = ['code', 'title', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuestionnaireVersionModel::class, 'questionnaire_id');
    }
}
