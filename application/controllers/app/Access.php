<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;

class Access extends CI_Controller {

	function __construct() {
		header('Content-type: application/json');
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET,PUT,DELETE,POST, OPTIONS");
		header("Access-Control-Allow-Headers: *");

	    parent::__construct();
	    $this->load->model('inbound/access_model','access');
	    $this->config->load('config');
	    $auth = $this->access->check_header();

	    if ( $auth != true ){
	    	header("HTTP/1.1 401");
	    	$data['code'] = "401";
	    	$data['message'] = "HEADER NOT ALLOWED";
	    	echo json_encode($data);
	    	exit;
	    }

	}

	public function login(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);
		$bpjs_number = $edata->bpjs_number;
		$enc_password = crypt($edata->password,'$6$rounds=5000$saltsalt$');
		$check_bpjs = "SELECT * FROM patient_login WHERE 1 AND ( no_bpjs = ? OR no_medrec = ? ) AND password = ?";
		$run_bpjs = $this->db->query($check_bpjs,array($bpjs_number, $bpjs_number,$enc_password));
		if ( $run_bpjs->num_rows() <= 0 ){
			header("HTTP/1.1 401");
			$data['code'] = "401";
	    	$data['message'] = "User not authorized";
	    	echo json_encode($data);
	    	exit;
		}

		$data_user = $run_bpjs->result_array();
		$patient_login_id = $data_user[0]['id'];
		$qry_patient_profile = "SELECT * FROM patient_profile WHERE patient_login_id = ? ";
		$run_patient_profile = $this->db->query($qry_patient_profile, array($patient_login_id));

		if ( $run_patient_profile->num_rows() <= 0 ){
			header("HTTP/1.1 401");
			$data['code'] = "401";
	    	$data['message'] = "User not found ";
	    	echo json_encode($data);
	    	exit;
		}

		$data_profile = $run_patient_profile->result_array();
		$secret_key = $this->config->item('secret_key');
        $token = array(
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => strtotime($data_user[0]['date_created']),
            "uid" => $data_profile[0]['patient_login_id'],
            "patient_login_id" => $data_profile[0]['patient_login_id'],
            "patient_profile_id" => $data_profile[0]['id'],
            "first_name" => $data_profile[0]['first_name'],
            "last_name" => $data_profile[0]['last_name'],
            "profile_pict" => $data_profile[0]['profile'],
            "mobile_number" => $data_profile[0]['mobile_number'],
            "address" => $data_profile[0]['address']
        );
        $access_token = JWT::encode($token, $secret_key);

		$result_array = $run_bpjs->result_array();
		$this->db->query("UPDATE patient_login set last_activity = now(), last_login = now(), remember_token = '$access_token' WHERE id = '$patient_login_id'");

		$data['code'] = "200";
		$data['message'] = "User authorized";
		$data['access_token'] = $access_token;
			
