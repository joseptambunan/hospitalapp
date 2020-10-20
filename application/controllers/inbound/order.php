<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller {

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

	public function visit(){
		$patient_id = $this->input->post("patient_id");
		$medical_number = $this->input->post("medical_number");
		$doctor_id = $this->input->post("doctor_id");
		$obat = $this->input->post("obat");
		$tanggal_kunjungan = date("Y-m-d", strtotime($this->input->post("visit_date")));
 		$create_visit = $this->master->create_visit( $patient_id, $medical_number, $doctor_id, $obat, $tanggal_kunjungan);

		$data['status'] = "200";
		$data['message'] = "Visit has been created";
		$data['profile_id'] = $patient_id;
		echo json_encode($data);
	}	

	public function update_order(){
		$order_id = $this->input->post("order_id");
		$status = $this->input->post("status");
		$this->db->query("UPDATE order_patient set status = '$status' where id = '$order_id'");
		$data['error_code'] = 0;
		$data['message'] = "order has been updated";
		echo json_encode($data);
	}
}
?>