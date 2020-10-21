<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;

class Profile_model extends CI_Model {

  	function detail_profile($profile_id){
   		$patien_profile_id = $profile_id;
   		$query_profile = "SELECT * FROM patient_profile as pp
   			JOIN patient_login as pl on pp.patient_login_id = pl.id
   			WHERE 1 
   				AND pp.id = ? ";
   		$run_profile = $this->db->query($query_profile, array($patien_profile_id));
   		return $run_profile->result_array();
  	}

  	function visit_profile( $limit = 5){
	    $this->load->model('app/master_model','master');
  		$patien_profile_id = $this->input->post("profile_id");
  		$query_visit = "SELECT * FROM kunjungan WHERE 1 AND patient_id = ? ORDER BY id desc LIMIT 0, $limit ";
  		$run_visit = $this->db->query($query_visit, array($patien_profile_id));
  		$array_visit = array();
  		$receipt_id = "";
  		foreach ($run_visit->result_array() as $key => $value) {
  			$array_visit[$key]['general'] = $run_visit->result_array();
  			$receipt_list = $this->master->list_medicine($value['id']);
  			$array_visit[$key]['medicine_list'] = $receipt_list;
  		}


  		return $array_visit;
  	}

    function check_token($token, $profile_id = ""){
      $this->config->load('config');
      $check_token = "SELECT  * FROM patient_login WHERE remember_token = ? ";
      $run_token = $this->db->query($check_token, array($token));

      if ( $run_token->num_rows() > 0 ){
        $secret_key = $this->config->item('secret_key');
        $decoded = JWT::decode($token, $secret_key, array('HS256'));
        if ( isset($decoded->uid)){
          if ( $decoded->uid != $profile_id ){
            return false;
          }
          return true;
        }
      }
      return false;
    }
  	
}
?>