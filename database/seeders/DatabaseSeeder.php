<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@eaduan.my'],
            [
                'name' => 'Admin Sistem',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => UserRole::Admin,
            ]
        );

        // Officers
        $officers = collect([
            ['name' => 'Pegawai Ahmad Razif', 'email' => 'pegawai1@eaduan.my'],
            ['name' => 'Pegawai Siti Norfazila', 'email' => 'pegawai2@eaduan.my'],
            ['name' => 'Pegawai Hazwan Amirul', 'email' => 'pegawai3@eaduan.my'],
        ])->map(fn ($data) => User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => UserRole::Officer,
            ]
        ));

        // Public complainant test account
        User::firstOrCreate(
            ['email' => 'awam@eaduan.my'],
            [
                'name' => 'Awam Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => UserRole::Complainant,
            ]
        );

        // Sample categories
        $categories = collect([
            'Jalan Rosak', 'Sampah Tidak Dikutip', 'Banjir', 'Lampu Jalan Rosak', 'Lain-lain',
        ])->map(fn ($name) => Category::firstOrCreate(['name' => $name], ['is_active' => true]));

        // Sample complainants + complaints if none exist
        if (Complaint::count() === 0) {
            $complainants = User::factory(10)->complainant()->create();

            Complaint::factory(20)
                ->recycle($complainants)
                ->recycle($categories)
                ->create();

            // Assign half to officers
            Complaint::whereNull('officer_id')
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->each(fn ($c) => $c->update([
                    'officer_id' => $officers->random()->id,
                ]));

            // Add a resolved one with an audit log
            $resolved = Complaint::factory()
                ->recycle($complainants)
                ->recycle($categories)
                ->resolved()
                ->create(['officer_id' => $officers->first()->id]);

            $resolved->logs()->create([
                'user_id' => $officers->first()->id,
                'old_status' => ComplaintStatus::InProgress->value,
                'new_status' => ComplaintStatus::Resolved->value,
                'created_at' => now(),
            ]);
        }
    }
}
