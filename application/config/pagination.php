<?php
  defined('BASEPATH') OR exit('No direct script access allowed');
  //$config['uri_segment'] = 4; // 頁次位置
  //$config['display_pages'] = FALSE; // 隱藏頁數,只顯示上一頁,下一頁
  $config['num_links'] = 5; // 目前所在頁數前面跟後面所顯示的分頁數量
  //$config['attributes'] = array('class' => 'page-link'); // a href 增加 class
  $config['use_page_numbers'] = TRUE;
  //$config['page_query_string'] = TRUE;

  // Bootstrap 3
  //$config['full_tag_open']  = '<li class="page-item"><a class="page-link" href="#">'; // 分頁起始
  //$config['full_tag_close'] = '</a></li>'; // 分頁結束

  //$config['num_tag_open'] = '<li class="page-item"><a class="page-link" href="#">'; // 分頁起始
  //$config['num_tag_close'] = '</a></li>'; // 分頁結束

  //$config['num_tag_open'] = '<li class="page-item">'; // 分頁起始
  //$config['num_tag_close'] = '</li>'; // 分頁結束
  //
  //$config['cur_tag_open']   = '<li class="page-item active"><a href="#" class="page-link">'; // 目前頁次樣式
  //$config['cur_tag_close']  = '</a></li>'; // 目前頁次结束樣式
  //
  //$config['first_link'] = '首頁';
  //$config['first_tag_open'] = '<li class="page-item">';
  //$config['first_tag_close'] = '</li>';
  //
  //$config['last_link']  = '末頁';
  //$config['last_tag_open'] = '<li class="page-item">';
  //$config['last_tag_close'] = '</li>';
  //
  //$config['next_link']  = '下頁';
  //$config['next_tag_open'] = '<li class="page-item">';
  //$config['next_tag_close'] = '</li>';
  //
  //$config['prev_link']  = '上頁';
  //$config['prev_tag_open'] = '<li class="page-item">';
  //$config['prev_tag_close'] = '</li>';


    $config['full_tag_open']    = '<div class="pagging text-center"><nav><ul class="pagination">';
    $config['full_tag_close']   = '</ul></nav></div>';
    
    $config['num_tag_open']     = '<li class="page-item"><span class="page-link">'; // 分頁起始
    $config['num_tag_close']    = '</span></li>'; // 分頁結束
    
    $config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">'; // 目前頁次樣式
    $config['cur_tag_close']    = '<span class="sr-only">(current)</span></span></li>'; // 目前頁次结束樣式
    
    $config['next_link']  = '下頁';
    $config['next_tag_open']    = '<li class="page-item"><span class="page-link">';
    $config['next_tag_close']  = '<span aria-hidden="true"></span></span></li>';
    
    $config['prev_link']  = '上頁';
    $config['prev_tag_open']    = '<li class="page-item"><span class="page-link">';
    $config['prev_tag_close']  = '</span></li>';
    
    $config['first_link'] = '首頁';
    $config['first_tag_open']   = '<li class="page-item"><span class="page-link">';
    $config['first_tag_close'] = '</span></li>';
    
    $config['last_link']  = '末頁';
    $config['last_tag_open']    = '<li class="page-item"><span class="page-link">';
    $config['last_tag_close']  = '</span></li>';
?>