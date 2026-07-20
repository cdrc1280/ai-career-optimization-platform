<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\CoverLetterController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\ResumeAnalysisController;
use App\Http\Controllers\Api\ResumeController;
use App\Http\Controllers\Api\ResumeOptimizationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| All routes require an authenticated Sanctum token. AI-consuming endpoints
| get a tighter rate limit than plain CRUD to protect against runaway
| provider costs — tune 'ai' in RouteServiceProvider/bootstrap/app.php.
*/

Route::post('login', [LoginController::class, 'loginApi']);
Route::post('register', [RegisterController::class, 'registerApi']);

// Development helper: create/find a local test user and return a token.
// Only available in non-production environments.
if (app()->environment('local') || config('app.debug')) {
    Route::get('debug/token', function () {
        $email = 'apitest-debug@example.com';
        $password = 'secret123';

        $userModel = \App\Models\User::where('email', $email)->first();
        if (!$userModel) {
            $userModel = \App\Models\User::create([
                'name' => 'API Debug User',
                'email' => $email,
                'password' => bcrypt($password),
            ]);
        }

        return response()->json(['token' => $userModel->createToken('debug-token')->plainTextToken, 'email' => $email]);
    });
}

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('resumes', ResumeController::class)->except(['update']);

    Route::apiResource('job-postings', JobPostingController::class)->except(['update']);

    Route::middleware('throttle:ai')->group(function () {
        Route::post('resume-analyses', [ResumeAnalysisController::class, 'store']);
        Route::get('resume-analyses/{resumeVersionId}/{jobPostingId}', [ResumeAnalysisController::class, 'show']);

        Route::post('resume-optimizations', [ResumeOptimizationController::class, 'store']);
        Route::get('resume-versions/{resumeVersion}/compare', [ResumeOptimizationController::class, 'compare']);

        Route::post('cover-letters', [CoverLetterController::class, 'store']);
    });

    Route::apiResource('applications', ApplicationController::class)->except(['show']);
    // Route::apiResource('applications', ApplicationController::class)->except(['show']);
});
