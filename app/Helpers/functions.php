<?php

if (!function_exists('should_blur_data')) {
    /**
     * Check if data should be blurred based on authentication status
     *
     * @return bool
     */
    function should_blur_data(): bool
    {
        return !auth()->check();
    }
}

if (!function_exists('blur_text')) {
    /**
     * Blur text by showing only last few characters
     *
     * @param string|null $text
     * @param int $visibleChars
     * @return string
     */
    function blur_text(?string $text, int $visibleChars = 4): string
    {
        if (empty($text)) {
            return '****';
        }

        if (strlen($text) <= $visibleChars) {
            return str_repeat('*', strlen($text));
        }

        return '*************-' . substr($text, -$visibleChars);
    }
}

if (!function_exists('blur_nik')) {
    /**
     * Blur NIK by showing only last few characters
     *
     * @param string|null $nik
     * @return string
     */
    function blur_nik(?string $nik): string
    {
        if (empty($nik)) {
            return '****************';
        }

        // Show only last 4 digits of NIK for security
        return '*************-' . substr($nik, -4);
    }
}
