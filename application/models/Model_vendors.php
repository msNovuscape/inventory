<?php 

class Model_vendors extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/* get the active store data */
	public function getActiveVendor()
	{
		$sql = "SELECT * FROM vendors WHERE active = ?";
		$query = $this->db->query($sql, array(1));
		return $query->result_array();
	}

	/* get the brand data */
	public function getVendorsData($id = null)
	{
		if($id) {
			$sql = "SELECT * FROM vendors where id = ?";
			$query = $this->db->query($sql, array($id));
			return $query->row_array();
		}

		$sql = "SELECT * FROM vendors";
		$query = $this->db->query($sql);
		return $query->result_array();
	}

	public function create($data)
	{
		if($data) {
			$insert = $this->db->insert('vendors', $data);
			return ($insert == true) ? true : false;
		}
	}

	public function update($data, $id)
	{
		if($data && $id) {
			$this->db->where('id', $id);
			$update = $this->db->update('vendors', $data);
			return ($update == true) ? true : false;
		}
	}

	public function remove($id)
	{
		if($id) {
			$this->db->where('id', $id);
			$delete = $this->db->delete('vendors');
			return ($delete == true) ? true : false;
		}
	}

	public function countTotalVendors()
	{
		$sql = "SELECT * FROM vendors WHERE active = ?";
		$query = $this->db->query($sql, array(1));
		return $query->num_rows();
	}

}