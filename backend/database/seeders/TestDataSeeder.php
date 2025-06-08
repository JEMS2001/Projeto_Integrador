<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Membro;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Test Data Seeder for development and testing environments.
 * 
 * Creates realistic test data with proper relationships:
 * - Admin and regular users
 * - Companies with members
 * - Independent members
 */
final class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedUsers();
            $this->seedCompaniesWithMembers();
            $this->seedIndependentMembers();
        });

        $this->command->info('✅ Test data seeded successfully!');
        $this->logCreatedCounts();
    }

    /**
     * Seed basic users.
     */
    private function seedUsers(): void
    {
        // Create admin user
        User::factory()->admin()->create();

        // Create regular users
        User::factory()->count(5)->create();

        // Create some unverified users
        User::factory()->count(2)->unverified()->create();
    }

    /**
     * Seed companies with their members.
     */
    private function seedCompaniesWithMembers(): void
    {
        // Create startup companies (1-5 members each)
        Empresa::factory()->count(3)->startup()->create();

        // Create medium companies (6-20 members each)  
        Empresa::factory()->count(2)->medium()->create();

        // Create enterprise companies (21-50 members each)
        Empresa::factory()->count(1)->enterprise()->create();

        // Create a few companies with logos
        Empresa::factory()->count(2)->withLogo()->create();
    }

    /**
     * Seed independent members (not associated with companies).
     */
    private function seedIndependentMembers(): void
    {
        // Create active independent members
        Membro::factory()->count(5)->independent()->active()->create();

        // Create inactive independent members
        Membro::factory()->count(3)->independent()->inactive()->create();

        // Create members with profile images
        Membro::factory()->count(2)->independent()->withImage()->create();
    }

    /**
     * Log the counts of created records.
     */
    private function logCreatedCounts(): void
    {
        $userCount = User::count();
        $empresaCount = Empresa::count();
        $membroCount = Membro::count();

        $this->command->info("📊 Created records:");
        $this->command->info("   - Users: {$userCount}");
        $this->command->info("   - Empresas: {$empresaCount}");
        $this->command->info("   - Membros: {$membroCount}");
    }
}
