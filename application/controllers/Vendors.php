<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Vendors extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Vendors';

		$this->load->model('model_vendors');
	}
	

	/* 
    * It only redirects to the manage stores page
    */
	public function index()
	{
		if(!in_array('viewVendor', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$this->render_template('vendors/index', $this->data);	
	}

	/*
	* It retrieve the specific store information via a store id
	* and returns the data in json format.
	*/
	public function fetchVendorsDataById($id) 
	{
		if($id) {
			$data = $this->model_vendors->getVendorsData($id);
			echo json_encode($data);
		}
	}

	/*
	* It retrieves all the Vendor data from the database 
	* This function is called from the datatable ajax function
	* The data is return based on the json format.
	*/
	public function fetchVendorsData()
	{
		$result = array('data' => array());

		$data = $this->model_vendors->getVendorsData();

		foreach ($data as $key => $value) {

			// button
			$buttons = '';

			if(in_array('updateVendor', $this->permission)) {
				$buttons = '<button type="button" class="btn btn-default" onclick="editFunc('.$value['id'].')" data-toggle="modal" data-target="#editModal"><i class="fa fa-pencil"></i></button>';
			}

			if(in_array('deleteVendor', $this->permission)) {
				$buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
			}

			$status = ($value['active'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

			$result['data'][$key] = array(
				$value['name'],
				$value['address'],
				$value['phone'],
				$status,
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}

	/*
    * If the validation is not valid, then it provides the validation error on the json format
    * If the validation for each input is valid then it inserts the data into the database and 
    returns the appropriate message in the json format.
    */
	public function create()
	{
		if(!in_array('createVendor', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$response = array();

		$this->form_validation->set_rules('vendor_name', 'Vendor name', 'trim|required');
		$this->form_validation->set_rules('vendor_address', 'Vendor address', 'trim|required');
		$this->form_validation->set_rules('vendor_phone', 'Vendor phone', 'trim|required');
		$this->form_validation->set_rules('active', 'Active', 'trim|required');

		$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

        if ($this->form_validation->run() == TRUE) {
        	$data = array(
        		'name' => $this->input->post('vendor_name'),
				'address' => $this->input->post('vendor_address'),
				'phone' => $this->input->post('vendor_phone'),
        		'active' => $this->input->post('active'),	
        	);

        	$create = $this->model_vendors->create($data);
        	if($create == true) {
        		$response['success'] = true;
        		$response['messages'] = 'Succesfully created';
        	}
        	else {
        		$response['success'] = false;
        		$response['messages'] = 'Error in the database while creating the brand information';			
        	}
        }
        else {
        	$response['success'] = false;
        	foreach ($_POST as $key => $value) {
        		$response['messages'][$key] = form_error($key);
        	}
        }

        echo json_encode($response);
	}	

	/*
    * If the validation is not valid, then it provides the validation error on the json format
    * If the validation for each input is valid then it updates the data into the database and 
    returns a n appropriate message in the json format.
    */
	public function update($id)
	{
		if(!in_array('updateVendor', $this->permission)) {
			redirect('dashboard', 'refresh');
		}

		$response = array();

		if($id) {
			$this->form_validation->set_rules('edit_vendor_name', 'Vendor name', 'trim|required');
			$this->form_validation->set_rules('edit_vendor_address', 'Vendor address', 'trim|required');
			$this->form_validation->set_rules('edit_vendor_phone', 'Vendor phone', 'trim|required');
			$this->form_validation->set_rules('edit_active', 'Active', 'trim|required');

			$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

	        if ($this->form_validation->run() == TRUE) {
	        	$data = array(
	        		'name' => $this->input->post('edit_vendor_name'),
					'address' => $this->input->post('edit_vendor_address'),
					'phone' => $this->input->post('edit_vendor_phone'),
	        		'active' => $this->input->post('edit_active'),	
	        	);

	        	$update = $this->model_vendors->update($data, $id);
	        	if($update == true) {
	        		$response['success'] = true;
	        		$response['messages'] = 'Succesfully updated';
	        	}
	        	else {
	        		$response['success'] = false;
	        		$response['messages'] = 'Error in the database while updated the vendor information';			
	        	}
	        }
	        else {
	        	$response['success'] = false;
	        	foreach ($_POST as $key => $value) {
	        		$response['messages'][$key] = form_error($key);
	        	}
	        }
		}
		else {
			$response['success'] = false;
    		$response['messages'] = 'Error please refresh the page again!!';
		}

		echo json_encode($response);
	}

	/*
	* If checks if the store id is provided on the function, if not then an appropriate message 
	is return on the json format
    * If the validation is valid then it removes the data into the database and returns an appropriate 
    message in the json format.
    */
	public function remove()
	{
		if(!in_array('deleteVendor', $this->permission)) {
			redirect('dashboard', 'refresh');
		}
		
		$vendor_id = $this->input->post('vendor_id');

		$response = array();
		if($vendor_id) {
			$delete = $this->model_vendors->remove($vendor_id);
			if($delete == true) {
				$response['success'] = true;
				$response['messages'] = "Successfully removed";	
			}
			else {
				$response['success'] = false;
				$response['messages'] = "Error in the database while removing the vendor information";
			}
		}
		else {
			$response['success'] = false;
			$response['messages'] = "Refersh the page again!!";
		}

		echo json_encode($response);
	}

}