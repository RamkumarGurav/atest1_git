<?php
class Role_manager_model extends CI_Model
{
	public $session_uid = '';
	public $session_name = '';
	public $session_email = '';

	function __construct()
	{
		$this->load->database();

		$this->model_data = array();


		$this->db->query("SET sql_mode = ''");


		$this->session_uid = $this->session->userdata('sess_current_uid');
		$this->session_name = $this->session->userdata('sess_current_name');
		$this->session_email = $this->session->userdata('sess_current_email');

	}

	function get_admin_user_role_data($params = array())
	{
		$result = ''; // Initialize result variable

		// Check if 'search_for' parameter is provided and not empty
		if (!empty($params['search_for'])) {
			// If searching for something specific, select the count of admin_user_role_id
			$this->db->select("count(ft.admin_user_role_id) as counts");
		} else {
			// Otherwise, select all columns from admin_user_role table
			$this->db->select("ft.* ");
			// Also, select the name of the admin_user who added the role
			$this->db->select("(select au.name from admin_user as au where au.admin_user_id = ft.added_by) as added_by_name ");
			// And the name of the admin_user who updated the role
			$this->db->select("(select au.name from admin_user as au where au.admin_user_id = ft.updated_by) as updated_by_name ");
		}

		// Set the from table to admin_user_role with alias ft
		$this->db->from("admin_user_role as ft");
		// Order the results by admin_user_role_id in descending order
		$this->db->order_by("admin_user_role_id desc");

		// Check if admin_user_role_id is provided in the params and not empty
		if (!empty($params['admin_user_role_id'])) {
			// Add a where clause to filter by admin_user_role_id
			$this->db->where("ft.admin_user_role_id", $params['admin_user_role_id']);
		}

		// Check if start_date is provided in the params and not empty
		if (!empty($params['start_date'])) {
			// Convert start_date to 'Y-m-d' format
			$temp_date = date('Y-m-d', strtotime($params['start_date']));
			// Add a where clause to filter records added on or after start_date
			$this->db->where("DATE_FORMAT(ft.added_on, '%Y%m%d') >= DATE_FORMAT('$temp_date', '%Y%m%d')");
		}

		// Check if end_date is provided in the params and not empty
		if (!empty($params['end_date'])) {
			// Convert end_date to 'Y-m-d' format
			$temp_date = date('Y-m-d', strtotime($params['end_date']));
			// Add a where clause to filter records added on or before end_date
			$this->db->where("DATE_FORMAT(ft.added_on, '%Y%m%d') <= DATE_FORMAT('$temp_date', '%Y%m%d')");
		}

		// Check if record_status is provided in the params and not empty
		if (!empty($params['record_status'])) {
			// If record_status is 'zero', filter records with status = 0
			if ($params['record_status'] == 'zero') {
				$this->db->where("ft.status = 0");
			} else {
				// Otherwise, filter records by admin_user_role_id with the given status
				$this->db->where("ft.admin_user_role_id", $params['record_status']);
			}
		}

		// Check if both field_value and field_name are provided in the params and not empty
		if (!empty($params['field_value']) && !empty($params['field_name'])) {
			// Add a where clause to filter records with field_name containing field_value
			$this->db->where("$params[field_name] like ('%$params[field_value]%')");
		}

		// Check if limit and offset are provided in the params and not empty
		if (!empty($params['limit']) && !empty($params['offset'])) {
			// Set the limit and offset for the query
			$this->db->limit($params['limit'], $params['offset']);
		} else if (!empty($params['limit'])) {
			// If only limit is provided, set the limit for the query
			$this->db->limit($params['limit']);
		}

		// Execute the query and get the result
		$query_get_list = $this->db->get();
		$result = $query_get_list->result();



		// Return the result
		return $result;


	}

}

?>