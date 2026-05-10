<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Research;
use App\Models\ArchiveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Integration Test: Ahmed's Journey
 *
 * Tests the complete flow from user registration, research upload,
 * approval, to archiving — simulating أحمد's full experience.
 *
 * This test ensures all system components work together correctly:
 * Auth → Research CRUD → Approval → Archive → Dashboard
 */
class AhmedJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $ahmed;
    protected Department $department;
    protected string $ahmedToken;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // Create department
        $this->department = Department::create([
            'name'        => 'الذكاء الاصطناعي',
            'description' => 'قسم الذكاء الاصطناعي وتعلم الآلة',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'name'          => 'مدير النظام',
            'email'         => 'admin@edutrack.local',
            'role'          => 'super_admin',
            'department_id' => $this->department->id,
        ]);
    }

    /** @test */
    public function ahmed_full_journey_from_registration_to_archive(): void
    {
        // ═══════════════════════════════════════════
        // Step 1: أحمد يسجل حساب جديد
        // ═══════════════════════════════════════════
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'أحمد محمد',
            'email'                 => 'ahmed@edutrack.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'researcher',
            'department_id'         => $this->department->id,
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->ahmedToken = $registerResponse->json('data.token');
        $this->ahmed = User::where('email', 'ahmed@edutrack.local')->first();

        // ═══════════════════════════════════════════
        // Step 2: أحمد يطلع على ملفه الشخصي
        // ═══════════════════════════════════════════
        $profileResponse = $this->withToken($this->ahmedToken)
            ->getJson('/api/v1/auth/profile');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'أحمد محمد')
            ->assertJsonPath('data.role', 'researcher');

        // ═══════════════════════════════════════════
        // Step 3: أحمد يرفع بحث جديد مع ملف PDF
        // ═══════════════════════════════════════════
        $file = UploadedFile::fake()->create('research.pdf', 1024, 'application/pdf');

        $uploadResponse = $this->withToken($this->ahmedToken)
            ->postJson('/api/v1/research', [
                'title'         => 'تطبيق الذكاء الاصطناعي في التشخيص الطبي',
                'abstract'      => 'تهدف هذه الدراسة إلى استكشاف تطبيقات الذكاء الاصطناعي في مجال التشخيص الطبي.',
                'file'          => $file,
                'researcher_id' => $this->ahmed->id,
                'department_id' => $this->department->id,
            ]);

        $uploadResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'تطبيق الذكاء الاصطناعي في التشخيص الطبي')
            ->assertJsonPath('data.status', 'pending');

        $researchId = $uploadResponse->json('data.id');

        // ═══════════════════════════════════════════
        // Step 4: أحمد يرى البحث في القائمة
        // ═══════════════════════════════════════════
        $listResponse = $this->withToken($this->ahmedToken)
            ->getJson('/api/v1/research');

        $listResponse->assertStatus(200)
            ->assertJsonPath('meta.total', 1);

        // ═══════════════════════════════════════════
        // Step 5: أحمد يرى تفاصيل البحث
        // ═══════════════════════════════════════════
        $showResponse = $this->withToken($this->ahmedToken)
            ->getJson("/api/v1/research/{$researchId}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('data.id', $researchId)
            ->assertJsonPath('data.status', 'pending');

        // ═══════════════════════════════════════════
        // Step 6: المدير يوافق على البحث
        // ═══════════════════════════════════════════
        $this->adminToken = $this->admin->createToken('admin_token')->plainTextToken;

        $approveResponse = $this->withToken($this->adminToken)
            ->putJson("/api/v1/research/{$researchId}", [
                'status' => 'approved',
            ]);

        $approveResponse->assertStatus(200);

        // Verify status changed
        $this->assertEquals('approved', Research::find($researchId)->status);

        // ═══════════════════════════════════════════
        // Step 7: المدير يؤرشف البحث المعتمد
        // ═══════════════════════════════════════════
        $archiveResponse = $this->withToken($this->adminToken)
            ->postJson('/api/v1/archives', [
                'research_id' => $researchId,
                'notes'       => 'بحث ممتاز، تمت الأرشفة.',
            ]);

        $archiveResponse->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['archive_number'],
            ]);

        $archiveNumber = $archiveResponse->json('data.archive_number');
        $this->assertStringStartsWith('EDU-', $archiveNumber);

        // ═══════════════════════════════════════════
        // Step 8: التحقق من الأرشيف
        // ═══════════════════════════════════════════
        $archiveLookup = $this->withToken($this->adminToken)
            ->getJson("/api/v1/archives/{$archiveNumber}");

        $archiveLookup->assertStatus(200)
            ->assertJsonPath('data.archive_number', $archiveNumber);

        // ═══════════════════════════════════════════
        // Step 9: التحقق من إحصائيات لوحة القيادة
        // ═══════════════════════════════════════════
        $dashboardResponse = $this->withToken($this->adminToken)
            ->getJson('/api/v1/dashboard/stats');

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'research' => ['total', 'pending', 'approved', 'rejected'],
                    'archive'  => ['total_archived'],
                ],
            ]);

        // ═══════════════════════════════════════════
        // Step 10: أحمد يسجل الخروج
        // ═══════════════════════════════════════════
        $logoutResponse = $this->withToken($this->ahmedToken)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);

        // Token should be invalidated
        $this->withToken($this->ahmedToken)
            ->getJson('/api/v1/auth/profile')
            ->assertStatus(401);
    }

    /** @test */
    public function it_detects_duplicate_file_uploads(): void
    {
        $this->ahmed = User::factory()->create([
            'role'          => 'researcher',
            'department_id' => $this->department->id,
        ]);
        $this->ahmedToken = $this->ahmed->createToken('test')->plainTextToken;

        // First upload
        $file1 = UploadedFile::fake()->create('thesis.pdf', 500, 'application/pdf');
        $this->withToken($this->ahmedToken)
            ->postJson('/api/v1/research', [
                'title'         => 'بحث أول',
                'researcher_id' => $this->ahmed->id,
                'department_id' => $this->department->id,
                'file'          => $file1,
            ])
            ->assertStatus(201);

        // Verify research was created
        $this->assertDatabaseHas('research', ['title' => 'بحث أول']);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_api(): void
    {
        $this->getJson('/api/v1/research')->assertStatus(401);
        $this->postJson('/api/v1/research', [])->assertStatus(401);
        $this->getJson('/api/v1/archives')->assertStatus(401);
        $this->getJson('/api/v1/dashboard/stats')->assertStatus(401);
    }

    /** @test */
    public function login_rate_limiting_works(): void
    {
        // Attempt login more than 10 times
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email'    => 'nonexistent@test.com',
                'password' => 'wrongpassword',
            ]);
        }

        // The 11th attempt should be rate limited
        $response->assertStatus(429);
    }
}
