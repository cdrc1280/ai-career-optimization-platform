<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile()
            ->with(['skills', 'certifications', 'languages', 'educations', 'workExperiences', 'projects'])
            ->first();

        if (! $profile) {
            $profile = Profile::create(['user_id' => $request->user()->id]);
        }

        return response()->json($profile);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name'            => ['sometimes', 'string', 'max:191'],
            'professional_title'   => ['sometimes', 'string', 'max:191'],
            'phone'                => ['sometimes', 'nullable', 'string', 'max:50'],
            'location'             => ['sometimes', 'nullable', 'string', 'max:191'],
            'linkedin_url'         => ['sometimes', 'nullable', 'url'],
            'github_url'           => ['sometimes', 'nullable', 'url'],
            'portfolio_url'        => ['sometimes', 'nullable', 'url'],
            'years_of_experience'  => ['sometimes', 'nullable', 'integer', 'min:0', 'max:60'],
            'preferred_roles'      => ['sometimes', 'nullable', 'array'],
            'preferred_industries' => ['sometimes', 'nullable', 'array'],
            'preferred_locations'  => ['sometimes', 'nullable', 'array'],
            'expected_salary_min'  => ['sometimes', 'nullable', 'integer'],
            'expected_salary_max'  => ['sometimes', 'nullable', 'integer'],
            'currency'             => ['sometimes', 'string', 'size:3'],
            'employment_type'      => ['sometimes', 'nullable', 'in:full_time,part_time,contract,freelance,internship'],
            'career_goals'         => ['sometimes', 'nullable', 'string'],
        ]);

        $profile = $request->user()->profile ?? Profile::create(['user_id' => $request->user()->id]);
        $profile->fill($validated);
        $profile->save();

        $profile->update([
            'completion_percentage' => $profile->calculateCompletionPercentage(),
        ]);

        return response()->json($profile->load(['skills', 'certifications', 'languages', 'educations', 'workExperiences', 'projects']));
    }
}
