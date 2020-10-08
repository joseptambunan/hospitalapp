<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Masterdata extends CI_Controller {

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

	public function index(){
		$this->load->view('welcome_message');
	}

	public function insertBpjs(){
		$bpjs_number = $this->input->post("bpjs_number");
		$medical_number = $this->input->post("medical_number");
		
		$data['status'] = "410";
		$data['message'] = "Request Not Found";
		
		if ( $bpjs_number != "" && $medical_number != "" ){

			$array_insert = array(
				"no_bpjs" => $bpjs_number,
				"no_medrec" => $medical_number
			);

			$this->db->insert("patient_login",$array_insert);
			$data['status'] = "200";
			$data['message'] = "Data Success Added";
		}
		
		echo json_encode($data);
	}

	public function insertDoctor(){
		$nik = $this->input->post("nik");
		$first_name = $this->input->post("first_name");
		$last_name = $this->input->post("last_name");
		$mobile_no = $this->input->post("mobile_no");
		$poli = $this->input->post("poli");
		$status = $this->input->post("status");

		$data['status'] = "410";
		$data['message'] = "Request Not Found";

		if ( $nik != "" && $first_name != "" && $last_name != "" && $poli != "" ){

			$array_insert = array(
				"nik" => $nik,
				"first_name" => $first_name,
				"last_name" => $last_name,
				"mobile_number" => $mobile_no,
				"poli" => $poli,
				"status" => 1,
				"created_at" => date("Y-m-d H:i:s")
			);

			$this->db->insert("master_doctor",$array_insert);
			$data['status'] = "200";
			$data['message'] = "Data Success Added";
		}
		
		echo json_encode($data);
	}

	public function insertMedicine(){
		$brand = $this->input->post("brand");
		$name = $this->input->post("name");
		$golongan = $this->input->post("golongan");
		$restricted = $this->input->post("restricted");
		$qty = $this->input->post("qty");
		$satuan = $this->input->post("satuan");
		$keterangan = $this->input->post("keterangan");

		$data['status'] = "410";
		$data['message'] = "Request Not Found";

		if ( $brand != "" && $name != "" && $golongan != "" && $restricted != "" && $qty != "" && $satuan != "" && $keterangan != "" ){
			$array_insert = array(
				"brand" => $brand,
				"name" => $name,
				"golongan" => $golongan,
				"restricted" => $restricted,
				"qty" => $qty,
				"satuan" => $satuan,
				'description' => $keterangan,
				"created_at" => date("Y-m-d H:i:s")
			);

			$this->db->insert("master_medicine",$array_insert);
			$data['status'] = "200";
			$data['message'] = "Data Success Added";
		}
		echo json_encode($data);
	}
}
