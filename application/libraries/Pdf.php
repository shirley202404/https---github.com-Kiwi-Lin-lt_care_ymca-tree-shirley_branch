<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH."libraries/tcpdf/tcpdf.php");
require_once(APPPATH."libraries/tcpdf/config/tcpdf_config.php");
class Pdf extends TCPDF { 
  function __construct() {
	  parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	  $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	  // set image scale factor
	  $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/tcpdf/lang/zho.php')) {
	      require_once(dirname(__FILE__).'/tcpdf/lang/zho.php'); // 中文語系
	      $this->setLanguageArray($l);
	  }

	  // ---------------------------------------------------------

	  // set font
	  $this->SetFont('msungstdlight', '', 10);
	}
}
?>