<?php

if (! function_exists('format_cantidad')) {
    /**
     * Formatea una cantidad eliminando ceros decimales innecesarios.
     * Ejemplos: 1.0000 → "1"  |  1.5000 → "1.5"  |  1.2500 → "1.25"  |  1.1234 → "1.1234"
     * Máximo 4 decimales (precisión de la BD).
     */
    function format_cantidad(float|string|null $cantidad): string
    {
        if ($cantidad === null) {
            return '0';
        }
        return rtrim(rtrim(number_format((float) $cantidad, 4, '.', ''), '0'), '.');
    }
}
