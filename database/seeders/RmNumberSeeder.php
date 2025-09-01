<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Village;
use App\Models\Building;
use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Support\Facades\DB;

class RmNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->updateVillageSequenceNumbers();
            $this->updateFamilySequenceNumbers();
            $this->updateFamilyMemberRmNumbers();
        });
    }

    /**
     * Update existing villages with sequence numbers
     */
    private function updateVillageSequenceNumbers()
    {
        $villages = Village::whereNull('sequence_number')->orderBy('id')->get();
        
        foreach ($villages as $index => $village) {
            $village->sequence_number = $index + 1;
            $village->save();
        }
        
        $this->command->info('Updated ' . $villages->count() . ' villages with sequence numbers');
    }

    /**
     * Update existing families with sequence numbers in building
     */
    private function updateFamilySequenceNumbers()
    {
        $buildings = Building::with('families')->get();
        
        $totalFamilies = 0;
        foreach ($buildings as $building) {
            $families = $building->families()->whereNull('sequence_number_in_building')->orderBy('id')->get();
            
            foreach ($families as $index => $family) {
                $family->sequence_number_in_building = $index + 1;
                $family->save();
                $totalFamilies++;
            }
        }
        
        $this->command->info('Updated ' . $totalFamilies . ' families with sequence numbers');
    }

    /**
     * Generate RM numbers for existing family members
     */
    private function updateFamilyMemberRmNumbers()
    {
        $families = Family::with(['members', 'building.village'])->get();
        
        $totalMembers = 0;
        foreach ($families as $family) {
            $members = $family->members()->whereNull('rm_number')->orderBy('id')->get();
            
            foreach ($members as $index => $member) {
                $member->sequence_number_in_family = $index + 1;
                $member->generateRmNumber();
                $member->save();
                $totalMembers++;
            }
        }
        
        $this->command->info('Generated RM numbers for ' . $totalMembers . ' family members');
    }
}