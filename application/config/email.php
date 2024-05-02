<?php

/*
|--------------------------------------------------------------------------
| Mail Configuration
|--------------------------------------------------------------------------
|
| Mail Configuration for send emails using Mail services like Mailgun and Mailchimp
|
*/
// 參考設定內容
// http://codeigniter-userguide.readthedocs.io/zh_TW/latest/libraries/email.html
$config['email']['protocol']     = 'smtp';
switch(ENVIRONMENT) {
  case 'development':
  case 'testing':
    // Hinet
    $config['email']['smtp_host']    = 'ms7.hinet.net';

    // Tony Mailgun
    //$config['email']['smtp_host']    = 'smtp.mailgun.org';
    //$config['email']['smtp_port']    = '587';
    //$config['email']['smtp_timeout'] = '30';
    //$config['email']['smtp_user']    = 'postmaster@zion.idv.tw';
    //$config['email']['smtp_pass']    = '096954d4e98b8d50ee48ec75346a3b90';
    break;
  case 'production': // 正式機，依據客戶給的設定修正
    // mailgun
    //$config['email']['smtp_host']    = 'smtp.mailgun.org'; // 正式機
    //$config['email']['smtp_port']    = '587';
    //$config['email']['smtp_timeout'] = '30';
    //$config['email']['smtp_user']    = 'postmaster@客戶申請的domain';
    //$config['email']['smtp_pass']    = '客戶申請的密碼';

    // ssl
    //$config['email']['smtp_host']    = 'ssl://客戶的smtp主機'; 
    //$config['email']['smtp_port']    = '465';
    //$config['email']['smtp_user']    = '客戶的帳號';
    //$config['email']['smtp_pass']    = '客戶的密碼';

    // tls
    //$config['email']['smtp_crypto']  = 'tls';
    //$config['email']['smtp_host']    = 'mail.ntu.edu.tw'; // 跟計中要的資料
    //$config['email']['smtp_port']    = '587';
    //$config['email']['smtp_user']    = '客戶的帳號';
    //$config['email']['smtp_pass']    = '客戶的密碼';
    break;
}
$config['email']['smtp_timeout'] = '30';
$config['email']['smtp_crypto']  = '';
$config['email']['charset']      = 'utf-8';
//$config['email']['mailtype']     = 'text'; // 信件內容-純文字
$config['email']['mailtype']     = 'html'; // 信件內容-html
$config['email']['wordwrap']     = TRUE;
$config['email']['crlf']         = "\r\n"; // 換行
$config['email']['newline']      = "\r\n"; // 換行