<?php
namespace sinri\databasehub\library;
/**
 * Class PrpcryptLibrary
 * @package sinri\databasehub\library
 */
class PrpcryptLibrary
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($text)
    {
        //获得16位随机字符串，填充到明文之前
        $random = $this->getRandomStr();
        $text = $random . $text;
        // encode
        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, substr($this->key,0,16));
        return base64_encode($encrypted);
    }

    public function decrypt($encrypted)
    {
        $ciphertext_dec = base64_decode($encrypted);
        $decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, substr($this->key,0,16));
        return substr($decrypted, 16, strlen($decrypted));
    }

    public function getRandomStr($len = 16)
    {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $len; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}
