<?php

namespace App\Services;

use App\Models\Certification;
use App\Models\Education;
use App\Models\Language;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\WorkExperience;

class ProfileSyncService
{
    /**
     * Syncs parsed resume JSON data into the normalized profile tables.
     * Safe to call multiple times — it upserts rather than duplicating.
     */
    public function sync(Resume $resume, array $parsed): void
    {
        $profile = Profile::firstOrCreate(
            ['user_id' => $resume->user_id],
            ['completion_percentage' => 0]
        );

        // Personal information
        $personalInfo = $parsed['personal_info'] ?? [];
        $profile->fill([
            'full_name'          => $personalInfo['full_name']   ?? $profile->full_name,
            'phone'              => $personalInfo['phone']        ?? $profile->phone,
            'location'           => $personalInfo['location']     ?? $profile->location,
            'linkedin_url'       => $personalInfo['linkedin_url'] ?? $profile->linkedin_url,
            'github_url'         => $personalInfo['github_url']   ?? $profile->github_url,
            'portfolio_url'      => $personalInfo['portfolio_url'] ?? $profile->portfolio_url,
            'professional_title' => $parsed['professional_title'] ?? $profile->professional_title,
            'career_goals'       => $parsed['summary']           ?? $profile->career_goals,
        ]);
        $profile->save();

        // Work experiences
        foreach ($parsed['work_experience'] ?? [] as $exp) {
            WorkExperience::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'company'    => $exp['company'] ?? '',
                    'title'      => $exp['title']   ?? '',
                ],
                [
                    'location'         => $exp['location']   ?? null,
                    'start_date'       => $this->parseDate($exp['start_date'] ?? null),
                    'end_date'         => $this->parseDate($exp['end_date'] ?? null),
                    'is_current'       => $exp['is_current'] ?? false,
                    'responsibilities' => $exp['responsibilities'] ?? [],
                    'achievements'     => $exp['achievements']     ?? [],
                ]
            );
        }

        // Educations
        foreach ($parsed['education'] ?? [] as $edu) {
            Education::updateOrCreate(
                [
                    'profile_id'  => $profile->id,
                    'institution' => $edu['institution'] ?? '',
                ],
                [
                    'degree'         => $edu['degree']         ?? null,
                    'field_of_study' => $edu['field_of_study'] ?? null,
                    'start_date'     => $this->parseDate($edu['start_date'] ?? null),
                    'end_date'       => $this->parseDate($edu['end_date']   ?? null),
                ]
            );
        }

        // Skills — create and sync
        $skillIds = [];
        foreach ($parsed['skills'] ?? [] as $skillName) {
            $skill      = Skill::firstOrCreate(['name' => trim($skillName)]);
            $skillIds[] = $skill->id;
        }
        if (! empty($skillIds)) {
            $profile->skills()->syncWithoutDetaching($skillIds);
        }

        // Certifications
        foreach ($parsed['certifications'] ?? [] as $cert) {
            Certification::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name'       => $cert['name'] ?? '',
                ],
                [
                    'issuing_organization' => $cert['issuing_organization'] ?? null,
                    'issued_date'          => $this->parseDate($cert['issued_date'] ?? null),
                ]
            );
        }

        // Languages
        foreach ($parsed['languages'] ?? [] as $lang) {
            Language::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name'       => $lang['name'] ?? '',
                ],
                [
                    'proficiency' => $lang['proficiency'] ?? 'conversational',
                ]
            );
        }

        // Projects
        foreach ($parsed['projects'] ?? [] as $proj) {
            Project::updateOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name'       => $proj['name'] ?? '',
                ],
                [
                    'description'  => $proj['description']  ?? null,
                    'technologies' => $proj['technologies']  ?? [],
                ]
            );
        }

        // Recalculate and save completion percentage
        $profile->refresh();
        $profile->update([
            'completion_percentage' => $profile->calculateCompletionPercentage(),
        ]);
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Handle formats like "2021-03", "2021", "March 2021"
        try {
            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}
