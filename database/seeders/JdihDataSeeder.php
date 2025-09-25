<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\DocumentStatus;
use App\Models\Subject;
use App\Models\Author;

class JdihDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Document Types based on JDIHN standards
        $documentTypes = [
            [
                'name' => 'Peraturan Perundang-undangan',
                'slug' => 'peraturan',
                'description' => 'Peraturan perundang-undangan meliputi UU, PP, Perpres, Permen, Perda, dan lainnya',
                'icon' => 'heroicon-o-scale',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Putusan Pengadilan',
                'slug' => 'putusan',
                'description' => 'Putusan pengadilan dari berbagai tingkat peradilan',
                'icon' => 'heroicon-o-building-office-2',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Monografi Hukum',
                'slug' => 'monografi',
                'description' => 'Buku, jurnal, dan literatur hukum lainnya',
                'icon' => 'heroicon-o-book-open',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Artikel Hukum',
                'slug' => 'artikel',
                'description' => 'Artikel, makalah, dan tulisan hukum',
                'icon' => 'heroicon-o-document-text',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::create($type);
        }

        // Create Document Statuses
        $statuses = [
            [
                'name' => 'Draft',
                'slug' => 'draft',
                'color' => 'gray',
                'description' => 'Dokumen dalam tahap penyusunan',
                'is_active' => true,
                'is_published' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Review',
                'slug' => 'review',
                'color' => 'warning',
                'description' => 'Dokumen sedang dalam tahap review',
                'is_active' => true,
                'is_published' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Approved',
                'slug' => 'approved',
                'color' => 'info',
                'description' => 'Dokumen telah disetujui',
                'is_active' => true,
                'is_published' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Published',
                'slug' => 'published',
                'color' => 'success',
                'description' => 'Dokumen telah dipublikasikan',
                'is_active' => true,
                'is_published' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Archived',
                'slug' => 'archived',
                'color' => 'secondary',
                'description' => 'Dokumen telah diarsipkan',
                'is_active' => true,
                'is_published' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($statuses as $status) {
            DocumentStatus::create($status);
        }

        // Create Legal Subject Classifications (Bidang Hukum)
        $subjects = [
            // Main categories
            [
                'name' => 'Hukum Pidana',
                'slug' => 'hukum-pidana',
                'description' => 'Hukum yang mengatur tentang tindak pidana dan sanksinya',
                'code' => 'HP',
                'parent_id' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hukum Perdata',
                'slug' => 'hukum-perdata',
                'description' => 'Hukum yang mengatur hubungan antara individu',
                'code' => 'HPD',
                'parent_id' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Hukum Tata Negara',
                'slug' => 'hukum-tata-negara',
                'description' => 'Hukum yang mengatur struktur dan fungsi negara',
                'code' => 'HTN',
                'parent_id' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Hukum Administrasi Negara',
                'slug' => 'hukum-administrasi-negara',
                'description' => 'Hukum yang mengatur administrasi pemerintahan',
                'code' => 'HAN',
                'parent_id' => null,
                'sort_order' => 4,
            ],
            [
                'name' => 'Hukum Ekonomi',
                'slug' => 'hukum-ekonomi',
                'description' => 'Hukum yang mengatur kegiatan ekonomi',
                'code' => 'HE',
                'parent_id' => null,
                'sort_order' => 5,
            ],
            [
                'name' => 'Hukum Lingkungan',
                'slug' => 'hukum-lingkungan',
                'description' => 'Hukum yang mengatur perlindungan lingkungan hidup',
                'code' => 'HL',
                'parent_id' => null,
                'sort_order' => 6,
            ],
            [
                'name' => 'Hukum Ketenagakerjaan',
                'slug' => 'hukum-ketenagakerjaan',
                'description' => 'Hukum yang mengatur hubungan kerja',
                'code' => 'HK',
                'parent_id' => null,
                'sort_order' => 7,
            ],
            [
                'name' => 'Hukum Internasional',
                'slug' => 'hukum-internasional',
                'description' => 'Hukum yang mengatur hubungan antar negara',
                'code' => 'HI',
                'parent_id' => null,
                'sort_order' => 8,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        // Create sample authors
        $authors = [
            [
                'name' => 'Prof. Dr. H. Jimly Asshiddiqie, S.H.',
                'slug' => 'jimly-asshiddiqie',
                'institution' => 'Universitas Indonesia',
                'position' => 'Guru Besar Hukum Tata Negara',
                'bio' => 'Mantan Ketua Mahkamah Konstitusi RI, ahli hukum tata negara terkemuka',
                'is_active' => true,
            ],
            [
                'name' => 'Prof. Dr. Bagir Manan, S.H., M.C.L.',
                'slug' => 'bagir-manan',
                'institution' => 'Universitas Padjadjaran',
                'position' => 'Guru Besar Hukum Tata Negara',
                'bio' => 'Mantan Ketua Mahkamah Agung RI, pakar hukum tata negara dan administrasi negara',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Hikmahanto Juwana, S.H., LL.M., Ph.D.',
                'slug' => 'hikmahanto-juwana',
                'institution' => 'Universitas Indonesia',
                'position' => 'Guru Besar Hukum Internasional',
                'bio' => 'Pakar hukum internasional dan ekonomi',
                'is_active' => true,
            ],
            [
                'name' => 'Prof. Dr. Satjipto Rahardjo, S.H.',
                'slug' => 'satjipto-rahardjo',
                'institution' => 'Universitas Diponegoro',
                'position' => 'Guru Besar Sosiologi Hukum',
                'bio' => 'Perintis Hukum Progresif di Indonesia',
                'is_active' => true,
            ],
        ];

        foreach ($authors as $author) {
            Author::create($author);
        }

        $this->command->info('JDIH initial data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 4 Document Types');
        $this->command->info('- 5 Document Statuses');
        $this->command->info('- 8 Legal Subject Categories');
        $this->command->info('- 4 Sample Authors');
    }
}
