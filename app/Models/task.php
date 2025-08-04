<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status_id',
        'creator_id',
        'assignee_id',
        'report',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi belongsTo dengan User sebagai creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relasi belongsTo dengan User sebagai assignee
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Relasi belongsTo dengan Status
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Scope untuk tugas yang aktif (todo dan doing)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->whereIn('name', ['todo', 'doing']);
        });
    }

    /**
     * Scope untuk tugas yang selesai
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('name', 'done');
        });
    }

    /**
     * Scope untuk tugas yang dibatalkan
     */
    public function scopeCanceled($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('name', 'canceled');
        });
    }

    /**
     * Get status name
     */
    public function getStatusNameAttribute()
    {
        return $this->status ? $this->status->name : null;
    }
}
