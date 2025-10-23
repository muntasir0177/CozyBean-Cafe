<?php
/**
 * Currency formatting helper for Taka (Bangladeshi currency)
 */

if (!function_exists('format_currency')) {
    /**
     * Format amount as Taka currency
     * 
     * @param float $amount The amount to format
     * @param bool $show_symbol Whether to show the Taka symbol (default: true)
     * @return string Formatted currency string
     */
    function format_currency($amount, $show_symbol = true) {
        // Format the number with 2 decimal places
        $formatted = number_format($amount, 2);
        
        // Add Taka symbol if requested
        if ($show_symbol) {
            // Using UTF-8 Taka symbol
            return '৳' . $formatted;
        }
        
        return $formatted;
    }
}
?>