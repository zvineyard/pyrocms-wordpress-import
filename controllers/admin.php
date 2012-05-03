<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is a sample module for PyroCMS
 *
 * @author 		Zac Vineyard
 * @website		http://acvineyard.com
 * @package 	PyroCMS
 * @subpackage 	WordPress Import Module
 */
class Admin extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->lang->load('wp');

		// We'll set the partials and metadata here since they're used everywhere
		$this->template->append_js('module:admin.js'));
		$this->template->append_css('module:admin.css'));
	}

	public function index() {
	
		//print_r($this->module_details);
		//die();
		
		$this->template->title($this->module_details['name'])->build('admin/form');

	}
	
	public function upload() {
	
		$config['upload_path'] = 'uploads/'.SITE_REF.'/wp';
		$config['allowed_types'] = 'xml';
		$config['max_size']	= '5000';
		$config['remove_spaces'] = true; 
		$config['overwrite'] = true;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			$this->session->set_flashdata('error', 'Your WordPress file could not be uploaded uploaded!');
			redirect('admin/'.$this->module_details['slug']);
		} else {
			$data = $this->upload->data();
			$this->session->set_flashdata('success', 'Your WordPress file was uploaded!');
			redirect('admin/'.$this->module_details['slug'].'/parse/'.$data['file_name']);
		}
		
	}
	
	public function get_filtered_xml($file) {
	
		$xml = file_get_contents('uploads/default/wp/'.$file);
		$xml = str_replace('content:encoded','content',$xml);
		$xml = str_replace('excerpt:encoded','excerpt',$xml);
		$xml = str_replace('wp:','',$xml);		
		$xml = simplexml_load_string($xml);
		return $xml;
		
	}
	
	public function parse($file) {
					
		set_time_limit(0);
				
		// Defaults
		$comments = array();		
		
		// Get the XML from the uploaded file
		$xml = $this->get_filtered_xml($file);
		
		// Load the wp_import Library
		$this->load->library('wp_import');
		
		// Check for duplicate post titles
		$titles = $this->wp_import->has_duplicate_titles($xml);
		if($titles) {
			$this->data->items =& $titles;
			$this->template->title($this->module_details['name'])->build('admin/duplicates',$this->data);
			return;
		}
		
		// Import Categories
		$this->wp_import->categories($xml);
		
		// Import Tags
		$this->wp_import->tags($xml);
		
		// Import Posts
		$this->wp_import->posts($xml);
		
		// Import Comments
		$this->wp_import->comments($xml);
		
		// Import Users
		$this->wp_import->users($xml); // Currently only imports users who aren't already in the system
		
		// Import Pages
		// Still needed $this->wp_import->pages($xml);
				
	}

}