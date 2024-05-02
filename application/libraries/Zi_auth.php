<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zi_auth {
  public function __construct() {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
    //u_var_dump(get_class_methods($this->CI->router->class)); // 取得目前class 的 所有 function
    //u_var_dump($this->CI->router->directory);
    //u_var_dump($this->CI->router->class);
    //u_var_dump($this->CI->router->method);
    //u_var_dump($this->CI->uri->segment(4, 0));
  }
  // **************************************************************************
  //  函數名稱: _chk_run()
  //  函數功能: 檢查權限是否可以執行
  //  程式設計: Tony
  //  設計日期: 2018/10/2
  // **************************************************************************
  public function _chk_run($class=NULL) {
    switch($class) {
      case "login": // 登入
      case "logout": // 登出
      case "main": // 系統首頁
      case "fileupload": // 檔案上傳
      case "sys_cr_php": // MVC程式產生器
      case "testunit": // 單元測試
      case "info_edit": // 編輯個人資料
      case "work_q_route_list": // 問卷資料
      case "work_q": // 問卷資料
      case "gps_log": // GPS紀錄
      case "punch_log": // 打卡紀錄
      //case "sys_account": // 系統選單 (如果db密碼壞了,這個要解開來避免權限檢查)
      //case "sys_menu": // 系統選單 (如果db密碼壞了,這個要解開來避免權限檢查)
        // 不用檢查權限
        return;
      case "sys_account": // 登入資料
        $kind = 'othe';
        if(!method_exists('zi_auth',"_auth_{$class}")) {
          die("注意!!! [zi_auth->_auth_{$class}()]，尚未設定權限!!!");
        }
        break;
      default:
        $kind = 'std';
        break;
    }
    if('std'==$kind) {
      $auth_class = "_auth_std"; // 一般共用的的權限
    }
    else { // othe
      $auth_class = "_auth_{$this->CI->router->class}"; // 自己設定的權限
    }
    eval('return $this->$auth_class();'); // 執行該 function // $this->_auth_member();
    //u_var_dump($kind);
    return;
  }
  // **************************************************************************
  //  函數名稱: _auth_std()
  //  函數功能: 檢查權限是否可以執行-一般共用的權限(瀏覽、新增、修改、刪除、查詢、列印、下載)
  //  程式設計: Tony
  //  設計日期: 2018/10/2
  // **************************************************************************
  private function _auth_std()  {
    $agu_open_ct_name = NULL;
    switch ($this->CI->router->method) {
      case "index":
      case "index2":
      case "disp":
        $agu_open_ct_name = 'list';         // 瀏覽
        break;
      case "add":
      case "cpy":
      case "produce":
      case "produce_meal_replacement":
      case "log_confirm":
      case "import":
      case "import_excel":
      case "import_old_excel":
      case "rest":
        $agu_open_ct_name = 'add';          // 新增
        break;
      case "upd":
      case "chk":
      case "meal_order":
      case "file_upload":
      case "upd_client_name":
      case "over":
      case "over_cancel":
      case "upd_meal_order":
      case "arrange":
      case "change":
      case "upd_reh":                       // 更新繳費資料-案主路徑資料
        $agu_open_ct_name = 'upd';          // 修改
        break;
      case "del":
      case "del_identity":                  // 刪除身分別資料
        $agu_open_ct_name = 'del';          // 刪除
        break;
      case "que":
      case "que_hist":                      // 查詢異動歷史紀錄
      case "que_ct":                        // 查詢案主資料
      case "que_ct_disp":                   // 查詢案主詳細資料
      case "reh_que_ct":                    // 查詢案主資料(路徑資料用)
      case "chk_ct03":                      // 檢查案主身分證
      case "chk_sw03":                      // 檢查社工身分證
      case "chk_dp03":                      // 檢查外送員身分證
      case "que_client_data":               // 查詢案主資料
      case "que_client_route_data":         // 查詢案主路線資料
      case "que_client_service_data":       // 查詢案主開案資料
      case "que_dp":                        // 查詢外送員資料
      case "que_sec":                       // 查詢開案資料
      case "que_data":                      // 查詢非餐食異動資料、查詢餐食異動資料、查詢停餐資料
      case "address_convert":               // 案主地址轉成經緯度
      case "que_meal_order_status":         // 查詢訂單狀況
        $agu_open_ct_name = 'que'; // 查詢
        break;
      case "save":
        switch($this->CI->uri->segment(4, 0)) {
          case "add": // 新增儲存
          case "cpy": // 複製儲存
          case "convert": // 案主暫存資料轉入正式資料
          case "import": // 匯入儲存
          case "import_excel": // 匯入儲存
          case "import_old_excel": // 匯入儲存
            $agu_open_ct_name = 'add'; // 新增
            break;
          case "upd": // 修改儲存
          case "upd_price": // 餐點修改價格
          case "change": // 修改儲存
          case "upd_is_available": // 上下架儲存
          case "upd_this_page": // 本頁儲存
          case "sort_up": // 排序上移儲存
          case "sort_down": // 排序下移儲存
          case "punch": // 打卡補登儲存
          case "upd_vp": // 更新核備人員資料
            $agu_open_ct_name = 'upd'; // 修改
            break;
        }
        break;
      case "prn":
      case "pdf":
        $agu_open_ct_name = 'prn'; // 列印
        break;
      case "download":
      case "download2":
      case "download3":
      case "download_execl":
      case "download_file":
      case "download_stats":
      case "download_meal_num":
      case "download_blank":
        $agu_open_ct_name = 'download'; // 下載
        break;
      case "sw_auth": // 社工確認
	   $agu_open_ct_name = 'money'; // 金額
	   break;
	 case "wo_auth": // 核銷人員
	   $agu_open_ct_name = 'cf'; // 發單確認
	   break;
	 case "me_auth": // 製餐人員
	   $agu_open_ct_name = 'cf_report'; // 列印確認
	   break;
    }
    $agu_open = $this->CI->sys_account_group_auth_model->get_agu_open_by_acc(); // 取得目前使用者使用的CT使用權限
    //u_var_dump($agu_open);
    if(!isset($agu_open[$agu_open_ct_name])) {
      $this->CI->tpl->display("be/not_permission.html");
      die();
    }
    if('N'==$agu_open[$agu_open_ct_name] or NULL==$agu_open) {
      $this->CI->tpl->display("be/not_permission.html");
      die();
    }
    $this->CI->tpl->assign('tv_agu_open_ct_name',$agu_open_ct_name);
    $this->CI->tpl->assign('tv_agu_open',$agu_open);
    return;
  }
  // **************************************************************************
  //  函數名稱: _auth_sys_account()
  //  函數功能: 檢查權限是否可以執行-會員資料
  //  程式設計: Tony
  //  設計日期: 2018/10/2
  // **************************************************************************
  private function _auth_sys_account()  {
    $agu_open_ct_name = NULL;
    switch ($this->CI->router->method) {
      case "info_edit": // 編輯個人資料
      case "pwd_edit": // 密碼修改
        return;
      case "index":
      case "disp":
        $agu_open_ct_name = 'list'; // 瀏覽
        break;
      case "add":
      case "cpy":
        $agu_open_ct_name = 'add'; // 新增
        break;
      case "upd":
      case "info_edit":
      case "pwd_edit":
        $agu_open_ct_name = 'upd'; // 修改
        break;
      case "del":
        $agu_open_ct_name = 'del'; // 刪除
        break;
      case "que":
        $agu_open_ct_name = 'que'; // 查詢
      case "save":
        switch($this->CI->uri->segment(4, 0)) {
          case "info_edit": // 編輯個人資料
          case "pwd_edit": // 密碼修改
            return; // 這兩個不檢查權限
          case "add": // 新增儲存
            $agu_open_ct_name = 'add'; // 新增
            break;
          case "upd": // 修改儲存
          case "upd_is_available": // 上下架儲存
            $agu_open_ct_name = 'upd'; // 修改
            break;
        }
        break;
    }
    $agu_open = $this->CI->sys_account_group_auth_model->get_agu_open_by_acc(); // 取得目前使用者使用的CT使用權限
    //u_var_dump($agu_open);
    if(!isset($agu_open[$agu_open_ct_name])) {
      $this->CI->tpl->display("be/not_permission.html");
      die();
    }
    if('N'==$agu_open[$agu_open_ct_name] or NULL==$agu_open) {
      $this->CI->tpl->display("be/not_permission.html");
      die();
    }
    $this->CI->tpl->assign('tv_agu_open_ct_name',$agu_open_ct_name);
    return;
  }

}

?>
