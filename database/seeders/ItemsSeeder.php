<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Item;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        $steel = Group::where('group_code', '51')->first();
        $electrical = Group::where('group_code', '52')->first();
        $hardware = Group::where('group_code', '53')->first();

        if ($steel) {
            $items = [
                ['item_code' => 'ST-001', 'name' => 'Steel Rod'],
                ['item_code' => 'ST-002', 'name' => 'Steel Mug'],
                ['item_code' => 'ST-003', 'name' => 'Steel Sheet'],
            ];

            foreach ($items as $it) {
                Item::updateOrCreate(
                    ['group_id' => $steel->id, 'item_code' => $it['item_code']],
                    ['name' => $it['name'], 'default_spec' => null]
                );
            }
        }

        if ($electrical) {
            $items = [
                ['item_code' => 'EL-001', 'name' => 'Copper Wire'],
                ['item_code' => 'EL-002', 'name' => 'Switch'],
            ];

            foreach ($items as $it) {
                Item::updateOrCreate(
                    ['group_id' => $electrical->id, 'item_code' => $it['item_code']],
                    ['name' => $it['name'], 'default_spec' => null]
                );
            }
        }

        if ($hardware) {
            $items = [
                ['item_code' => 'HW-001', 'name' => 'Nut Bolt Set'],
            ];

            foreach ($items as $it) {
                Item::updateOrCreate(
                    ['group_id' => $hardware->id, 'item_code' => $it['item_code']],
                    ['name' => $it['name'], 'default_spec' => null]
                );
            }
        }
    }
}
