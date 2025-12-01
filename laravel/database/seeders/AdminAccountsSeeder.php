<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminAccounts = [
            // Master Admin (Rauf)
            [
                'name' => 'Rauf - Master Admin',
                'email' => 'rauf6821@gmail.com',
                'password' => Hash::make('12345678'),
                'phone' => '0123456789',
                'ic_number' => '990101-01-0001',
                'role' => 'master_admin',
                'district' => null,
            ],

            // State Admin
            [
                'name' => 'Ahmad - State Admin',
                'email' => 'state.admin@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0199876543',
                'ic_number' => '850505-03-0002',
                'role' => 'state_admin',
                'district' => null,
            ],

            // District Admin - Hulu Terengganu (MDHT)
            [
                'name' => 'Ammar - Admin MDHT',
                'email' => 'admin.mdht@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0198765432',
                'ic_number' => '880808-03-0003',
                'role' => 'district_admin',
                'district' => 'Hulu Terengganu',
            ],

            // District Admin - Besut (MDB)
            [
                'name' => 'Rifqi - Admin MDB',
                'email' => 'admin.mdb@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0197654321',
                'ic_number' => '870707-03-0004',
                'role' => 'district_admin',
                'district' => 'Besut',
            ],

            // District Admin - Marang (MDM)
            [
                'name' => 'Aina - Admin MDM',
                'email' => 'admin.mdm@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0196543210',
                'ic_number' => '900909-03-0005',
                'role' => 'district_admin',
                'district' => 'Marang',
            ],

            // District Admin - Setiu (MDS)
            [
                'name' => 'Ika - Admin MDS',
                'email' => 'admin.mds@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0195432109',
                'ic_number' => '920202-03-0006',
                'role' => 'district_admin',
                'district' => 'Setiu',
            ],

            // District Admin - Kuala Terengganu (MBKT)
            [
                'name' => 'Amin - Admin MBKT',
                'email' => 'admin.mbkt@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0194321098',
                'ic_number' => '860606-03-0007',
                'role' => 'district_admin',
                'district' => 'Kuala Terengganu',
            ],

            // District Admin - Kemaman (MPK)
            [
                'name' => 'Farah - Admin MPK',
                'email' => 'admin.mpk@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0193210987',
                'ic_number' => '891111-03-0008',
                'role' => 'district_admin',
                'district' => 'Kemaman',
            ],

            // District Admin - Dungun (MPD)
            [
                'name' => 'Faiz - Admin MPD',
                'email' => 'admin.mpd@terengganu.gov.my',
                'password' => Hash::make('12345678'),
                'phone' => '0192109876',
                'ic_number' => '911212-03-0009',
                'role' => 'district_admin',
                'district' => 'Dungun',
            ],
        ];

        foreach ($adminAccounts as $account) {
            // Check if user exists
            $existingUser = DB::table('users')->where('email', $account['email'])->first();

            if ($existingUser) {
                // Update existing user
                DB::table('users')
                    ->where('email', $account['email'])
                    ->update([
                        'password' => $account['password'],
                        'role' => $account['role'],
                        'district' => $account['district'],
                        'updated_at' => now(),
                    ]);
                echo "Updated: {$account['email']} - {$account['role']}\n";
            } else {
                // Create new user
                DB::table('users')->insert(array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                echo "Created: {$account['email']} - {$account['role']}\n";
            }
        }

        echo "\n=== Admin Accounts Summary ===\n";
        echo "Master Admin: rauf6821@gmail.com (Full System Access)\n";
        echo "State Admin: state.admin@terengganu.gov.my (All Districts)\n";
        echo "MDHT Admin: admin.mdht@terengganu.gov.my (Hulu Terengganu only)\n";
        echo "MDB Admin: admin.mdb@terengganu.gov.my (Besut only)\n";
        echo "MDM Admin: admin.mdm@terengganu.gov.my (Marang only)\n";
        echo "MDS Admin: admin.mds@terengganu.gov.my (Setiu only)\n";
        echo "MBKT Admin: admin.mbkt@terengganu.gov.my (Kuala Terengganu only)\n";
        echo "MPK Admin: admin.mpk@terengganu.gov.my (Kemaman only)\n";
        echo "MPD Admin: admin.mpd@terengganu.gov.my (Dungun only)\n";
        echo "\nPassword for all accounts: 12345678\n";
    }
}
