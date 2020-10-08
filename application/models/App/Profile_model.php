<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile_model extends CI_Model {

  	function detail_profile(){
 		$patien_profile_id = $this->input->post("profile_id");
 		$query_profile = "SELECT * FROM patient_profile as pp
 			JOIN patient_login as pl on pp.patient_login_id = pl.id
 			WHERE 1 
 				AND pp.id = ? ";
 		$run_profile = $this->db->query($query_profile, array($patien_profile_id));
 		return $run_profile->result_array();
  	}

  	function visit_profile(){
  		$patien_profile_id = $this->input->post("profile_id");
  		$query_visit = "SELECT * FROM kunjungan WHERE 1 AND patient_id = ? ORDER BY id desc";
  		$run_visit = $this->db->query($query_visit, array($patien_profile_id));

  		return $run_visit->result_array();
  	}

}
