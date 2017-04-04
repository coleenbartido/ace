<?php

class Encryption 
{
    private $cryptKey;

    function __construct()
    {
        $this->cryptKey  = "gamechangers";
    }

    public function encryptIt( $string )    
    {
        $stringEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $this->cryptKey ), $string, MCRYPT_MODE_CBC, md5( md5( $this->cryptKey ) ) ) );
        return( $stringEncoded );
    }

    public function decryptIt( $string ) 
    {      
        $stringDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $this->cryptKey ), base64_decode( $string ), MCRYPT_MODE_CBC, md5( md5( $this->cryptKey ) ) ), "\0");
        return( $stringDecoded );
    }
}





