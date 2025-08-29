<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BlurredData extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $content,
        public bool $sensitive = true,
        public int $visibleChars = 3,
        public string $replacement = '***'
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.blurred-data');
    }

    /**
     * Determine if the data should be blurred.
     */
    public function shouldBlur(): bool
    {
        return $this->sensitive && should_blur_data();
    }

    /**
     * Get the blurred content.
     */
    public function getContent(): string
    {
        if (!$this->shouldBlur()) {
            return $this->content;
        }

        if (strlen($this->content) <= $this->visibleChars) {
            return $this->replacement;
        }

        return substr($this->content, 0, $this->visibleChars) . $this->replacement;
    }
}
