<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IksRecommendation extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'indicator_code',
        'recommendation_type',
        'title',
        'description',
        'priority_score',
        'priority_level',
        'actions',
        'resources',
        'expected_days_to_complete',
        'difficulty_level',
        'status',
        'target_date',
        'completed_date',
        'notes'
    ];

    protected $casts = [
        'priority_score' => 'float',
        'actions' => 'json',
        'resources' => 'json',
        'expected_days_to_complete' => 'integer',
        'target_date' => 'date',
        'completed_date' => 'date',
    ];

    /**
     * Relasi ke model Family
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Relasi ke User yang membuat rekomendasi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menetapkan status selesai
     */
    public function markAsCompleted(string $notes = null): self
    {
        $this->status = 'completed';
        $this->completed_date = now();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }

    /**
     * Menetapkan status dalam progress
     */
    public function markAsInProgress(string $notes = null): self
    {
        $this->status = 'in_progress';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }

    /**
     * Menetapkan status ditolak
     */
    public function markAsRejected(string $notes = null): self
    {
        $this->status = 'rejected';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }

    /**
     * Mendapatkan warna berdasarkan status
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Mendapatkan warna berdasarkan prioritas
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority_level) {
            'High' => 'danger',
            'Medium' => 'warning',
            'Low' => 'success',
            default => 'gray',
        };
    }

    /**
     * Mendapatkan ikon berdasarkan indikator
     */
    public function getIndicatorIconAttribute(): string
    {
        return match ($this->indicator_code) {
            'kb' => 'heroicon-o-heart',
            'birth_facility' => 'heroicon-o-building-office-2',
            'immunization' => 'heroicon-o-beaker',
            'exclusive_breastfeeding' => 'heroicon-o-face-smile',
            'growth_monitoring' => 'heroicon-o-chart-bar',
            'tb_treatment' => 'heroicon-o-bug-ant',
            'hypertension_treatment' => 'heroicon-o-heart',
            'mental_treatment' => 'heroicon-o-brain',
            'no_smoking' => 'heroicon-o-no-symbol',
            'jkn_membership' => 'heroicon-o-identification',
            'clean_water' => 'heroicon-o-beaker',
            'sanitary_toilet' => 'heroicon-o-home',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
