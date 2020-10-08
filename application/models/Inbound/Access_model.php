<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Access_model extends CI_Model {

  	function check_header(){
 		$array_header = array("channelhospital");
 		if ( isset($_SERVER['HTTP_CHANNEL'])){
 			if ( in_array($_SERVER['HTTP_CHANNEL'], $array_header)){
 				return true;
 			}
 		}

 		return false;
  	}

}
