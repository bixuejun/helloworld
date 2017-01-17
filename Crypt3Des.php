<?php

/**
 * 3DES加密解密类
 * @author root
 * @date 2017-01-03
 * 接口要求：先用3DES对数据进行加密，在用BASE64进行加密。即：BASE64(3DES(value))。
 * 3DES加密规则：
 * 模式：ECB
 * 填充模式：PKCS5/PKCS7Padding（可选）
 * 初始化向量：无
 * 密钥：XXXXXXXXXXXXXXXXX（24位）
 */
class Crypt3Des
{

    public $key = "012345678901234567890123";
    
    function __construct($key)
    {
        $this->key = $key;
    }

    function encrypt($input)
    { 
        // 数据加密
        $size = mcrypt_get_block_size(MCRYPT_3DES, 'ecb');
//         $input = $this->pkcs5_pad($input, $size);
        $input = $this->PaddingPKCS7($input);
        $key = str_pad($this->key, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    function decrypt($encrypted)
    { 
        // 数据解密
        $encrypted = base64_decode($encrypted);
        $key = str_pad($this->key, 24, '0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
//         $y = $this->pkcs5_unpad($decrypted);
        $y = $this->UnPaddingPKCS7($decrypted);
        return $y;
    }

    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, - 1 * $pad);
    }

    function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }
    
    function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}


/*
 * 使用示例代码
 */
// $rep = new Crypt3Des('AD905@!QLF-D25WEDA5!@#$%'); // 初始化一个对象
// $input = "hello world";
// echo "原文：" . $input . "<br/>";
// $encrypt_card = $rep->encrypt($input);
// echo "加密：" . $encrypt_card . "<br/>";
// echo "解密：" . $rep->decrypt($rep->encrypt($input));

?>