<?php
class SecurityHelper {
    // Kunci rahasia ini HARUS SAMA PERSIS di project Admin dan Landing Page
    private static $secret_key = 'DishubSlemanPJU_2026_AsdF$*';
    private static $secret_iv = 'PJU_Sleman_ASDF_2026';
    private static $encrypt_method = "AES-256-CBC";

    public static function encrypt($string) {
        $key = hash('sha256', self::$secret_key);
        $iv = substr(hash('sha256', self::$secret_iv), 0, 16);
        $output = openssl_encrypt($string, self::$encrypt_method, $key, 0, $iv);
        // Jadikan aman untuk URL (hilangkan karakter +, /, =)
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($output));
    }

    public static function decrypt($string) {
        $key = hash('sha256', self::$secret_key);
        $iv = substr(hash('sha256', self::$secret_iv), 0, 16);
        // Kembalikan karakter URL ke format base64 asli
        $string = base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
        return openssl_decrypt($string, self::$encrypt_method, $key, 0, $iv);
    }
}