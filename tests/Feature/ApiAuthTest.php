<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\ResumeVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    // ────────────────────────────────────────────────────────────────────────────
    // Authentication
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token', 'user']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password123!')]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Profile
    // ────────────────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile')
            ->assertOk();
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'full_name'          => 'Jane Smith',
                'professional_title' => 'Senior Developer',
                'phone'              => '+1234567890',
                'location'           => 'San Francisco, CA',
            ])
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Jane Smith']);
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Resumes
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_upload_resume(): void
    {
        Storage::fake('private');
        \Illuminate\Support\Facades\Queue::fake();
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/resumes', ['file' => $file]);

        $response->assertStatus(201)->assertJsonStructure(['id', 'original_filename', 'parse_status']);
        $this->assertDatabaseHas('resumes', ['user_id' => $user->id]);
    }

    public function test_user_cannot_view_another_users_resume(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $resume = Resume::factory()->create(['user_id' => $userA->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/resumes/{$resume->id}")
            ->assertForbidden();
    }

    public function test_user_can_delete_resume(): void
    {
        Storage::fake('private');
        $user   = User::factory()->create();
        $resume = Resume::factory()->create(['user_id' => $user->id, 'file_path' => 'resumes/test.pdf']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/resumes/{$resume->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Job Postings
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_create_job_posting_with_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/job-postings', [
                'raw_description' => 'We are looking for a senior Laravel developer with 5+ years experience in PHP and Vue.js.',
            ]);

        $response->assertStatus(201)->assertJsonStructure(['id', 'raw_description']);
    }

    public function test_user_can_delete_own_job_posting(): void
    {
        $user   = User::factory()->create();
        $job    = JobPosting::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/job-postings/{$job->id}")
            ->assertNoContent();
    }

    public function test_user_cannot_delete_another_users_job_posting(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $job   = JobPosting::factory()->create(['user_id' => $userA->id]);

        $this->actingAs($userB, 'sanctum')
            ->deleteJson("/api/v1/job-postings/{$job->id}")
            ->assertForbidden();
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Applications
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_create_application(): void
    {
        $user = User::factory()->create();
        $job  = JobPosting::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/applications', [
                'job_posting_id' => $job->id,
                'status'         => 'saved',
                'notes'          => 'Great company',
            ]);

        $response->assertStatus(201)->assertJsonFragment(['status' => 'saved']);
    }

    public function test_user_can_update_application_status(): void
    {
        $user = User::factory()->create();
        $job  = JobPosting::factory()->create(['user_id' => $user->id]);
        $app  = Application::factory()->create([
            'user_id'        => $user->id,
            'job_posting_id' => $job->id,
            'status'         => ApplicationStatus::Saved,
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/applications/{$app->id}", ['status' => 'applied'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'applied']);

        $this->assertDatabaseHas('application_status_history', [
            'application_id' => $app->id,
            'from_status'    => 'saved',
            'to_status'      => 'applied',
        ]);
    }

    public function test_user_cannot_delete_another_users_application(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $job   = JobPosting::factory()->create(['user_id' => $userA->id]);
        $app   = Application::factory()->create(['user_id' => $userA->id, 'job_posting_id' => $job->id]);

        $this->actingAs($userB, 'sanctum')
            ->deleteJson("/api/v1/applications/{$app->id}")
            ->assertForbidden();
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Notifications
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_fetch_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_user_can_fetch_unread_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications/unread')
            ->assertOk()
            ->assertJsonStructure([]);
    }

    public function test_user_can_mark_all_notifications_read(): void
    {
        $user = User::factory()->create();

        // Insert a notification record directly so we can test marking it read
        \DB::table('notifications')->insert([
            'id'              => \Str::uuid(),
            'type'            => 'App\\Notifications\\ResumeAnalysisCompleted',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['title' => 'Test', 'message' => 'Test notification']),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Account / Settings
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_view_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/account')
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_user_can_update_account_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/account', ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPass123!')]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/password', [
                'current_password'      => 'OldPass123!',
                'password'              => 'NewPass456!',
                'password_confirmation' => 'NewPass456!',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Password updated successfully.']);
    }

    public function test_wrong_current_password_returns_422(): void
    {
        $user = User::factory()->create(['password' => bcrypt('CorrectPass!')]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/password', [
                'current_password'      => 'WrongPass!',
                'password'              => 'NewPass456!',
                'password_confirmation' => 'NewPass456!',
            ])
            ->assertUnprocessable();
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Resume Versions
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_delete_non_master_resume_version(): void
    {
        $user    = User::factory()->create();
        $resume  = Resume::factory()->create(['user_id' => $user->id]);
        $master  = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => true]);
        $version = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => false]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/resume-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('resume_versions', ['id' => $version->id]);
    }

    public function test_user_cannot_delete_master_version(): void
    {
        $user   = User::factory()->create();
        $resume = Resume::factory()->create(['user_id' => $user->id]);
        $master = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => true]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/resume-versions/{$master->id}")
            ->assertUnprocessable();
    }

    public function test_user_can_set_version_as_master(): void
    {
        $user    = User::factory()->create();
        $resume  = Resume::factory()->create(['user_id' => $user->id]);
        $master  = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => true]);
        $version = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => false]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/resume-versions/{$version->id}/set-master")
            ->assertOk();

        $this->assertDatabaseHas('resume_versions', ['id' => $version->id, 'is_master' => true]);
        $this->assertDatabaseHas('resume_versions', ['id' => $master->id, 'is_master' => false]);
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Cover Letters
    // ────────────────────────────────────────────────────────────────────────────

    public function test_user_can_list_cover_letters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/cover-letters')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_update_own_cover_letter(): void
    {
        $user        = User::factory()->create();
        $job         = JobPosting::factory()->create(['user_id' => $user->id]);
        $resume      = Resume::factory()->create(['user_id' => $user->id]);
        $version     = ResumeVersion::factory()->create(['resume_id' => $resume->id, 'is_master' => true]);
        $coverLetter = CoverLetter::factory()->create([
            'user_id'           => $user->id,
            'job_posting_id'    => $job->id,
            'resume_version_id' => $version->id,
            'content'           => 'Original content',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/cover-letters/{$coverLetter->id}", ['content' => 'Updated content'])
            ->assertOk()
            ->assertJsonFragment(['content' => 'Updated content']);
    }

    public function test_user_cannot_update_another_users_cover_letter(): void
    {
        $userA       = User::factory()->create();
        $userB       = User::factory()->create();
        $job         = JobPosting::factory()->create(['user_id' => $userA->id]);
        $resume      = Resume::factory()->create(['user_id' => $userA->id]);
        $version     = ResumeVersion::factory()->create(['resume_id' => $resume->id]);
        $coverLetter = CoverLetter::factory()->create([
            'user_id'           => $userA->id,
            'job_posting_id'    => $job->id,
            'resume_version_id' => $version->id,
        ]);

        $this->actingAs($userB, 'sanctum')
            ->putJson("/api/v1/cover-letters/{$coverLetter->id}", ['content' => 'Hacked!'])
            ->assertForbidden();
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Admin Authorization
    // ────────────────────────────────────────────────────────────────────────────

    public function test_admin_route_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
