<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// 藍新金流
class Npmpay {
  function __construct(){
    //$this->CI =& get_instance();
  }

  function create_mpg_aes_encrypt($parameter = "" , $key = "", $iv = "") {
    $return_str = '';
    if (!empty($parameter)) {
      //將參數經過 URL ENCODED QUERY STRING
      $return_str = http_build_query($parameter);
    }
    return trim(bin2hex(openssl_encrypt($this->addpadding($return_str), 'aes-256-cbc', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv)));
  }

  function addpadding($string, $blocksize = 32) {
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= str_repeat(chr($pad), $pad);

    return $string;
  }

  /*HashKey AES 解密 */
  function create_aes_decrypt($parameter = "", $key = "", $iv = "") {
    return $this->strippadding(openssl_decrypt(hex2bin($parameter),'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv));
  }

  function strippadding($string) {
    $slast = ord(substr($string, -1));
    $slastc = chr($slast);
    $pcheck = substr($string, -$slast);
    if (preg_match("/$slastc{" . $slast . "}/", $string)) {
      $string = substr($string, 0, strlen($string) - $slast);
      return $string;
    } else {
      return false;
    }
  }

  /*HashIV SHA256 加密*/
  function SHA256($key="", $tradeinfo="", $iv=""){
    $HashIV_Key = "HashKey=".$key."&".$tradeinfo."&HashIV=".$iv;
    return $HashIV_Key;
  }

  function CheckOut($URL="", $MerchantID="", $TradeInfo="", $SHA256="", $VER="") {
    $szHtml = '<!doctype html>';
    $szHtml .='<html>';
    $szHtml .='<head>';
    $szHtml .='<meta charset="utf-8">';
    $szHtml .='</head>';
    $szHtml .='<body>';
    $szHtml .='<form name="newebpay" id="newebpay" method="post" action="'.$URL.'" style="display:none;">';
    $szHtml .='<input type="text" name="MerchantID" value="'.$MerchantID.'" type="hidden">';
    $szHtml .='<input type="text" name="TradeInfo" value="'.$TradeInfo.'"   type="hidden">';
    $szHtml .='<input type="text" name="TradeSha" value="'.$SHA256.'" type="hidden">';
    $szHtml .='<input type="text" name="Version"  value="'.$VER.'" type="hidden">';
    $szHtml .='</form>';
    $szHtml .='<script type="text/javascript">';
    $szHtml .='document.getElementById("newebpay").submit();';
    $szHtml .='</script>';
    $szHtml .='</body>';
    $szHtml .='</html>';
    return $szHtml;
  }

  function period_CheckOut($URL="", $MerchantID="", $PostData="") {    
    $szHtml = '<!doctype html>';
    $szHtml .='<html>';
    $szHtml .='<head>';
    $szHtml .='<meta charset="utf-8">';
    $szHtml .='</head>';
    $szHtml .='<body>';
    $szHtml .='<form name="newebpay" id="newebpay" method="post" action="'.$URL.'" style="display:none;">';
    $szHtml .='<input type="text" name="MerchantID_" value="'.$MerchantID.'" type="hidden">';
    $szHtml .='<input type="text" name="PostData_" value="'.$PostData.'"   type="hidden">';
    $szHtml .='</form>';
    $szHtml .='<script type="text/javascript">';
    $szHtml .='document.getElementById("newebpay").submit();';
    $szHtml .='</script>';
    $szHtml .='</body>';
    $szHtml .='</html>';
    return $szHtml;
  }
  
  function que_CheckOut($URL="", $que_data=NULL) {
    $szHtml = '<!doctype html>';
    $szHtml .='<html>';
    $szHtml .='<head>';
    $szHtml .='<meta charset="utf-8">';
    $szHtml .='</head>';
    $szHtml .='<body>';
    $szHtml .='<form name="newebpay" id="newebpay" method="post" action="'.$URL.'" style="display:none;">';
    $szHtml .='<input type="text" name="MerchantID" value="'.$que_data['MerchantID'].'" type="hidden">';
    $szHtml .='<input type="text" name="Version"  value="'.$que_data['Version'].'" type="hidden">';
    $szHtml .='<input type="text" name="RespondType"  value="'.$que_data['RespondType'].'" type="hidden">';
    $szHtml .='<input type="text" name="CheckValue"  value="'.$que_data['CheckValue'].'" type="hidden">';
    $szHtml .='<input type="text" name="TimeStamp"  value="'.$que_data['TimeStamp'].'" type="hidden">';
    $szHtml .='<input type="text" name="MerchantOrderNo"  value="'.$que_data['MerchantOrderNo'].'" type="hidden">';
    $szHtml .='<input type="text" name="Amt"  value="'.$que_data['Amt'].'" type="hidden">';
    $szHtml .='</form>';
    $szHtml .='<script type="text/javascript">';
    $szHtml .='document.getElementById("newebpay").submit();';
    $szHtml .='</script>';
    $szHtml .='</body>';
    $szHtml .='</html>';
    return $szHtml;
  }

  function period_encrypt($key = "", $iv = "", $str = "") {
    $str = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $this->addpadding($str), MCRYPT_MODE_CBC, $iv)));
    return $str;
    }

  function period_addpadding($string, $blocksize = 32) {
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= str_repeat(chr($pad), $pad);
    return $string;
  }

  function period_decrypt($key = '', $iv = '', $encrypt = ''){
    $str = $this->period_strippadding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, hex2bin($encrypt), MCRYPT_MODE_CBC, $iv));
    return $str;
    }

  function period_strippadding($string) {
    $slast = ord(substr($string, -1));
    $slastc = chr($slast);
    if (preg_match("/$slastc{" . $slast . "}/", $string)) {
     $string = substr($string, 0, strlen($string) - $slast);
    return $string;
    } 
    else {
      return false;
    }
  }

  /*取得訂單編號*/
  function getOrderNo(){
    date_default_timezone_set('Asia/Taipei'); // CDT
    $info = getdate();
    $date = $info['mday'];
    $month = $info['mon'];
    $year = $info['year'];
    $hour = $info['hours'];
    $min = $info['minutes'];
    $sec = $info['seconds'];
    $ordre_no = $year.$month.$date.$hour.$min.$sec;

    return $ordre_no;
  }
    
}
?>