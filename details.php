<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Wordpress_import extends Module
{
	public $version = '1.2.1';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'WordPress Import'
			),
			'description' => array(
				'en' => 'Import a WordPress site into PyroCMS.'
			),
			'frontend' => false,
			'backend' => true
		);
	}

	public function install()
	{
		// Adjust default_posts.slug column length to accept default wp slugs
		$sql = "ALTER TABLE  `".$this->db->dbprefix('blog')."` CHANGE  `slug`  `slug` VARCHAR( 200 ) CHARACTER SET utf8 NOT NULL";
		$this->db->query($sql);

		// Make sure the folder exists and is writable
		return is_dir($this->upload_path.'wp') or @mkdir($this->upload_path.'wp',0777,TRUE);
	}

	public function uninstall()
	{
		@rmdir($this->upload_path.'wp');
		return true;
	}

	public function upgrade($old_version)
	{
		return true;
	}

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
	}
}

/* End of file details.php */