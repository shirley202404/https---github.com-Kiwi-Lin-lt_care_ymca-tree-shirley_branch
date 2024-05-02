<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Donate extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('front'); // 網頁 head
    $this->load->library('npmpay'); 
    $this->load->library('encryption');
    $this->load->model('donate_model'); // 捐款資料
    $this->load->model('donate_item_model'); // 捐款項目
    $this->load->model('front_content_model'); // 前台內容資料
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class()); // controllers name
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_menu_title','Main');
    $this->tpl->assign('tv_source','Donate');
    $this->tpl->assign("tv_pub_url" , pub_url('front'));
    $this->tpl->assign("tv_fc01_40_1", $this->front_content_model->get_by_obj_fc01(40, 1)); // 衛服部字號

    /* 金鑰與版本設定 */
    $this->merchant_id = MERCHANT_ID; // 商店代號
    $this->hash_key    = HASH_KEY;    // HashKey
    $this->hash_iv     = HASH_IV; 		// HashIV
    $this->normal_url  = NORMAL_URL;  // 一般付款方式環境 URL
    $this->normal_ver  = NORMAL_VER;  // 一般付款方式版本
    $this->period_url  = PERIOD_URL;  // 定期定額付款方式環境 URL
    $this->period_ver  = PERIOD_VER;  // 定期定額付款方式版本
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 捐款頁面
  //  程式設計: Kiwi
  //  設計日期: 2021/11/17
  // **************************************************************************
  public function index() {
    // $get_data = $this->input->get();
    // if(empty($get_data['di_s_num'])){
      // $get_data['di_s_num'] = 3;
    // }
    $this->zi_init->captcha(); // 產生驗證碼
    $donate_item_row = $this->donate_item_model->get_all_is_available();
    // $this->tpl->assign('tv_di_s_num', $get_data['di_s_num']);
    $this->tpl->assign('tv_donate_item_row', $donate_item_row);
    $this->tpl->assign('tv_cap_path',base_url().'pub/captcha/');
    $this->tpl->assign('tv_cap_img',$_SESSION['cap']['filename']);
    $this->tpl->assign('tv_captcha_link',base_url().'front/donate/captcha/');
    $this->tpl->assign("tv_main_link" , front_url().'main/');
    $this->tpl->assign("tv_save_link" , front_url().'donate/save/add');
    $this->tpl->assign("tv_donate_tab_next_link" , front_url().'donate/tab/next/2');
    $this->tpl->assign("tv_pay_link" , front_url()."donate/pay");
    $this->tpl->assign("tv_thank_link" , front_url()."donate/thank");
    $this->tpl->display("front/donate.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: donate_embed()
  //  函數功能: 捐款頁面(嵌入用)
  //  程式設計: shirley
  //  設計日期: 2022/11/23
  // **************************************************************************
  public function donate_embed() {
    // $get_data = $this->input->get();
    // if(empty($get_data['di_s_num'])){
      // $get_data['di_s_num'] = 1;
    // }
    
    $this->zi_init->captcha(); // 產生驗證碼
    $donate_item_row = $this->donate_item_model->get_all_is_available();
    // $this->tpl->assign('tv_di_s_num', $get_data['di_s_num']);
    $this->tpl->assign('tv_donate_item_row', $donate_item_row);
    $this->tpl->assign('tv_cap_path',base_url().'pub/captcha/');
    $this->tpl->assign('tv_cap_img',$_SESSION['cap']['filename']);
    $this->tpl->assign('tv_captcha_link',base_url().'front/donate/captcha/');
    $this->tpl->assign("tv_main_link" , front_url().'main/');
    $this->tpl->assign("tv_save_link" , front_url().'donate/save/add');
    $this->tpl->assign("tv_donate_tab_next_link" , front_url().'donate/tab/next/2');
    $this->tpl->assign("tv_pay_link" , front_url()."donate/pay");
    $this->tpl->assign("tv_thank_link" , front_url()."donate/thank");
    $this->tpl->display("front/donate_embed.html");
    return;
  }

  // **************************************************************************
  //  函數名稱: pay()
  //  函數功能: 付款
  //  程式設計: Kiwi
  //  設計日期: 2021/11/17
  // **************************************************************************
  public function pay() {
    // 處理SESSION值會被清掉的問題
    if(version_compare(PHP_VERSION, '7.3', '>=')) {
      setcookie('cross-site-cookie', 'name', ['samesite' => 'None', 'secure' => true]);
      setcookie('cookie2', 'name', ['samesite' => 'None', 'secure' => true]);
    }
    else {
      header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');
      header('Set-Cookie: cookie2=name; SameSite=None; Secure', false);
    }
    $get_data = $this->input->post();
    $s_num = null;
    $s_num = $this->donate_model->save_front_add(); // 新增儲存
    $order_no = $s_num;
    
    // s_num 加密 BEGIN // 
    $rand_num = rand(2,6);
    $time = date('U');
    $time_en = substr($this->encryption->encrypt($time),0,4); // 取4碼當混亂使用
    $verify_s_num = $s_num;
    $verify_s_num_en = '';
    for($i = 0; $i < strlen($verify_s_num); $i++) {
      $verify_s_num_en .= substr($verify_s_num,$i,1).random_string('alnum', $rand_num);
    }
    $s_num = "{$time_en}{$rand_num}".base64url_encode($verify_s_num_en);
    // s_num 加密 END //

    $return_url = front_url()."donate/thank/{$s_num}"; // 支付完成 返回商店網址
    $notify_url = front_url()."donate/back/";          // 支付通知網址    
    $customer_url = ""; 						                   // 商店取號網址
    $client_back_url  = front_url()."donate/cancel"; 	 // 支付取消 返回商店網址

    $order_title = "慈心捐款";	     	   // 慈心捐款
    $atm_expiredate = 3;			          // ATM付款到期日
    $donate_money = $get_data['de06'];  // 捐款金額
    
    if($get_data['donate_type'] != 'period') { // 非定期定額
      // 送給藍新資料 BEGIN //
      $trade_info_arr = array(
        'MerchantID'      => $this->merchant_id,
        'RespondType'     => 'JSON',
        'TimeStamp'       => time(),
        'Version'         => $this->normal_ver,
        'MerchantOrderNo' => $order_no,
        'Amt'             => $donate_money,
        'ItemDesc'        => $order_title,
        'ReturnURL'       => $return_url,        // 支付完成 返回商店網址
        'NotifyURL'       => $notify_url,    // 支付通知網址
        'CustomerURL'     => $customer_url,      // 商店取號網址
        'ClientBackURL'   => $return_url ,       // 支付取消 返回商店網址
        'ExpireDate'      => date("Y-m-d" , mktime(0 , 0 , 0 , date("m") , date("d") + $atm_expiredate , date("Y"))),
        'Email'           => "{$get_data['de03_email']}" // 可以直接帶入付款人信箱,
      );

      // 'CREDIT'   = CREDIT
      // 'WEBATM'   = WEBATM
      // 'VACC'     = VACC(金額不可超過5萬元)
      // 'CVS'      = 超商代碼繳費-CVS(金額不可超過2萬元)
      // 'BARCODE'  = BARCODE(金額不可超過4萬元)
      // 'LINEPAY'  = LINEPAY
      // 'ANDROIDPAY'  = GOOGLE PAY
      switch ($get_data['donate_type']) { // 付款方式
        case 'credit':
          $trade_info_arr['CREDIT'] = 1;
          break;
        case 'vacc':
          $trade_info_arr['VACC'] = 1;
          break;
        case 'cvs':
          $trade_info_arr['CVS'] = 1;
          break;
        case 'barcode':
          $trade_info_arr['BARCODE'] = 1;
          break;
        case 'androidpay':
          $trade_info_arr['ANDROIDPAY'] = 1;
          break;
        case 'linepay':
          $trade_info_arr['LINEPAY'] = 1;
          break;
      }
      //u_var_dump($trade_info_arr);
      // 送給藍新資料 END //
      if(isset($get_data['pay']) == 1 && $get_data['pay'] == "y"){
        $trade_info = $this->npmpay->create_mpg_aes_encrypt($trade_info_arr , $this->hash_key , $this->hash_iv);
        $sha256 = strtoupper(hash("sha256", $this->npmpay->SHA256($this->hash_key , $trade_info , $this->hash_iv)));
        echo $this->npmpay->CheckOut($this->normal_url , $this->merchant_id , $trade_info , $sha256 , $this->normal_ver);
      }
    }
    else {  
      $trade_info_arr = array(
        'MerchantID'      => $this->merchant_id,
        'RespondType'     => 'JSON',
        'TimeStamp'       => time(),
        'Version'         => $this->period_ver,
        'MerOrderNo'      => $order_no,
        'PeriodAmt'       => $donate_money,
        'ProdDesc'        => $order_title,
        'PeriodType'      => "M",                          // 週期類別
        'PeriodTimes'     => "{$get_data['de07']}",        // 授權期數
        'PeriodPoint'     => "{$get_data['de08']}",        // 扣款日
        'PeriodStartType' => '2',                          // 檢查卡號模式
        'PayerEmail'      => "{$get_data['de03_email']}",  // 付款人電子信箱
        'ReturnURL'       => $return_url,                  // 支付完成 返回商店網址
        'NotifyURL'       => $notify_url_atm,              // 支付通知網址
        'CustomerURL'     => $customer_url,                // 商店取號網址
        'ClientBackURL'   => $client_back_url ,            // 支付取消 返回商店網址
        'PaymentInfo'     => "N" ,                         // 是否開啟 付款人資訊
        'OrderInfo'       => "N" ,                         // 是否開啟 收件人資訊
        'ExpireDate'      => date("Y-m-d" , mktime(0 , 0 , 0 , date("m") , date("d") + $atm_expiredate , date("Y"))),
      );
      // return;
      // 送給藍新資料 END //
      if(isset($get_data['pay']) == 1 && $get_data['pay'] == "y"){
        $trade_info_arr = http_build_query($trade_info_arr); //經過 URL-encode 後的 String 組合(加密前字串)
        $trade_info = $this->npmpay->period_encrypt($this->hash_key , $this->hash_iv , $trade_info_arr);
        echo $this->npmpay->period_CheckOut($this->period_url , $this->merchant_id , $trade_info);
      }
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: thank()
  //  函數功能: 付款完成
  //  程式設計: Kiwi
  //  設計日期: 2021/11/17
  // **************************************************************************
  public function thank($en_s_num) {
    // s_num 解密 END //
    $rand_num = substr($en_s_num,4,1);
    $en_s_num_len = strlen($en_s_num);
    $de_s_num = base64url_decode(substr($en_s_num,5,$en_s_num_len-5));
    $verify_s_num = substr($de_s_num,0,1); // 第1碼
    for($i = 1; $i <= 11; $i++) { // s_num不會超過11個字
      $verify_s_num .= substr($de_s_num,($rand_num*$i)+$i,1); // 第2-6碼
    }
    // s_num 解密 END //

    $status = '';
    if(isset($_POST['TradeInfo'])) {
      $return_trade_info = json_decode($this->npmpay->create_aes_decrypt($_POST['TradeInfo'], $this->hash_key, $this->hash_iv), true); // 回傳付款資訊
      $this->donate_model->save_front_upd($return_trade_info, $verify_s_num); // 更新儲存
    }
    if(isset($_POST['Period'])) {
      $return_period_info = json_decode($this->npmpay->period_decrypt($this->hash_key, $this->hash_iv, $_POST['Period']), true); // 回傳付款資訊
      $this->donate_model->save_front_upd($return_period_info, $verify_s_num); // 更新儲存
    }
    $donate_row = $this->donate_model->get_one($verify_s_num); 
    if(NULL != $donate_row) {
      if($donate_row->de15 == 'SUCCESS') {
        $sms_body = '';
        $status = '捐款成功';
        switch(ENVIRONMENT) {
          case 'development': // 開發
          case 'testing': // 測試
            $sms_body .= '[測試簡訊]';
            break;
          case 'production': // 正式
            $sms_body .= '';
            break;
        }
        if(NULL != $donate_row->de03_phone) {
          if($donate_row->de10 != NULL){
            $sms_body .= $donate_row->de10;
          }else{
            $name = $donate_row->de01.$donate_row->de02;
            $sms_body .= "{$name}先生/小姐";
          }
          $sms_body .= "您好:感謝您的愛心，對弗傳慈心送餐服務受惠長者的支持與援助，您本次的捐款金額為 NTD ". number_format($donate_row->de06) ."，捐款序號為".$donate_row->de18."，再次萬分感謝您的善心!!";
          $sms_send = $this->zi_my_func->send_sms('send', $donate_row->de03_phone, $sms_body);
          if('ok' != $sms_send) {
            return($sms_send); // 簡訊發送異常訊息
          }
        }
      }
      else {
        switch ($donate_row->de09) {
          case 3: // atm轉帳
            $status = "請至ATM轉帳";
            break;
          case 4: // 超商繳費
            $status = "請至超商繳費";
            break;
          case 5: // 超商條碼
            $status = "請至超商列印條碼繳費";
            break;
          default:
            $status = '捐款失敗';
            break;
        }
      }
    }
    //u_var_dump($donate_row);
    $this->tpl->assign("tv_status" , $status);
    $this->tpl->assign("tv_donate_row" , $donate_row);
    $this->tpl->assign("tv_main_link" , front_url().'main/');
    $this->tpl->assign("tv_donate_link" , front_url().'donate/');
    $this->tpl->display("front/thankyou.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cancel()
  //  函數功能: 付款取消
  //  程式設計: Kiwi
  //  設計日期: 2021/12/2
  // **************************************************************************
  public function cancel() {
    echo "頁面開發中!!";
  }
  // **************************************************************************
  //  函數名稱: back()
  //  函數功能: 非即時支付方式，收回訊息
  //  程式設計: Kiwi
  //  設計日期: 2023/08/02
  // **************************************************************************
  public function back() {
    $this->load->library('npmpay'); // 藍星支付
    $return_trade_info = json_decode($this->npmpay->create_aes_decrypt($_POST['TradeInfo'], 'ub30jvOkxD39IjzNY8eHM3nsBYM6vZFH', 'CuaisVBGU5ttxDBP'), true); // 回傳付款資訊
    
    // 暫時先將結果存起來
    $mrand_str = $this->config->item('rand_str_8');
    $file = FCPATH."export_file/test_des_donate_{$mrand_str}.txt";
    file_put_contents($file, json_encode($return_trade_info));

    if('SUCCESS' == $return_trade_info['Status'] and isset($return_trade_info['Result']['MerchantOrderNo'])) {
      $this->donate_model->save_front_upd($return_trade_info, $return_trade_info['Result']['MerchantOrderNo']); // 更新儲存
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: captcha()
  //  函數功能: 驗證碼
  //  程式設計: shirley
  //  設計日期: 2022/02/01(春節初一)
  // **************************************************************************
  public function captcha() {
    $this->zi_init->captcha(); // 產生驗證碼
    //var_dump($_SESSION['cap']);
    echo $_SESSION['cap']['filename'];
    
    return;
  }

  function __destruct() {
    $this->zi_init->footer('front'); // 網頁 foo
  }
}