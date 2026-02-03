<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'key' => 'cobit',
                'name' => 'COBIT Assessment',
                'description' => 'Sistem penilaian dan audit tata kelola IT berbasis COBIT 2019',
                'url' => config('sso.apps.cobit.url', 'https://cobit2019.divusi.co.id/login'),
                'icon' => 'shield-check',
                'color' => 'emerald',
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'key' => 'pmo',
                'name' => 'Project Management Office',
                'description' => 'Pengelolaan proyek, timeline, dan resource perusahaan',
                'url' => config('sso.apps.pmo.url', 'http://pmo.divusi.local'),
                'icon' => 'clipboard-document-check',
                'color' => 'blue',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'hr',
                'name' => 'HR Management',
                'description' => 'Pengelolaan SDM, absensi, dan payroll karyawan',
                'url' => config('sso.apps.hr.url', 'http://hr.divusi.local'),
                'icon' => 'users',
                'color' => 'violet',
                'is_active' => false,
                'sort_order' => 2,
            ],
            [
                'key' => 'finance',
                'name' => 'Finance & Accounting',
                'description' => 'Pengelolaan keuangan, budgeting, dan laporan finansial',
                'url' => config('sso.apps.finance.url', 'http://finance.divusi.local'),
                'icon' => 'currency-dollar',
                'color' => 'amber',
                'is_active' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['key' => $module['key']],
                $module
            );
        }
    }
}