		echo json_encode($data);

	}

	public function register(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);

		$bpjs_number = $edata->bpjs_number;
		$medic_number = $edata->medic_number;
		$date_of_birth = date("Y-m-d", strtotime($edata->date_of_birth));
		$temp_password =  $edata->password;
		$password = crypt($temp_password,'$6$rounds=5000$saltsalt$');
		$mobile_number = $edata->mobile_number;
		$first_name = "";
		$last_name = "";
		$created_at = date("Y-m-d H:i:s");

		$check_bpjs = "SELECT * FROM patient_login WHERE 1 AND ( no_bpjs = ? OR no_medrec = ? ) AND dob = ? ";
		$run_bpjs = $this->db->query($check_bpjs, array($bpjs_number,$medic_number,$date_of_birth));
		if ( $run_bpjs->num_rows() <= 0 ){
			header("HTTP/1.1 401 BPJS and Medical Number not exist");
			$data['code'] = "401";
			$data['message'] = "BPJS and Medical Number not exist";
			echo json_encode($data);
			exit;
		}else{
			$array_data = $run_bpjs->result_array();
			$first_name = $array_data[0]['first_name'];
			$last_name = $array_data[0]['last_name'];
		}

		$result_bpjs = $run_bpjs->result_array();
		$patient_login_id = $result_bpjs[0]['id'];
		$check_profile = "SELECT * FROM patient_profile WHERE 1 AND patient_login_id = ? ";
		$run_profile = $this->db->query($check_profile,array($patient_login_id));
		if ( $run_profile->num_rows() > 0 ){
			header("HTTP/1.1 401");
			$data['code'] = "401";
			$data['message'] = "Profile is exist";
			echo json_encode($data);
			exit;
		}

		$array_insert = array(
			"patient_login_id" => $patient_login_id,
			"first_name" => $first_name,
			"last_name" => $last_name,
			"mobile_number" => $mobile_number,
			"dob" => $date_of_birth,
			"created_at" => date("Y-m-d H:i:s")
		);
		$this->db->insert("patient_profile", $array_insert);
		$patient_profile_id = $this->db->insert_id();

		$secret_key = $this->config->item('secret_key');
        $token = array(
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => strtotime($created_at),
            "uid" => $patient_profile_id
        );
        $access_token = JWT::encode($token, $secret_key);
    
		$this->db->query("UPDATE patient_login set last_activity = now(), date_created = '$created_at', password = '$password', last_login = now(), remember_token = '$access_token' WHERE id = '$patient_login_id'");


		$data['code'] = "200";
		$data['message'] = "Data User Success";
		$data['token'] = $access_token;
		echo json_encode($data);
	}

	public function logout(){
		session_destroy();
		
		$data['status'] = "200";
		$data['message'] = "Logout Success";
	}

	public function forgot_password(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);
		$bpjs_number = $edata->bpjs_number;
		$mobile_number = $edata->mobile_number;

		$check_bpjs = "SELECT 
		pl.id as patient_login_id, 
		pp.id as patient_profile_id,
		pl.no_bpjs as bpjs_number, 
		pl.no_medrec as medic_number, 
		pp.mobile_number as mobile_number, 
		pp.address as address,
		pl.date_created as date_created,
		pl.first_name as first_name,
		pl.last_name as last_name
		FROM patient_login as pl 
			INNER JOIN patient_profile as pp ON (pl.id = pp.patient_login_id ) 
			WHERE 1 AND ( pl.no_bpjs = ? OR pl.no_medrec = ? ) AND pp.mobile_number = ? ";

		$run_bpjs = $this->db->query($check_bpjs, array($bpjs_number,$bpjs_number,$mobile_number));
		if ( $run_bpjs->num_rows() <= 0 ){
			header("HTTP/1.1 401");
			$data['code'] = "401";
			$data['message'] = "BPJS and Medical Number not exist";
			echo json_encode($data);
			exit;
		}
		$data_profile = $run_bpjs->result_array();
		$secret_key = $this->config->item('secret_key');
        $token = array(
            "iss" => $_SERVER['SERVER_NAME'],
            "iat" => strtotime($data_profile[0]['date_created']),
            "patient_login_id" => $data_profile[0]['patient_login_id']
        );
        $access_token = JWT::encode($token, $secret_key);

		$data['code'] = "200";
		$data['message'] = "User allow to change password";
		$data['token'] = $access_token;
		echo json_encode($data);
	}

	public function change_password(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);

		if ( !(isset($edata->token))) {

			header("HTTP/1.1 401");
			$data['code'] = "401";
			$data['message'] = "Invalid Header";
			echo json_encode($data);
			exit;
		}

		$token = $edata->token;
		$password = $edata->password;
		$secret_key = $this->config->item('secret_key');
		$decoded = JWT::decode($token, $secret_key, array('HS256'));
		if ( isset($decoded->patient_login_id) ){
			$id = $decoded->patient_login_id;
			$temp_password =  $edata->password;
			$password = crypt($temp_password,'$6$rounds=5000$saltsalt$');
			$this->db->query("UPDATE patient_login set password = '$password' where id = '$id'");
		}

		$data['code'] = "200";
		$data['message'] = "Success to change password";
		echo json_encode($data);
		

	}

}
?>