<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RealtimeSearch extends Component
{
    public $inlineResults;
    public $placeholder;
    public $targetSelector;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($inlineResults = false, $placeholder = 'Cari...', $targetSelector = null)
    {
        $this->inlineResults = $inlineResults;
        $this->placeholder = $placeholder;
        $this->targetSelector = $targetSelector;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.realtime-search');
    }
}
