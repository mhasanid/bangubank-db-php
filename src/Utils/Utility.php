<?php

namespace App\Utils;

class Utility
{
    public static function sanitize(string $data): string
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }
    
    public static function dd(mixed $data): void
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
    
    public static function flash($key, $message = null)
    {
        if ($message) {
            $_SESSION['flash'][$key] = $message;
        }
        else if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
    }

    public static function convertToSha256($data) {
        return hash('sha256', $data);
    }
}



