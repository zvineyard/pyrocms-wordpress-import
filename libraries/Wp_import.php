<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wp_Import {
		
	private $ci;
	
	function __construct()
	{
        $this->ci =& get_instance();
    }
	
	private function get_duplicates($array)
	{
		return array_unique(array_diff_assoc($array, array_unique($array)));
	}

	public function has_duplicate_titles($xml)
	{

		$titles = array();
		foreach ($xml->channel->item as $val)
		{
			if((string) $val->content != "" && (string) $val->post_type == "post" && (string) $val->status == 'publish')
			{
				$titles[] = (string) mb_convert_encoding($val->title,"HTML-ENTITIES", "UTF-8");
			}
		}
		$dups = $this->get_duplicates($titles);
		if(count($dups) > 0)
		{
			return $dups;
		}
		else
		{
			return false;
		}
		
	}
	
	public function categories($xml)
	{
		
		foreach ($xml->channel->category as $val)
		{
			$categories[] = array(
				'slug' => (string) $val->category_nicename,
				'title' => (string) $val->cat_name
			);
		}
		if(!empty($categories))
		{
			if($this->ci->db->insert_batch('default_blog_categories', $categories))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	
	}
	
	public function tags($xml)
	{
	
		foreach ($xml->channel->tag as $val)
		{
			$tags[] = array(
				'name' => (string) $val->tag_name
			);
		}
		if(!empty($tags))
		{
			if($this->ci->db->insert_batch('default_keywords', $tags))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	
	}
	
	public function posts($xml)
	{
		
		// Defaults
		$posts = array();
		
		foreach ($xml->channel->item as $val) {
		
			$slug = (string) $val->post_name;
						
			$comments_enabled = ($val->comment_status == 'open');
			
			$status = ($val->status === 'publish') ? 'draft' : 'live';
			
			// Get content, category, and tags for every post
			if((string) $val->content != "" && (string) $val->post_type == "post" && (string) $val->status == "publish")
			{
				
				// Get a category slug
				$category_slug = "";
				foreach($val->category as $cat)
				{
					$category_slug = "";
					if($cat[0]['domain'] == 'category')
					{
						$category_slug = (string) $cat[0]['nicename'];
						break;
					}
				}
				
				// Query the ID of this posts's category slug
				$category_id = 0;
				if($category_slug != "")
				{
					$this->ci->db->where('slug',$category_slug);
					$this->ci->db->limit(1);
					$query = $this->ci->db->get('default_blog_categories');
					if($query->num_rows() > 0)
					{
						foreach ($query->result() as $row)
						{
							$category_id = $row->id;
						}
					}
				}
				
				// Get tag slugs
				$tag_slugs = array();
				foreach($val->category as $tag)
				{
					if($tag[0]['domain'] == 'post_tag')
					{
						$tag_slugs[] = (string) $tag;
					}
				}
				
				// Assign a keyword hash to the post and insert tags
				$keywords_hash = "";
				if(count($tag_slugs) > 0)
				{
				
					$keywords_hash = md5(time()+rand(1,1000000));
				
					// Query tag IDs off the tag slugs and assign
					$this->ci->db->where('name',$tag_slugs[0]);
					foreach($tag_slugs as $v)
					{
						$this->ci->db->or_where('name',$v);
					}
					$query = $this->ci->db->get('default_keywords');
					$assign = array();
					if($query->num_rows() > 0)
					{
						foreach ($query->result() as $row)
						{
							$assign[] = array(
								'keyword_id' => $row->id,
								'hash' => $keywords_hash
							);
						}
					}
					// Insert tags					
					$this->ci->db->insert_batch('default_keywords_applied', $assign);
				
				}
				
				$posts[] = array(
					'title' => (string) $val->title,
					'slug' => $slug,
					'category_id' => $category_id,
					'intro' => (string) mb_convert_encoding($val->excerpt,"HTML-ENTITIES", "UTF-8"),
					'body' => nl2br((string) mb_convert_encoding($val->content,"HTML-ENTITIES", "UTF-8")),
					'parsed' => '',
					'keywords' => $keywords_hash,
					'author_id' => 1,
					'created_on' => (string) strtotime($val->post_date),
					'updated_on' => (string) strtotime($val->pubDate),
					'comments_enabled' => $comments_enabled,
					'status' => $status,
					'type' => 'wysiwyg-advanced'
				);
				
			}
			
		}
		
		// Insert posts into the database
		if($this->ci->db->insert_batch('default_blog', $posts))
		{
			return true;
		}
		else
		{
			return false;
		}

	}
	
	public function comments($xml)
	{
	
		foreach ($xml->channel->item as $val)
		{			
			$slug = (string) $val->post_name;
			
			// Comments
			if($val->comment)
			{
				foreach($val->comment as $comment)
				{
					if($comment->comment_type == "")
					{	
						$comments[$slug][] = array(
							'is_active' => 1,
							'user_id' => 0,
							'name' => (string) $comment->comment_author,
							'email' => (string) $comment->comment_author_email,
							'website' => (string) $comment->comment_author_url,
							'comment' => (string) mb_convert_encoding($comment->comment_content,"HTML-ENTITIES","UTF-8"),
							'parsed' => '',
							'module' => 'blog',
							//'module_id' => 1, // ID of the post/page
							'created_on' => (string) strtotime($comment->comment_date),
							'ip_address' => (string) $comment->comment_author_IP
						);
					}
				}
			}						
		}
		
		// Now that you have a comments array you can query all posts, and for each post, batch add comments (I know this hurts)
		$query = $this->ci->db->get('default_blog');
		if($query->num_rows() > 0) {
			foreach ($query->result() as $row)
			{
				if(isset($comments[$row->slug]))
				{
					$counter = 0;
					foreach($comments[$row->slug] as $v)
					{
					//for($i = 0; $i <= count($comments[$row->slug]); $i++) { // getting a memory error here
						$comments[$row->slug][$counter]['module_id'] = $row->id;
						$counter++;
					}	
					$this->ci->db->insert_batch('default_comments', $comments[$row->slug]);				
				}				
			}
		}
		
	} // end comments method
	
	public function users($xml)
	{
		
		// Move this function to a helper
		function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
		{
			$str = '';
			$count = strlen($charset);
			while ($length--)
			{
				$str .= $charset[mt_rand(0, $count-1)];
			}
			return $str;
		}
	
		foreach ($xml->channel->author as $val)
		{
			$rand = randString(6);
			$user = array(
				'email' => (string) $val->author_email,
				'password' => md5((string)$val->author_email.$rand.time()),
				'salt' => $rand,
				'group_id' => 1,
				'active' => 1,
				'created_on' => time(),
				'last_login' => 0,
				'username' => (string) $val->author_login
			);
			$this->ci->db->where('username',(string) $val->author_login);
			$this->ci->db->or_where('email',(string) $val->author_email);
			$query = $this->ci->db->get('default_users');
			if($query->num_rows() == 0)
			{
				$this->ci->db->insert('users',$user);
				$user_id = $this->ci->db->insert_id();
				$profile = array(
					'user_id' => $user_id,
					'display_name' => (string) $val->author_display_name,
					'first_name' => '[first_name]',
					'last_name' => '[last_name]',
					'lang' => 'en'
				);
				$this->ci->db->insert('default_profiles',$profile);
			}
		}
	
	}

	public function pages($xml)
	{
		// Defaults
		$parent_pages = array();
		$child_pages = array();
		$parents = array(); // key = post id, val = parent id

		foreach ($xml->channel->item as $val)
		{
			$parent_id = (string) $val->post_parent;
			if($parent_id != 0)
			{
				$parents[(string) $val->post_id] = $parent_id;
			}
		}
		
		foreach ($xml->channel->item as $val)
		{
		
			$slug = (string) $val->post_name;
						
			$comments_enabled = ((string) $val->comment_status == 'open') ? 1 : 0;
			
			$status = ((string) $val->status == 'publish') ? 'live' : 'draft';
			
			// Get content, category, and tags for every post
			if((string) $val->content != "" && (string) $val->post_type == "page" && (string) $val->status == "publish")
			{
				
				/*
				if((string) $val->post_parent == 0)
				{
					$parent_pages[(string) $val->post_id] = array(
						'title' => (string) $val->title,
						'slug' => $slug,
						'uri' => $slug,
						'parent_id' => 0,
						'revision_id' => 1,
						'layout_id' => 1,
						'meta_title' => '',
						'meta_keywords' => '',
						'meta_description' => '',
						'comments_enabled' => $comments_enabled,
						'status' => $status,
						'created_on' => (string) strtotime($val->post_date),
						'updated_on' => (string) strtotime($val->pubDate),
						'is_home' => 0,
						'strict_uri' => 1,
						'order' => 0
					);
				}
				else
				{
					$child_pages[(string) $val->post_parent.'-'.(string) $val->post_id] = array( // add parent page id in key
						'title' => (string) $val->title,
						'slug' => $slug,
						'uri' => $slug,
						'parent_id' => 0,
						'revision_id' => 1,
						'layout_id' => 1,
						'meta_title' => '',
						'meta_keywords' => '',
						'meta_description' => '',
						'comments_enabled' => $comments_enabled,
						'status' => $status,
						'created_on' => (string) strtotime($val->post_date),
						'updated_on' => (string) strtotime($val->pubDate),
						'is_home' => 0,
						'strict_uri' => 1,
						'order' => 0
					);
				}
				*/

				$pages[] = array(
					'title' => (string) $val->title,
					'slug' => $slug,
					'uri' => $slug,
					'parent_id' => 0,
					'revision_id' => 1,
					'layout_id' => 1,
					'meta_title' => '',
					'meta_keywords' => '',
					'meta_description' => '',
					'comments_enabled' => $comments_enabled,
					'status' => $status,
					'created_on' => (string) strtotime($val->post_date),
					'updated_on' => (string) strtotime($val->pubDate),
					'is_home' => 0,
					'strict_uri' => 1,
					'order' => 0
				);

			}
			
		}

		/*
		// Insert parents and their children
		foreach($parent_pages as $post_id => $val_array)
		{
			$this->ci->db->insert('default_pages',$val_array);
			$pyro_page_id = $this->ci->db->insert_id();
			foreach($child_pages as $parent_id => $val)
			{
				$parent_id = explode("-", $parent_id);
				if($post_id == $parent_id[0])
				{
					$val['parent_id'] = $pyro_page_id;
					$this->ci->db->insert('default_pages',$val);
				}
			}
		}
		*/

		// Insert pages into the database
		if($this->ci->db->insert_batch('default_pages', $pages))
		{
			return true;
		}
		else
		{
			return false;
		}

	}
			
}