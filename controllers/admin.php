<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is a sample module for PyroCMS
 *
 * @author 		Jerel Unruh - PyroCMS Dev Team
 * @website		http://unruhdesigns.com
 * @package 	PyroCMS
 * @subpackage 	Sample Module
 */
class Admin extends Admin_Controller
{
	protected $section = 'items';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('wp_m');
		$this->load->library('form_validation');
		$this->lang->load('wp');

		// Set the validation rules
		$this->item_validation_rules = array(
			array(
				'field' => 'name',
				'label' => 'Name',
				'rules' => 'trim|max_length[100]|required'
			),
			array(
				'field' => 'slug',
				'label' => 'Slug',
				'rules' => 'trim|max_length[100]|required'
			)
		);

		// We'll set the partials and metadata here since they're used everywhere
		$this->template->append_metadata(js('admin.js', 'wp'))->append_metadata(css('admin.css', 'wp'));
	}

	/**
	 * List all items
	 */
	public function index() {
		
		// Build the view with sample/views/admin/items.php
		$this->template->title($this->module_details['name'])->build('admin/form');
		//die('debug');
		
		/*		
		// here we use MY_Model's get_all() method to fetch everything
		$items = $this->sample_m->get_all();

		// Build the view with sample/views/admin/items.php
		$this->data->items =& $items;
		$this->template->title($this->module_details['name'])
						->build('admin/items', $this->data);
						
						*/
	}
	
	public function upload() {
	
		$config['upload_path'] = 'uploads/default/wp';
		$config['allowed_types'] = 'xml';
		$config['max_size']	= '5000';
		$config['remove_spaces'] = true; 
		$config['overwrite'] = true;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			$this->session->set_flashdata('error', 'Your WordPress file has been uploaded!');
			redirect('admin/WordPressImport');
		} else {
			$data = $this->upload->data();
			$this->session->set_flashdata('success', 'Your WordPress file could not be uploaded!');
			redirect('admin/WordPressImport/parse/'.$data['file_name']);
		}
		
	}
	
	public function get_filtered_xml($file) {
	
		$xml = file_get_contents('uploads/default/wp/'.$file);
		$xml = str_replace('content:encoded','content',$xml);
		$xml = str_replace('excerpt:encoded','excerpt',$xml);
		$xml = str_replace('wp:','',$xml);
		$xml = simplexml_load_string($xml);
		
		$titles = $this->wp_import->has_duplicate_titles($xml);
		
		return $xml;
		
	}
	
	public function parse($file) {
				
		set_time_limit(0);
				
		// Defaults
		$comments = array();		
		
		// Get the XML from the uploaded file
		$xml = $this->get_filtered_xml($file);
		
		// Load the WpImport Library
		$this->load->library('wp_import');
		
		// Check for duplicate titles
		$titles = $this->wp_import->has_duplicate_titles($xml);
		if($titles) {
			foreach($titles as $title) {
				$pattern = '/[a-z]+/';
				$replacement = 'test';
				$output = preg_replace($pattern, $replacement, $xml);
			}
			//$this->data->items =& $titles;
			//$this->template->title($this->module_details['name'])->build('admin/resolve_duplicate_titles',$this->data);
			//return;
		}
		print_r($titles);		
		print_r($output);
		print_r($xml);
		die();
		
		// Import Categories
		$this->wp_import->categories($xml);
		
		// Import Tags
		$this->wp_import->tags($xml);
		
		// Import Posts
		$this->wp_import->posts($xml);
		
		// Import Comments
		$this->wp_import->comments($xml);
				
	}

}