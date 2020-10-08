<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller {

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

	public function visit(){
		$patient_id = $this->input->post("patient_id");
		$medical_number = $this->input->post("medical_number");
		$doctor_id = $this->input->post("doctor_id");
		$obat = $this->input->post("obat");
		$tanggal_kunjungan = date("Y-m-d", strtotime($this->input->post("visit_date")));
 
		$array_insert = array(
			"patient_id" => $patient_id,
			"medical_number" => $medical_number,
			"doctor_id" => $doctor_id,
			"created_at" => date("Y-m-d H:i:s"),
			"tanggal_kunjungan" => $tanggal_kunjungan
		);
		$this->db->insert("kunjungan", $array_insert);
		$kunjungan_id = $this->db->insert_id();

		$array_insert_receipt = array(
			"kunjungan_id" => $kunjungan_id,
			"doctor_id" => $doctor_id,
			"created_at" => date("Y-m-d H:i:s")
		);
		$this->db->insert("receipt_header", $array_insert_receipt);
		$receipt_id = $this->db->insert_id();

		$list_obat = explode(",", $obat);
		foreach ($list_obat as $key => $value) {
			$array_insert_receipt_detail = array(
				"receipt_header_id" => $receipt_id,
				"obat" => $value,
				"dosis" => ( $key + 1 ) * 10
			);
			$this->db->insert("receipt_detail", $array_insert_receipt_detail);
		}

		$data['status'] = "200";
		$data['message'] = "Visit has been created";
		$data['profile_id'] = $patient_id;
		echo json_encode($data);
	}	

}
?>