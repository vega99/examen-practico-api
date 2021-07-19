<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('_encrypt'))
{
    function _encrypt($string, $action = 'e'){
       $secret_key = 'C.r1_Q';
       $secret_iv = 'my_Cr1.q';

       $output = false;
       $encrypt_method = "AES-256-CBC";
       $key = hash( 'sha256', $secret_key );
       $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

       if( $action == 'e' ) {
           $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
       }
       else if( $action == 'd' ){
           $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
       }

       return $output;
    }
}
