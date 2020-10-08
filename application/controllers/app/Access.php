<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access extends CI_Controller {

	function __construct() {
	    parent::__construct();
	    $this->load->model('inbound/access_model','access');
	    $auth = $this->access->check_header();
	    if ( $auth != true ){
	    	$data['error_code'] = "401";
	    	$data['message'] = "HEADER NOT ALLOWED";
	    	echo json_encode($data);
	    	exit;
	    }
	}

	public function login(){
		$bpjs_number = $this->input->post("bpjs_number");
		$password = $this->input->post("password");

		$enc_password = crypt($password,'$6$rounds=5000$saltforh0sp1tal$');
		$check_bpjs = "SELECT * FROM patient_login WHERE 1 AND no_bpjs = ? OR no_medrec = ? AND password = ?";
		$run_bpjs = $this->db->query($check_bpjs,array($bpjs_number, $bpjs_number,$enc_password));
		if ( $run_bpjs->num_rows() <= 0 ){
			$data['error_code'] = "401";
	    	$data['message'] = "User not authorized";
	    	echo json_encode($data);
	    	exit;
		}

		$data['error_code'] = "200";
		$data['message'] = "Success authorized";
		$data['token'] = "";
		echo json_encode($data);

	}

	public function register(){
		$bpjs_number = $this->input->post("bpjs_number");
		$medic_number = $this->input->post("medic_number");
		$full_name = $this->input->post("full_name");
		$date_of_birth = date("Y-m-d", strtotime($this->input->post("date_of_birth")));
		$gender = $this->input->post("gender");
		$address = $this->input->post("address");
		$separate_name = explode(" ",$full_name);
		$first_name = $full_name;
		$last_name = "";

		if ( count($separate_name) > 0 ){
			$first_name = $separate_name[0];
			$last_name = str_replace($first_name, "", $full_name);
		}

		$check_bpjs = "SELECT * FROM patient_login WHERE 1 AND no_bpjs = ? AND no_medrec = ? ";
		$run_bpjs = $this->db->query($check_bpjs, array($bpjs_number,$medic_number));
		if ( $run_bpjs->num_rows() < 0 ){
			$data['error_code'] = "401";
			$data['error_message'] = "BPJS and Medical Number not exist";
			echo json_encode($data);
			exit;
		}

		$result_bpjs = $run_bpjs->result_array();
		$patient_login_id = $result_bpjs[0]['id'];
		
		$check_profile = "SELECT * FROM patient_profile WHERE 1 AND patient_login_id = ? ";
		$run_profile = $this->db->query($check_profile,array($patient_login_id));
		if ( $run_profile->num_rows() > 0 ){
			$data['error_code'] = "401";
			$data['error_message'] = "Profile is exist";
			echo json_encode($data);
			exit;
		}

		$array_insert = array(
			"patient_login_id" => $patient_login_id,
			"first_name" => $first_name,
			"last_name" => $last_name,
			"mobile_number" => "",
			"address" => $address,
			"dob" => $date_of_birth,
			"gender" => $gender,
			"created_at" => date("Y-m-d H:i:s")
		);
		$this->db->insert("patient_profile", $array_insert);
		$patient_profile_id = $this->db->insert_id();

		$this->db->query("UPDATE patient_login set last_activity = now(), date_created = now() WHERE id = '$patient_login_id'");

		$data['error_code'] = "200";
		$data['error_message'] = "Data User Success";
		$data['patiend_id'] = $patient_profile_id;
		echo json_encode($data);
	}

}
?>