<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run JDIH specific seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            JdihDataSeeder::class,
        ]);

        $this->command->info('JDIH Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('🎉 ILDIS - Indonesian Legal Documentation Information System');
        $this->command->info('Database setup completed. You can now access the admin panel at: /admin');
        $this->command->info('Default login: admin@jdih.local / password');
    }
}
