<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

	function __construct() {
	    parent::__construct();
	    $this->load->model('inbound/access_model','access');
	    $this->load->model('app/profile_model','profile');
	    $this->load->model('app/master_model','master');

	    $auth = $this->access->check_header();
	    if ( $auth != true ){
	    	$data['error_code'] = "401";
	    	$data['message'] = "HEADER NOT ALLOWED";
	    	echo json_encode($data);
	    	exit;
	    }
	}

	public function update_coordinate(){
		$patient_profile_id = $this->input->post("patient_profile_id");
		$longitude = $this->input->post("longitude");
		$latitude = $this->input->post("latitude");

		$array_update = array(
			"longitude" => $longitude,
			"latitude" => $latitude
		);
		$this->db->where("id", $patient_profile_id);
		$this->db->update("patient_profile",$array_update);
		$data['status'] = "200";
		$data['message'] = "Coordinate Has ben Update";

		echo json_encode($data);
	}

	public function detail_profile(){
		$profile_id = $this->input->post("profile_id");
		$profile = $this->profile->detail_profile($profile_id);
		$visit = $this->profile->visit_profile($profile_id);

		if ( count($profile) > 0 ){
			$array_profile['detail_profile'] = $profile[0];
		}

		if ( count($visit) > 0 ){
			$array_profile['visit'] = $visit[0];
		}
		echo json_encode($array_profile);
	}

	public function order(){
		$profile_id = $this->input->post("profile_id");
		$delivery_date = date("Y-m-d", strtotime($this->input->post("delivery_date")));
		$visit = $this->profile->visit_profile($profile_id);
		$profile = $this->profile->detail_profile($profile_id);
		$doctor_id = "";
		$obat = "";
		if ( count($visit) > 0 ){
			$latest_visit = $visit[0];
			$doctor_id = $latest_visit['general'][0]['doctor_id'];
			$array_insert = array(
				"patient_id" => $profile_id,
				"delivery_date" => $delivery_date,
				"doctor_id" => $doctor_id,
				"created_at" => date("Y-m-d H:i:s"),
				"status" => 1
			);
			$this->db->insert("order_patient", $array_insert);
			$list_obat = $visit[0]['medicine_list']['medicine'];
			foreach ($list_obat as $key_obat => $value_obat) {
				$obat .=  $value_obat['id'].',';
			}
			$obat = trim($obat,",");
			$created_order = $this->master->create_visit( $profile_id, $profile[0]['no_medrec'], $doctor_id , $obat, $delivery_date );
		}

		$data['status'] = "200";
		$data['message'] = "Order has been created";
		$data['profile_id'] = $profile_id;
		echo json_encode($data);
	}
}

?>