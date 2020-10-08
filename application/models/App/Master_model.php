<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Master_model extends CI_Model {

  	function create_visit(  $patient_id = "", $medical_number = "", $doctor_id = "", $obat = "", $tanggal_kunjungan = "" ){
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

		return true;
  	}

  	function list_medicine($visit_id){
  		$check_receipt = "SELECT rh.id as receipt_id FROM kunjungan kj
  			INNER JOIN receipt_header as rh ON rh.kunjungan_id = kj.id
  				WHERE 1 AND kj.id = ? ORDER BY kj.id desc";
  		$run_receipt = $this->db->query($check_receipt,array($visit_id));
  		$data_medicine = array();
  		if ( $run_receipt->num_rows() > 0 ){
  			$data_receipt = $run_receipt->result_array();
  			$receipt_id = $data_receipt[0]['receipt_id'];
  			$check_medicine = "SELECT * FROM receipt_detail WHERE 1 AND receipt_header_id = ?";
  			$run_medicine = $this->db->query($check_medicine, array($receipt_id));
  			if ( $run_medicine->num_rows() > 0 ){
  				foreach ($run_medicine->result_array() as $key_medicine => $value_medicine) {
  					$data_obat = $this->get_detail_medicine($value_medicine['obat']);
  					$data_medicine[$key_medicine]['id'] = $value_medicine['obat'];
  					$data_medicine[$key_medicine]['name'] = $data_obat[0]['name'];
  					$data_medicine[$key_medicine]['brand'] = $data_obat[0]['brand'];
  					$data_medicine[$key_medicine]['dosis'] = $value_medicine['dosis'];
  				}
  			}
  		}

  		$data['error_code'] = "200";
  		$data['medicine'] = $data_medicine;
  		return $data;
  	}

  	function get_detail_medicine( $medicine_id){
  		$check_medicine = "SELECT * FROM master_medicine WHERE 1 AND id = ? ";
  		$run_medicine = $this->db->query($check_medicine, array($medicine_id));
  		return $run_medicine->result_array();
  	}


  	
}
