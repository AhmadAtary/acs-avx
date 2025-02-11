<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Access;

class AccessSeeder extends Seeder
{
    public function run()
    {
        Access::create([
            'account_number' => 'ACC001',
            'user_id' => 1, // Assuming user ID 1 is the owner
            'role' => 'owner',
            'permissions' => [
                'create_user' => true,
                'update_user' => true,
                'delete_user' => true,
                'view_user' => true,
            ],
            'account_limit' => 100, // Owner can manage up to 100 accounts
        ]);

        Access::create([
            'account_number' => 'ACC002',
            'user_id' => 2, // Assuming user ID 2 is an engineer
            'role' => 'eng',
            'permissions' => [
                'create_user' => false,
                'update_user' => true,
                'delete_user' => false,
                'view_user' => true,
            ],
            'account_limit' => 10, // Engineer can manage up to 10 accounts
        ]);
    }
}
