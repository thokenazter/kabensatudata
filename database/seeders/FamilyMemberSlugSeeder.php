<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FamilyMemberSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\FamilyMember::chunk(100, function ($members) {
            foreach ($members as $member) {
                $member->slug = Str::slug($member->name) . '-' . $member->id;
                $member->save();
            }
        });
    }
}
