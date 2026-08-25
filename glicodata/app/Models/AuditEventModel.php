<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEventModel extends Model
{
    /** @use HasFactory<\Database\Factories\AuditEventFactory> */
    use HasFactory, HasUuids;

    protected $table = 'audit_events';

    protected $fillable = [
        'actor_ubs_id',
        'actor_administrator_id',
        'owner_ubs_id',
        'subject_type',
        'subject_id',
        'action',
        'changed_fields',
        'redacted_at',
        'redacted_by_ubs_id',
        'redacted_by_administrator_id',
        'redaction_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'redacted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<UbsModel, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'actor_ubs_id');
    }

    /**
     * @return BelongsTo<AdministratorModel, $this>
     */
    public function administratorActor(): BelongsTo
    {
        return $this->belongsTo(AdministratorModel::class, 'actor_administrator_id');
    }

    /**
     * @return BelongsTo<UbsModel, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'owner_ubs_id');
    }
}
