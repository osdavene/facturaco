<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Procesa, redimensiona proporcionalmente y comprime una imagen.
     * Convierte imágenes pesadas (3MB - 10MB) a WebP o JPEG optimizado (~50KB - 250KB).
     *
     * @param UploadedFile $archivo
     * @param string $carpeta Directorio destino dentro de storage/app/public (ej: 'productos', 'logos')
     * @param int $maxDimension Dimensión máxima en ancho o alto (px)
     * @param int $calidad Calidad de compresión (0 a 100)
     * @return string Ruta relativa guardada (ej: 'productos/abc12345.webp')
     */
    public function optimizarYGuardar(
        UploadedFile $archivo,
        string $carpeta = 'productos',
        int $maxDimension = 1000,
        int $calidad = 80
    ): string {
        $storageDir = storage_path('app/public/' . trim($carpeta, '/'));
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $extensionOriginal = strtolower($archivo->getClientOriginalExtension());
        $mime = $archivo->getMimeType();
        $rutaTemporal = $archivo->getRealPath();

        // Si GD no está disponible o no es una imagen procesable, guardar directamente
        if (!extension_loaded('gd') || !$rutaTemporal || !file_exists($rutaTemporal)) {
            return $archivo->store($carpeta, 'public');
        }

        // Crear recurso de imagen según el formato
        $origen = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $origen = @imagecreatefromjpeg($rutaTemporal);
                break;
            case 'image/png':
                $origen = @imagecreatefrompng($rutaTemporal);
                break;
            case 'image/webp':
                $origen = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($rutaTemporal) : null;
                break;
            case 'image/gif':
                $origen = @imagecreatefromgif($rutaTemporal);
                break;
            case 'image/bmp':
                $origen = function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($rutaTemporal) : null;
                break;
        }

        if (!$origen) {
            return $archivo->store($carpeta, 'public');
        }

        // Corregir orientación automática si existe información EXIF (fotos de celulares)
        if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'])) {
            try {
                $exif = @exif_read_data($rutaTemporal);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $origen = imagerotate($origen, 180, 0);
                            break;
                        case 6:
                            $origen = imagerotate($origen, -90, 0);
                            break;
                        case 8:
                            $origen = imagerotate($origen, 90, 0);
                            break;
                    }
                }
            } catch (\Throwable) {
                // Si falla la lectura de exif, continuar normalmente
            }
        }

        $anchoOriginal = imagesx($origen);
        $altoOriginal  = imagesy($origen);

        // Calcular nuevas dimensiones manteniendo la relación de aspecto
        $nuevoAncho = $anchoOriginal;
        $nuevoAlto  = $altoOriginal;

        if ($anchoOriginal > $maxDimension || $altoOriginal > $maxDimension) {
            if ($anchoOriginal >= $altoOriginal) {
                $nuevoAncho = $maxDimension;
                $nuevoAlto  = (int) round(($altoOriginal / $anchoOriginal) * $maxDimension);
            } else {
                $nuevoAlto  = $maxDimension;
                $nuevoAncho = (int) round(($anchoOriginal / $altoOriginal) * $maxDimension);
            }
        }

        // Crear lienzo de destino con soporte de transparencia
        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        $transparencia = imagecolorallocatealpha($destino, 0, 0, 0, 127);
        imagefilledrectangle($destino, 0, 0, $nuevoAncho, $nuevoAlto, $transparencia);

        // Redimensionar con algoritmo bicúbico de alta calidad
        imagecopyresampled(
            $destino,
            $origen,
            0, 0, 0, 0,
            $nuevoAncho, $nuevoAlto,
            $anchoOriginal, $altoOriginal
        );

        $nombreBase = Str::random(40);

        // Preferir WebP para máxima compresión y calidad; si no, JPEG optimizado
        if (function_exists('imagewebp')) {
            $nombreArchivo = $nombreBase . '.webp';
            $rutaDestino   = $storageDir . '/' . $nombreArchivo;
            imagewebp($destino, $rutaDestino, $calidad);
        } else {
            $nombreArchivo = $nombreBase . '.jpg';
            $rutaDestino   = $storageDir . '/' . $nombreArchivo;
            imagejpeg($destino, $rutaDestino, $calidad);
        }

        // Liberar memoria
        imagedestroy($origen);
        imagedestroy($destino);

        return trim($carpeta, '/') . '/' . $nombreArchivo;
    }
}
