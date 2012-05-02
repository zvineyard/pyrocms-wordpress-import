<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Wp_Import {
		
	private $ci;
	
	function __construct() {
        $this->ci =& get_instance();
    }
	
	private function get_duplicates($array) {
		return array_unique(array_diff_assoc($array, array_unique($array)));
	}

	public function has_duplicate_titles($xml) {

		foreach ($xml->channel->item as $val) {
			if((string) $val->content != "" && (string) $val->post_type == "post") {
				$titles[] = (string) mb_convert_encoding($val->title,"HTML-ENTITIES", "UTF-8");
			}
		}
		$dups = $this->get_duplicates($titles);
		if(count($dups) > 0) {
			return $dups;
		} else {
			return false;
		}
		
	}

	public function categories($xml) {
		
		foreach ($xml->channel->category as $val) {
			$categories[] = array(
				'slug' => (string) $val->category_nicename,
				'title' => (string) $val->cat_name
			);
		}		
		if($this->ci->db->insert_batch('default_blog_categories', $categories)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function tags($xml) {
	
		foreach ($xml->channel->tag as $val) {
			$tags[] = array(
				'name' => (string) $val->tag_name
			);
		}
		if($this->ci->db->insert_batch('default_keywords', $tags)) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function posts($xml) { // This returns the comments for each post, which will be imported
	
		foreach ($xml->channel->item as $val) {
			
			$slug = (string) $val->post_name;
						
			$comments_enabled = 0;
			if($val->comment_status == 'open') {
				$comments_enabled = 1;
			}
			
			$status = 'live';
			if($val->status != 'publish') {
				$status = 'draft';
			}
			
			// Get content, category, and tags for every post
			if((string) $val->content != "" && (string) $val->post_type == "post") {
				
				// Get a category slug
				foreach($val->category as $cat) {
					$category_slug = "";
					if($cat[0]['domain'] == 'category') {
						$category_slug = (string) $cat[0]['nicename'];
						break;
					}
				}
				
				// Query the ID of this posts's category slug
				$category_id = 0;
				$this->ci->db->where('slug',$category_slug);
				$this->ci->db->limit(1);
				$query = $this->ci->db->get('default_blog_categories');
				if($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$category_id = $row->id;
					}
				}
				
				// Get tag slugs
				$tag_slugs = array();
				foreach($val->category as $tag) {
					if($tag[0]['domain'] == 'post_tag') {
						$tag_slugs[] = (string) $tag;
					}
				}
				
				// Assign a keyword hash to the post and insert tags
				$keywords_hash = "";
				if(count($tag_slugs) > 0) {
				
					$keywords_hash = md5(time()+rand(1,1000000));
				
					// Query tag IDs off the tag slugs and assign
					$this->ci->db->where('name',$tag_slugs[0]);
					foreach($tag_slugs as $v) {
						$this->ci->db->or_where('name',$v);
					}
					$query = $this->ci->db->get('default_keywords');
					$assign = array();
					if($query->num_rows() > 0) {
						foreach ($query->result() as $row) {
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
		if($this->ci->db->insert_batch('default_blog', $posts)) {
			return true;
		} else {
			return false;
		}

	}
	
	public function comments($xml) {
	
		foreach ($xml->channel->item as $val) {
			
			$slug = (string) $val->post_name;
			
			// Comments
			if($val->comment) {
				foreach($val->comment as $comment) {
					if($comment->comment_type == "") {	
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
			foreach ($query->result() as $row) {
				if(isset($comments[$row->slug])) {
					$counter = 0;
					foreach($comments[$row->slug] as $v) {
					//for($i = 0; $i <= count($comments[$row->slug]); $i++) { // getting a memory error here
						$comments[$row->slug][$counter]['module_id'] = $row->id;
						$counter++;
					}	
					$this->ci->db->insert_batch('default_comments', $comments[$row->slug]);				
				}				
			}
		}
		
	} // end comments method

}