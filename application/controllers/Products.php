<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Admin_Controller 
{
	public function __construct()
	{
		parent::__construct();

		$this->not_logged_in();

		$this->data['page_title'] = 'Products';

		$this->load->model('model_products');
		$this->load->model('model_brands');
		$this->load->model('model_category');
		$this->load->model('model_attributes');
        $this->load->model('model_vendors');
	}

    /* 
    * It only redirects to the manage product page
    */
	public function index()
	{
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->render_template('products/index', $this->data);	
	}
    public function soldProducts()
	{
        if(!in_array('viewProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->render_template('products/sold_products', $this->data);	
	}

    /*
    * It Fetches the products data from the product table 
    * this function is called from the datatable ajax function
    */
	public function fetchProductData()
	{
		$result = array('data' => array());

		$data = $this->model_products->getProductData();

		foreach ($data as $key => $value) {

            $vendor_data = $this->model_vendors->getVendorsData($value['vendor_id']);
            $brand_data = $this->model_brands->getBrandData($value['brand_id']);
			// button
            $buttons = '';
            if(in_array('updateProduct', $this->permission)) {
    			$buttons .= '<a href="'.base_url('products/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            }

            if(in_array('deleteProduct', $this->permission)) { 
    			$buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            }
			

			$img = '<img src="'.base_url($value['image']).'" alt="'.$value['name'].'" class="img-circle" width="50" height="50" />';

            $availability = ($value['availability'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            $qty_status = '';
            if($value['qty'] == 0) {
                $qty_status = '<span class="label label-danger">Out of stock !</span>';
            } else{
                $qty_status = '<span class="label label-success">Available !</span>';
            }


			$result['data'][$key] = array(
				
                $value['bill_no'],
                $brand_data['name'],
				$value['name'],
                $value['sku'],
                $value['model'],
				$value['cost_price'],
                $value['qty'] . ' ' . $qty_status,
                $vendor_data['name'],
				$value['date_time'],
				$buttons
			);
		} // /foreach

		echo json_encode($result);
	}
    public function fetchSoldProductData()
	{
		$result = array('data' => array());

		$data = $this->model_products->getSoldProductData();
        

		foreach ($data as $key => $value) {

            // $vendor_data = $this->model_vendors->getVendorsData($value['vendor_id']);
			// // button
            // $buttons = '';
            // if(in_array('updateProduct', $this->permission)) {
    		// 	$buttons .= '<a href="'.base_url('products/update/'.$value['id']).'" class="btn btn-default"><i class="fa fa-pencil"></i></a>';
            // }

            // if(in_array('deleteProduct', $this->permission)) { 
    		// 	$buttons .= ' <button type="button" class="btn btn-default" onclick="removeFunc('.$value['id'].')" data-toggle="modal" data-target="#removeModal"><i class="fa fa-trash"></i></button>';
            // }
			

			// $img = '<img src="'.base_url($value['image']).'" alt="'.$value['name'].'" class="img-circle" width="50" height="50" />';

            // $availability = ($value['availability'] == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';

            // $qty_status = '';
            // if($value['qty'] <= 10) {
            //     $qty_status = '<span class="label label-warning">Low !</span>';
            // } else if($value['qty'] <= 0) {
            //     $qty_status = '<span class="label label-danger">Out of stock !</span>';
            // }


			$result['data'][$key] = array(
				
				
                $value['name'],
                $value['sku'],
                $value['model'],
				$value['sum(orders_item.qty)'],
                $value['rate'],
                $value['sum(orders_item.qty)'] * $value['rate'],
                ($value['sum(orders_item.qty)'] * $value['rate']) - ($value['cost_price'] * $value['sum(orders_item.qty)']),
                
				// $availability,
				// $buttons
			);
		} // /foreach

		echo json_encode($result);
	}	

    /*
    * If the validation is not valid, then it redirects to the create page.
    * If the validation for each input field is valid then it inserts the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
	public function create()
	{
		if(!in_array('createProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

		$this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
		$this->form_validation->set_rules('sku', 'Code', 'trim|required');
        $this->form_validation->set_rules('date_time', 'Date', 'trim|required');
		$this->form_validation->set_rules('price', 'Selling Price', 'trim|required');
        $this->form_validation->set_rules('cost_price', 'Cost Price', 'trim|required');
        $this->form_validation->set_rules('model', 'Model', 'trim|required');
        $this->form_validation->set_rules('bill_no', 'Bill number', 'trim|required');
		$this->form_validation->set_rules('qty', 'Qty', 'trim|required');
        $this->form_validation->set_rules('vendor', 'Vendor', 'trim|required');
		
	
        if ($this->form_validation->run() == TRUE) {
            // true case
        	$upload_image = $this->upload_image();

        	$data = array(
        		'name' => $this->input->post('product_name'),
        		'sku' => $this->input->post('sku'),
        		'price' => $this->input->post('price'),
                'cost_price' => $this->input->post('cost_price'),
        		'qty' => $this->input->post('qty'),
                'date_time' => $this->input->post('date_time'),
        		'image' => $upload_image,
        		'description' => $this->input->post('description'),
        		'attribute_value_id' => json_encode($this->input->post('attributes_value_id')),
        		'brand_id' => $this->input->post('brand'),
        		'category_id' => json_encode($this->input->post('category')),
                'model' => $this->input->post('model'),
                'bill_no' => $this->input->post('bill_no'),
                'vendor_id' => $this->input->post('vendor'),
        		
        	);

        	$create = $this->model_products->create($data);
        	if($create == true) {
        		$this->session->set_flashdata('success', 'Successfully created');
        		redirect('products/', 'refresh');
        	}
        	else {
        		$this->session->set_flashdata('errors', 'Error occurred!!');
        		redirect('products/create', 'refresh');
        	}
        }
        else {
            // false case

        	// attributes 
        	$attribute_data = $this->model_attributes->getActiveAttributeData();

        	$attributes_final_data = array();
        	foreach ($attribute_data as $k => $v) {
        		$attributes_final_data[$k]['attribute_data'] = $v;

        		$value = $this->model_attributes->getAttributeValueData($v['id']);

        		$attributes_final_data[$k]['attribute_value'] = $value;
        	}

        	$this->data['attributes'] = $attributes_final_data;
			$this->data['brands'] = $this->model_brands->getActiveBrands();        	
			$this->data['category'] = $this->model_category->getActiveCategroy();        	
            $this->data['vendors'] = $this->model_vendors->getActiveVendor();      	

            $this->render_template('products/create', $this->data);
        }	
	}

    /*
    * This function is invoked from another function to upload the image into the assets folder
    * and returns the image path
    */
	public function upload_image()
    {
    	// assets/images/product_image
        $config['upload_path'] = 'assets/images/product_image';
        $config['file_name'] =  uniqid();
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '1000';

        // $config['max_width']  = '1024';s
        // $config['max_height']  = '768';

        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('product_image'))
        {
            $error = $this->upload->display_errors();
            return $error;
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            $type = explode('.', $_FILES['product_image']['name']);
            $type = $type[count($type) - 1];
            
            $path = $config['upload_path'].'/'.$config['file_name'].'.'.$type;
            return ($data == true) ? $path : false;            
        }
    }

    /*
    * If the validation is not valid, then it redirects to the edit product page 
    * If the validation is successfully then it updates the data into the database 
    * and it stores the operation message into the session flashdata and display on the manage product page
    */
	public function update($product_id)
	{      
        if(!in_array('updateProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }

        if(!$product_id) {
            redirect('dashboard', 'refresh');
        }

        $this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
        $this->form_validation->set_rules('sku', 'Code', 'trim|required');
        $this->form_validation->set_rules('price', 'Selling Price', 'trim|required');
        $this->form_validation->set_rules('cost_price', 'Cost Price', 'trim|required');
        $this->form_validation->set_rules('date_time', 'Date', 'trim|required');
        $this->form_validation->set_rules('qty', 'Qty', 'trim|required');
        $this->form_validation->set_rules('model', 'Model', 'trim|required');
        $this->form_validation->set_rules('bill_no', 'Bill number', 'trim|required');
        $this->form_validation->set_rules('vendor', 'Vendor', 'trim|required');

        if ($this->form_validation->run() == TRUE) {
            // true case
            
            $data = array(
                'name' => $this->input->post('product_name'),
                'sku' => $this->input->post('sku'),
                'price' => $this->input->post('price'),
                'cost_price' => $this->input->post('cost_price'),
                'qty' => $this->input->post('qty'),
                'date_time' => $this->input->post('date_time'),
                'description' => $this->input->post('description'),
                'attribute_value_id' => json_encode($this->input->post('attributes_value_id')),
                'brand_id' => $this->input->post('brand'),
                'category_id' => json_encode($this->input->post('category')),
                'model' => $this->input->post('model'),
                'vendor_id' => $this->input->post('vendor'),
                'bill_no' => $this->input->post('bill_no'),
                
            );

            
            if($_FILES['product_image']['size'] > 0) {
                $upload_image = $this->upload_image();
                $upload_image = array('image' => $upload_image);
                
                $this->model_products->update($upload_image, $product_id);
            }

            $update = $this->model_products->update($data, $product_id);
            if($update == true) {
                $this->session->set_flashdata('success', 'Successfully updated');
                redirect('products/', 'refresh');
            }
            else {
                $this->session->set_flashdata('errors', 'Error occurred!!');
                redirect('products/update/'.$product_id, 'refresh');
            }
        }
        else {
            // attributes 
            $attribute_data = $this->model_attributes->getActiveAttributeData();

            $attributes_final_data = array();
            foreach ($attribute_data as $k => $v) {
                $attributes_final_data[$k]['attribute_data'] = $v;

                $value = $this->model_attributes->getAttributeValueData($v['id']);

                $attributes_final_data[$k]['attribute_value'] = $value;
            }
            
            // false case
            $this->data['attributes'] = $attributes_final_data;
            $this->data['brands'] = $this->model_brands->getActiveBrands();         
            $this->data['category'] = $this->model_category->getActiveCategroy();           
            $this->data['vendors'] = $this->model_vendors->getActiveVendor();          

            $product_data = $this->model_products->getProductData($product_id);
            $this->data['product_data'] = $product_data;
            $this->render_template('products/edit', $this->data); 
        }   
	}

    /*
    * It removes the data from the database
    * and it returns the response into the json format
    */
	public function remove()
	{
        if(!in_array('deleteProduct', $this->permission)) {
            redirect('dashboard', 'refresh');
        }
        
        $product_id = $this->input->post('product_id');

        $response = array();
        if($product_id) {
            $delete = $this->model_products->remove($product_id);
            if($delete == true) {
                $response['success'] = true;
                $response['messages'] = "Successfully removed"; 
            }
            else {
                $response['success'] = false;
                $response['messages'] = "Error in the database while removing the product information";
            }
        }
        else {
            $response['success'] = false;
            $response['messages'] = "Refersh the page again!!";
        }

        echo json_encode($response);
	}

}