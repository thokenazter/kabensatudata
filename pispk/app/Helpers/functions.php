<?php

// if (!function_exists('should_blur_data')) {
//     /**
//      * Check if sensitive data should be blurred
//      */
//     function should_blur_data(): bool
//     {
//         return app()->bound('blur_sensitive_data') ? app('blur_sensitive_data') : !auth()->check();
//     }
// }

// if (!function_exists('blur_text')) {
//     /**
//      * Blur sensitive text if needed
//      */
//     function blur_text(?string $text, int $visibleChars = 3, string $replacement = '***'): string
//     {
//         if (!$text) {
//             return '';
//         }

//         if (!should_blur_data()) {
//             return $text;
//         }

//         if (strlen($text) <= $visibleChars) {
//             return $replacement;
//         }

//         return substr($text, 0, $visibleChars) . $replacement;
//     }
// }

// if (!function_exists('blur_nik')) {
//     /**
//      * Blur NIK but show last 4 digits
//      */
//     function blur_nik(?string $nik): string
//     {
//         if (!$nik) {
//             return '';
//         }

//         if (!should_blur_data()) {
//             return $nik;
//         }

//         return 'XXXX-XXXX-XXXX-' . substr($nik, -4);
//     }
// }


// Kodingan Kedua

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
