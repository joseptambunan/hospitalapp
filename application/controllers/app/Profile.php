<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

	function __construct() {
	    parent::__construct();
	    $this->load->model('inbound/access_model','access');
	    $this->load->model('app/profile_model','profile');
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
		$array_profile['detail_profile'] = $profile[0];
		$array_profile['visit'] = $visit[0];
		echo json_encode($array_profile);
	}
}

?>