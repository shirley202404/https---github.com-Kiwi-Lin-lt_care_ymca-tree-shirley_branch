<?php
class Sys_language_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }

  // **************************************************************************
  //  函數名稱: all_list()
  //  函數功能: 列出所有帳號資料
  //  程式設計: Tony
  //  設計日期: 2017/7/20
  // **************************************************************************
  public function all_list() {
    list($vfront,$vwhere)=chk_front(); // 檢查是否為前台，就不顯示下架資料
    $vrow = NULL;
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_language'); // 語系
    $vsql = "select * 
             from {$vtbl_name}
             where d_date is null
                   $vwhere
             order by d_order asc
            ";
    //echo $vsql;
    //echo '<hr>';
    $vrs = $this->db->query($vsql, array($vsql));
    if($vrs->num_rows() > 0) { // 有資料才執行
      foreach ($vrs->result() as $vrow){
        $adata[$vrow->s_num]['s_num']        = $vrow->s_num;
        $adata[$vrow->s_num]['lang_code']    = $vrow->lang_code;
        $adata[$vrow->s_num]['lang_wording'] = $vrow->lang_wording;
        $adata[$vrow->s_num]['lang_note']    = $vrow->lang_note;
        $adata[$vrow->s_num]['d_order']      = $vrow->d_order;
        $adata[$vrow->s_num]['is_available'] = $vrow->is_available;
      }
    }
    return($adata);
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得language單筆資料(s_num)
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
  public function get_one($vs_num=NULL) {
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_language'); // 語系
    $vrow = NULL;
    $vsql = "select * 
             from {$vtbl_name}
             where s_num = ?
                   and d_date is null
             order by s_num
             limit 0,1
            ";
    //echo $vsql;
    //echo '<hr>';
    $vrs = $this->db->query($vsql, array($vs_num));
    if($vrs->num_rows() > 0) { // 有資料才執行
      $vrow = $vrs->row(); 
    }
    return $vrow;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/7/21
  // **************************************************************************
  public function save() {
    $rtn_msg = 'ok';
    $f_kind         = $_POST['f_kind'];
    $f_s_num        = $_POST['f_s_num'];
    $f_lang_code    = $_POST['f_lang_code'];
    $f_d_order      = $_POST['f_d_order'];
    $f_lang_wording = $_POST['f_lang_wording'];
    $f_is_available = $_POST['f_is_available'];
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_language'); // 語系
    
    $vdata['lang_code']     = $f_lang_code;
    $vdata['d_order']       = $f_d_order;
    $vdata['lang_wording']  = $f_lang_wording;
    $vdata['is_available']  = $f_is_available;

    switch ($f_kind) {
      case "add":
        $vdata['b_empno']      = $_SESSION['acc_s_num'];
        $vdata['b_date']       = date('Y-m-d H:i:s');
        break;
      case "upd":
        $vdata['e_empno']      = $_SESSION['acc_s_num'];
        $vdata['e_date']       = date('Y-m-d H:i:s');
        break;
      case "del":
        $vdata['d_empno']      = $_SESSION['acc_s_num'];
        $vdata['d_date']       = date('Y-m-d H:i:s');
        break;
    }
    if('add'==$f_kind) {
      if(!$this->db->insert($vtbl_name, $vdata)) {
        $rtn_msg = $this->lang->line('language_save_'.$f_kind.'_err');
      }
    }
    else {
      $this->db->where('s_num', $f_s_num);
      if(!$this->db->update($vtbl_name, $vdata)) {
        $rtn_msg = $this->lang->line('language_save_'.$f_kind.'_err');
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2017/7/21
  // **************************************************************************
  public function del($v_s_num=NULL) {
    $rtn_msg          = 'ok';
    $f_s_num          = $v_s_num;
    $vdata['d_empno'] = $_SESSION['acc_s_num'];
    $vdata['d_date']  = date('Y-m-d H:i:s');
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_language'); // 語系
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($vtbl_name, $vdata)) {
      $rtn_msg = $this->lang->line('language_save_del_err');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: Tony
  //  設計日期: 2017/7/21
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_kind         = $_POST['f_kind'];
    $f_s_num        = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_language'); // 語系
    $vdata['is_available'] = $f_is_available;
    $vdata['e_empno']      = $_SESSION['acc_s_num'];
    $vdata['e_date']       = date('Y-m-d H:i:s');
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($vtbl_name, $vdata)) {
      $rtn_msg = $this->lang->line('language_save_'.$f_kind.'_err');
    }
    echo $rtn_msg;
    return;
  }
}
?>