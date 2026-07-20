<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'full_name', 'professional_title', 'phone', 'location',
        'linkedin_url', 'github_url', 'portfolio_url', 'avatar_path',
        'years_of_experience', 'preferred_roles', 'preferred_industries',
        'preferred_locations', 'expected_salary_min', 'expected_salary_max',
        'currency', 'employment_type', 'career_goals', 'completion_percentage',
    ];

    protected function casts(): array
    {
        return [
            'preferred_roles' => 'array',
            'preferred_industries' => 'array',
            'preferred_locations' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'profile_skill')
            ->withPivot('proficiency')
            ->withTimestamps();
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('sort_order');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('sort_order');
    }

    /**
     * Recalculates the profile completion percentage from filled sections.
     * Called from the ProfileService whenever a related section is saved —
     * kept here as the single source of truth for "what counts as complete".
     */
    public function calculateCompletionPercentage(): int
    {
        $sections = [
            (bool) $this->full_name,
            (bool) $this->professional_title,
            (bool) $this->location,
            $this->skills()->exists(),
            $this->educations()->exists(),
            $this->workExperiences()->exists(),
            (bool) $this->years_of_experience,
            (bool) $this->career_goals,
        ];

        $filled = count(array_filter($sections));

        return (int) round(($filled / count($sections)) * 100);
    }
}
