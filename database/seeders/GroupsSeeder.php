<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupsSeeder extends Seeder
{
    public function run(): void
    {
        // Example groups (client ke real codes later add/ import)
        $groups = [
            ['group_code' => '51', 'group_name' => 'Steel Material'],
            ['group_code' => '52', 'group_name' => 'Electrical'],
            ['group_code' => '53', 'group_name' => 'Hardware'],
        ];

        foreach ($groups as $g) {
            Group::updateOrCreate(
                ['group_code' => $g['group_code']],
                ['group_name' => $g['group_name']]
            );
        }
    }
}
