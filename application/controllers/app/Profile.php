<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once('vendor/autoload.php');
use \Firebase\JWT\JWT;
header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,PUT,DELETE,POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");


class Profile extends CI_Controller {

	function __construct() {
	    parent::__construct();
	    $this->load->model('inbound/access_model','access');
	    $this->load->model('app/profile_model','profile');
	    $this->load->model('app/master_model','master');
	    $this->config->load('config');
	    header("Access-Control-Allow-Origin: *");

	    $auth = $this->access->check_header();
	    if ( $auth != true ){
	    	$data['code'] = "401";
	    	$data['message'] = "HEADER NOT ALLOWED";
	    	echo json_encode($data);
	    	exit;
	    }

	    if ( !(isset($_SERVER['HTTP_TOKEN']))) {
	    	$data['code'] = "401";
	    	$data['message'] = "HEADER TOKEN NOT AVAILABLE";
	    	echo json_encode($data);
	    	exit;
	    }
	}

	public function update_coordinate(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);

		$patient_profile_id = $edata->patient_profile_id;
		$longitude = $edata->longitude;
		$latitude = $edata->latitude;
		$access_token = $_SERVER['HTTP_TOKEN'];

		if ( $this->profile->check_token($access_token, $patient_profile_id) == false ){
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$array_update = array(
			"longitude" => $longitude,
			"latitude" => $latitude
		);
		$this->db->where("id", $patient_profile_id);
		$this->db->update("patient_profile",$array_update);
		$data['code'] = "200";
		$data['message'] = "Coordinate Has ben Update";

		echo json_encode($data);
	}

	public function detail_profile($profile_id){
		$profile_id = $profile_id;
		$profile = $this->profile->detail_profile($profile_id);
		$visit = $this->profile->visit_profile($profile_id);
		if ( count($profile) > 0 ){
			$array_profile['detail_profile'] = $profile[0];
		}

		$access_token = $_SERVER['HTTP_TOKEN'];
		if ( $this->profile->check_token($access_token, $profile_id) == false ){
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$array_profile['visit'] = array();
	
		echo json_encode($array_profile);
	}

	public function check_order($profile_id){
		$data['code'] = "200";
		$data['status_order'] = "AVAILABLE";
		$data['content'] = "Patient can order medicine";

		$profile_id = $profile_id;
		$access_token = $_SERVER['HTTP_TOKEN'];

		if ( $this->profile->check_token($access_token, $profile_id) == false ){
			http_response_code(401);
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$qry_check_order = "SELECT * FROM order_patient WHERE 1 AND patient_id = ? order by id DESC LIMIT 0,1";
		$run_check_order = $this->db->query($qry_check_order, array($profile_id));
		if ( $run_check_order->num_rows() > 0 ){
			$data_order = $run_check_order->result_array();
			if ( $data_order[0]['keluhan'] == 1 ){
				$now = strtotime(date("Y-m-d H:i:s"));
				$order_time = strtotime($data_order[0]['created_at']);
				$diff = ($now - $order_time) / (60 * 60 * 24);
				if ( $diff <= 7 ){
					$data['error_code'] = "200";
					$data['code'] = "LOCKED";
					$data['content'] = "Patient can not order medicine";
				}
			}
		}

		echo json_encode($data);
	}

	public function update_address(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);

		$profile_id = $edata->profile_id;
		$address = $edata->address;
		$access_token = $_SERVER['HTTP_TOKEN'];

		if ( $this->profile->check_token($access_token, $profile_id) == false ){
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$this->db->query("UPDATE patient_profile set address = '$address' WHERE 1 AND id = $profile_id ");

		$data['code'] = "200";
		$data['message'] = "Address has been updated";
		echo json_encode($data);
	}


	public function order(){
		$obj = file_get_contents('php://input');
		$edata = json_decode($obj);

		$profile_id = $edata->profile_id; 
		$keluhan = $edata->keluhanl
		$access_token = $_SERVER['HTTP_TOKEN'];

		if ( $this->profile->check_token($access_token, $profile_id) == false ){
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$array_insert = array(
			"patient_id" => $profile_id,
			"delivery_date" => date("Y-m-d H:i:s"),
			"created_at" => date("Y-m-d H:i:s"),
			"status" => 1,
			"keluhan" => $keluhan
		);
		$this->db->insert("order_patient", $array_insert);
		if ( $keluhan == 1 ){
			$data['code'] = "201";
			$data['message'] = "Queue has been canceled because any complain";
			echo json_encode($data);
			exit();
		}

		$data['code'] = "200";
		$data['message'] = "Queue has been registered";
		echo json_encode($data);
		
	}

	public function get_detail_order($profile_id, $order_id){
		$profile_id = $profile_id;
		$order_id = $order_id;
		$access_token = $_SERVER['HTTP_TOKEN'];
		$description = $this->config->item('status_order');
		$address = "";

		if ( $this->profile->check_token($access_token, $profile_id) == false ){
			$data['code'] = "401";
			$data['message'] = "INVALID TOKEN";
			echo json_encode($data);
			exit();
		}

		$check_order = "SELECT * FROM order_patient where 1 AND id = ? ";
		$run_order = $this->db->query($check_order, array($order_id));
		if ( $run_order->num_rows() <= 0 ){
			$data['code'] = "401";
			$data['message'] = "Order not Exist";
		}

		$check_profile = "SELECT * FROM patient_profile WHERE 1 AND ? ";
		$run_profile = $this->db->query($check_profile, array($profile_id));
		if ( $run_profile->num_rows() > 0 ){
			$res_order = $run_profile->result_array();
			$address = $res_order[0]['address'];
		}

		$res_order = $run_order->result_array();
		$array_data['id'] = $res_order[0]['id'];
		$array_data['date'] = $res_order[0]['created_at'];
		$array_data['status'] = $res_order[0]['status'];
		$array_data['description'] = $description[$res_order[0]['status']];
		$array_data['address'] = $address;
		$data['code'] = "200";
		$data['status'] = $array_data;
		echo json_encode($data);
	}

}

?>