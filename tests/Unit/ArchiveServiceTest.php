<?php

namespace Tests\Unit;

use App\Services\ArchiveService;
use App\Interfaces\ArchiveRepositoryInterface;
use App\Interfaces\ResearchRepositoryInterface;
use App\Models\ArchiveRecord;
use App\Models\Research;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests: ArchiveService
 *
 * Tests archive number generation and archiving business logic.
 */
class ArchiveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArchiveService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ArchiveService::class);
    }

    /** @test */
    public function it_archives_approved_research(): void
    {
        $department = Department::create(['name' => 'علوم الحاسب']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $research = Research::create([
            'title'         => 'بحث تجريبي للأرشفة',
            'status'        => 'approved',
            'researcher_id' => $user->id,
            'department_id' => $department->id,
        ]);

        $archive = $this->service->archiveResearch($research->id, $user->id, 'ملاحظة تجريبية');

        $this->assertInstanceOf(ArchiveRecord::class, $archive);
        $this->assertNotEmpty($archive->archive_number);
        $this->assertStringStartsWith('EDU-', $archive->archive_number);
        $this->assertEquals($research->id, $archive->research_id);
        $this->assertEquals($user->id, $archive->archived_by);
        $this->assertEquals('ملاحظة تجريبية', $archive->notes);
    }

    /** @test */
    public function it_rejects_archiving_pending_research(): void
    {
        $department = Department::create(['name' => 'الهندسة']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $research = Research::create([
            'title'         => 'بحث معلق',
            'status'        => 'pending',
            'researcher_id' => $user->id,
            'department_id' => $department->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only approved research');

        $this->service->archiveResearch($research->id, $user->id);
    }

    /** @test */
    public function it_rejects_double_archiving(): void
    {
        $department = Department::create(['name' => 'الرياضيات']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $research = Research::create([
            'title'         => 'بحث للأرشفة المزدوجة',
            'status'        => 'approved',
            'researcher_id' => $user->id,
            'department_id' => $department->id,
        ]);

        // First archive should succeed
        $this->service->archiveResearch($research->id, $user->id);

        // Second archive should fail
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already archived');

        $this->service->archiveResearch($research->id, $user->id);
    }

    /** @test */
    public function it_generates_unique_archive_numbers(): void
    {
        $department = Department::create(['name' => 'CS']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $research = Research::create([
                'title'         => "بحث رقم {$i}",
                'status'        => 'approved',
                'researcher_id' => $user->id,
                'department_id' => $department->id,
            ]);

            $archive = $this->service->archiveResearch($research->id, $user->id);
            $numbers[] = $archive->archive_number;
        }

        // All numbers should be unique
        $this->assertCount(3, array_unique($numbers));

        // All should follow the format EDU-YYYY-XXX-NNNNN
        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^EDU-\d{4}-[A-Z]{1,3}-\d{5}$/', $number);
        }
    }

    /** @test */
    public function it_updates_research_archived_at_timestamp(): void
    {
        $department = Department::create(['name' => 'علوم']);
        $user = User::factory()->create(['department_id' => $department->id]);
        $research = Research::create([
            'title'         => 'بحث للتحقق من التاريخ',
            'status'        => 'approved',
            'researcher_id' => $user->id,
            'department_id' => $department->id,
        ]);

        $this->assertNull($research->archived_at);

        $this->service->archiveResearch($research->id, $user->id);

        $research->refresh();
        $this->assertNotNull($research->archived_at);
        $this->assertTrue($research->is_archived);
    }
}
