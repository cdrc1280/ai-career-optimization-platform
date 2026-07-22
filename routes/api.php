<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\CoverLetterController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ResumeAnalysisController;
use App\Http\Controllers\Api\ResumeController;
use App\Http\Controllers\Api\ResumeExportController;
use App\Http\Controllers\Api\ResumeOptimizationController;
use App\Http\Controllers\Api\ResumeVersionUpdateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ResumeVersionController;
use App\Http\Controllers\Api\CoverLetterExportController;
use App\Http\Controllers\RecruiterController;
use App\Http\Controllers\ExtensionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| All routes require an authenticated Sanctum token. AI-consuming endpoints
| get a tighter rate limit than plain CRUD to protect against runaway
| provider costs.
*/

Route::post('login', [LoginController::class, 'loginApi']);
Route::post('register', [RegisterController::class, 'registerApi']);

// Development helper: create/find a local test user and return a token.
// Only available in non-production environments.
if (app()->environment('local') || config('app.debug')) {
    Route::get('debug/token', function () {
        $email    = 'apitest-debug@example.com';
        $password = 'secret123';

        $userModel = \App\Models\User::where('email', $email)->first();
        if (! $userModel) {
            $userModel = \App\Models\User::create([
                'name'     => 'API Debug User',
                'email'    => $email,
                'password' => bcrypt($password),
            ]);
        }

        return response()->json(['token' => $userModel->createToken('debug-token')->plainTextToken, 'email' => $email]);
    });
}

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // --- Profile ---
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    // --- Resumes ---
    Route::apiResource('resumes', ResumeController::class)->except(['update']);
    Route::put('resume-versions/{resumeVersion}', [ResumeVersionUpdateController::class, 'update']);
    Route::get('resume-versions/{resumeVersion}/export/{format}', [ResumeExportController::class, 'export']);

    // --- Job Postings ---
    Route::apiResource('job-postings', JobPostingController::class)->except(['update']);

    // --- AI Endpoints (rate limited) ---
    Route::middleware('throttle:ai')->group(function () {
        Route::get('resume-analyses', [ResumeAnalysisController::class, 'index']);
        Route::post('resume-analyses', [ResumeAnalysisController::class, 'store']);
        Route::get('resume-analyses/{resumeVersionId}/{jobPostingId}', [ResumeAnalysisController::class, 'show']);

        Route::post('resume-optimizations', [ResumeOptimizationController::class, 'store']);
        Route::get('resume-versions/{resumeVersion}/compare', [ResumeOptimizationController::class, 'compare']);

        Route::get('cover-letters', [CoverLetterController::class, 'index']);
        Route::post('cover-letters', [CoverLetterController::class, 'store']);
        Route::put('cover-letters/{coverLetter}', [CoverLetterController::class, 'update']);
        Route::get('cover-letters/{coverLetter}/export', [CoverLetterExportController::class, 'export']);

        // Phase 3: Personal Branding & Portfolio Analysis
        Route::get('personal-branding', [\App\Http\Controllers\Api\PersonalBrandController::class, 'show']);
        Route::post('personal-branding/generate', [\App\Http\Controllers\Api\PersonalBrandController::class, 'generate']);
        
        Route::get('portfolio-analyses', [\App\Http\Controllers\Api\PortfolioAnalysisController::class, 'index']);
        Route::post('portfolio-analyses', [\App\Http\Controllers\Api\PortfolioAnalysisController::class, 'store']);
    });

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Subscriptions
    Route::post('subscriptions/simulate-checkout', [\App\Http\Controllers\Api\SubscriptionController::class, 'simulateCheckout']);

    // Account/Settings
    Route::get('account', [AccountController::class, 'show']);
    Route::put('account', [AccountController::class, 'update']);
    Route::post('account/password', [AccountController::class, 'changePassword']);
    Route::post('account/avatar', [AccountController::class, 'uploadAvatar']);
    Route::delete('account', [AccountController::class, 'deleteAccount']);

    // Resume versions
    Route::delete('resume-versions/{resumeVersion}', [ResumeVersionController::class, 'destroy']);
    Route::post('resume-versions/{resumeVersion}/set-master', [ResumeVersionController::class, 'setMaster']);

    // --- Applications ---
    Route::apiResource('applications', ApplicationController::class)->except(['show']);

    // --- Phase 2: Career Roadmap & Learning ---
    Route::apiResource('roadmaps', \App\Http\Controllers\CareerRoadmapController::class)->only(['index', 'show', 'store', 'destroy']);
    Route::apiResource('offer-evaluations', \App\Http\Controllers\OfferEvaluationController::class)->only(['index', 'show', 'store']);
    
    Route::prefix('mock-interviews')->group(function () {
        Route::get('/', [\App\Http\Controllers\MockInterviewController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\MockInterviewController::class, 'store']);
        Route::get('/{mockInterview}', [\App\Http\Controllers\MockInterviewController::class, 'show']);
        Route::delete('/{mockInterview}', [\App\Http\Controllers\MockInterviewController::class, 'destroy']);
        Route::post('/{mockInterview}/chat', [\App\Http\Controllers\MockInterviewController::class, 'chat']);
        Route::post('/{mockInterview}/finish', [\App\Http\Controllers\MockInterviewController::class, 'finish']);
    });

    // --- AI Copilot ---
    Route::prefix('copilot')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\CopilotController::class, 'index']);
        Route::get('/{session}', [\App\Http\Controllers\Api\V1\CopilotController::class, 'show']);
        Route::post('/chat', [\App\Http\Controllers\Api\V1\CopilotController::class, 'chat']);
    });

    // --- Phase 4: Analytics & Job Discovery ---
    Route::get('analytics/performance', [\App\Http\Controllers\Api\AnalyticsController::class, 'index']);
    Route::get('jobs/discovery', [\App\Http\Controllers\Api\JobDiscoveryController::class, 'index']);

    // --- Phase 5: Enterprise Recruiter Platform ---
    Route::prefix('recruiter')->group(function () {
        Route::get('candidates', [RecruiterController::class, 'candidates']);
        Route::post('bulk-screen', [RecruiterController::class, 'bulkScreen']);
        Route::post('notes', [RecruiterController::class, 'addNote']);
    });

    // --- Phase 5: Extension Hub ---
    Route::prefix('extension')->group(function () {
        Route::get('token', [ExtensionController::class, 'token']);
        Route::post('analyze', [ExtensionController::class, 'analyze']);
    });
});
