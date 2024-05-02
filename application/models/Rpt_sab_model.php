<?php
class Rpt_sab_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
}
?>