<?php

namespace App\View\Components;

use Illuminate\View\Component;

class HealthIndicatorCard extends Component
{
    public $indicators;
    public $member;

    /**
     * Create a new component instance.
     *
     * @param array $indicators
     * @param \App\Models\FamilyMember $member
     * @return void
     */
    public function __construct($indicators, $member)
    {
        $this->indicators = $indicators;
        $this->member = $member;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('components.health-indicator-card');
    }
}
