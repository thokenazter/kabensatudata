<?php

namespace App\View\Components;

use App\Models\Family;
use App\Services\FamilyIndicatorService;
use Illuminate\View\Component;

class FamilyIndicatorCard extends Component
{
    public $family;
    public $indicators;

    /**
     * Create a new component instance.
     *
     * @param array|null $indicators Indikator yang sudah dihitung (opsional)
     * @param \App\Models\Family|null $family Keluarga yang akan dianalisa
     * @return void
     */
    public function __construct($indicators = null, Family $family = null)
    {
        $this->family = $family;

        if ($indicators) {
            // Gunakan indikator yang sudah dihitung jika disediakan
            $this->indicators = $indicators;
        } elseif ($family) {
            // Jika hanya family yang disediakan, hitung indikatornya
            $service = app(FamilyIndicatorService::class);
            $this->indicators = $service->getAggregateIndicators($family);
        } else {
            // Fallback ke array kosong jika tidak ada parameter yang valid
            $this->indicators = [];
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('components.family-indicator-card');
    }
}
