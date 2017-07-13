<?php 
class ecom_category extends nested_set {
	
	public function __construct()
	{
		parent::__construct('ecom_category', 'ecom_product');
		$this->categoryDataTable = "ecom_category_data";
		$this->tmpCategoryTable	 = "ecom_tmp_category";

	}
	
	// returns false on success, error data on failure
	public function create_ecom_category_data_table()
	{
		$query = "
			CREATE TABLE IF NOT EXISTS `{$this->categoryDataTable}` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`url` varchar(100) NOT NULL,
				`name` varchar(100) NOT NULL,
				`desc` text NOT NULL,
				`seo_keywords` text NOT NULL,
				`seo_description` varchar(250) NOT NULL,
				`seo_title` varchar(100) NOT NULL,
				`status` tinyint(1) NOT NULL,
				`date_created` varchar(30) NOT NULL,
				`date_updated` varchar(30) NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `uni` (`url`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		";	
		$result = logged_query($query,0,array());
		if($result===false) return "Unable to create category data table";
		
		$query = "
			CREATE TABLE IF NOT EXISTS `{$this->categoryDataTable}_rev` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`ecom_cat_data_id` int(11) NOT NULL,
				`url` varchar(100) NOT NULL,
				`name` varchar(100) NOT NULL,
				`desc` text NOT NULL,
				`seo_keywords` text NOT NULL,
				`seo_description` varchar(250) NOT NULL,
				`seo_title` varchar(100) NOT NULL,
				`status` tinyint(1) NOT NULL,
				`date_created` varchar(30) NOT NULL,
				`date_updated` varchar(30) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		";	
		$result = logged_query($query,0,array());
		if($result===false) return "Unable to create category data table";
		else return false; //returns error status = false
	}
	
	public function  insert_category($data)
	{
		global $_config;
		logged_query("
			INSERT INTO {$this->categoryDataTable}(`url`, `name`, `desc`, `seo_keywords`, `seo_description`, `seo_title`, `date_created`, `date_updated`) 
			VALUES(:url, :name, :desc, :seo_keywords, :seo_description, :seo_title, UTC_TIMESTAMP(), UTC_TIMESTAMP());",0,array(
				":url" => $data['url'],
				":name" => $data['name'],
				":desc" => $data['desc'],
				":seo_keywords" => $data['seo_keywords'],
				":seo_description" => $data['seo_description'],
				":seo_title" => $data['seo_title']
			));
		return $_config['db']->getLastInsertId();

	}	
	
	public function update_category($data)
	{
			$result = logged_query("
				UPDATE {$this->categoryDataTable}
				SET `url`				= :url,
					`name`				= :name,
					`desc`				= :desc,
					`seo_keywords`		= :seo_keywords,
					`seo_description`	= :seo_description,	
					`seo_title`			= :seo_title,
					`status`			= :status,
					`date_created`		= :date_created,
					`date_updated`		= UTC_TIMESTAMP()
				WHERE id = '{$data['id']}';",0,array(
				":url"				=> "{$data['url']}",
				":name"				=> "{$data['name']}",
				":desc"				=> "{$data['desc']}",
				":seo_keywords"		=> "{$data['seo_keywords']}",
				":seo_description"	=> "{$data['seo_description']}",	
				":seo_title"		=> "{$data['seo_title']}",
				":status"			=> "{$data['status']}",
				":date_created"		=> "{$data['date_created']}"
			));	
			if ($result === false) 
			{
				my_log("Error updating Category");
				return false;
			}
			else return true;
	}
	
	// returns category data
	public function get_category($id)
	{
		if( ! is_pos_int($id) )return false;
		$result = logged_query_assoc_array("SELECT * FROM `{$this->categoryDataTable}` WHERE `id` = {$id} LIMIT 1;",null,0,array());
		if( $result && count($result) > 0 ) return $result[0];
		return false;
	}
	
	public function get_category_by_url($url)
	{
		$url = trim($url);
		$result = logged_query_assoc_array("SELECT * FROM `{$this->categoryDataTable}` WHERE `url` = '{$url}' LIMIT 1;",null,0,array());
		if( $result && count($result) > 0 ) return $result[0];
		return false;
	}
	
	// get category data
	public function get_category_by_product($url)
	{
	
		$url = trim($url);
		$result = logged_query_assoc_array("
			SELECT `{$this->categoryDataTable}`.* 
			FROM `{$this->categoryDataTable}`, `ecom_product`
			WHERE `category_id` = `{$this->categoryDataTable}`.`id`
			  AND `ecom_product`.`url` = '{$url}'",null,0,array());
		if( $result && count($result) > 0 ) return $result[0];
		return false;
	}
	
	/* added by BEH 11/11/2014 */
	public function get_real_category_menu() {
		$fullData = $this->get_full_cat_data();
		$catMenu = array();
		$i = 0;	//keep count
		foreach($fullData as $f) {
			if($f['depth']==0) {
				$i++;
				$catMenu[$i] = $f;
			} else {
				$catMenu[$i]['children'][] = $f;
			}
		}
		return $catMenu;
	}
	/***/
	
	// returns ecom category data in menu building format
	// grabs category info from the url
	public function get_ecom_menu_data()
	{
		// get the data (limited to a depth of two)
		$data = $this->get_category_chain(2, false);
		if (! $data['catPath'] || ! $data['tree']) return false;
		
		$tree = $data['tree']; // all categories(parent and descendants two deep) linked to the active cat
		$catPath = $data['catPath']; // categories from parent to selected (last one is the actual selected category
									// but may not be included in the menu because it is deeper than 2
		// find selected id
		// it is either the first (if not part of any sub menus) 
		// or second (because we aren't highlighting the pop out, second depth element in the menu)

		if (isset($catPath[1]['id'])) 		$selected = array('id'=>$catPath[1]['id']);
		elseif (isset($catPath[0]['id'])) 	$selected = array('id'=>$catPath[0]['id']);
		else 								$selected = array();
		
		$head = array(
			'id' 	=> $catPath[0]['id'],
			'slug' 	=> $this->_config['cartName'].'/category/' . $catPath[0]['url'],
			'title' =>$catPath[0]['name']
		);
		
		// alter depth data to fit is_gr_child for menus
		$descendants = array();
		$count = 0;
		foreach($tree as $row)
		{
			$is_gr_child =  $row['depth'] - 1;
			
			$curRow = array(
				'slug' => $this->_config['cartName'].'/category/' . $row['url'],
				'id'	=> $row['id'],
				'page_title'	=> $row['name'],
				'is_gr_child' 	=> $is_gr_child
			);
			if($is_gr_child)
			{
				// add the row to the grand child subarray
				$descendants['descendants'][$count]['grChild'][] = $curRow;
			}
			else
			{
				// add the row to the next level
				$descendants['descendants'][++$count] = $curRow;
			}
		}
		
		return array(
			'selected' => $selected,
			'head'		=> $head,
			'descendants' => $descendants
		);
	}
	
	// returns all data needed to create a side menu for the passed page
	// builds the menu based on categories
	// NOTE: categories can be indeterminate levels deep, but menus can only be 3 levels deep
	//   THEREFORE: category head, sub category, sub-sub are the only ones shown here
	//   CORRECTION: this function will return the full chain, what you want to do with that info 
	// 				 is up to you (we may allow deeper nested menus in the future)	
	//	URL:	 	current category is extracted from the url
	//	Returns: array
	//				'tree'  : this is all descendats of the category parent of the cat in the url 
	//				'catPath' 	: all categories in the selected category's path,
	//							starts with the top level and ends at the selected cat
	// 
	public function get_category_chain($depth=0, $includeParent=true)
	{
		if ( ! is_pos_int($depth, true) ) return false;
		
		// find the category 
		$category = $this->get_cat_by_full_url();
//	var_dump($category);	
		// get the chain of categories from the parent to the selected category
		$catPath = $this->get_path($category['id']);

		$tree = $this->get_descendants($catPath[0]['id'],$depth,$includeParent);
		
		return array('tree' => $tree, 'catPath' => $catPath);
	}

	// returns category data extracted from the url 
	// (from category or products pages: also from searches with category as part of search)
	public function get_cat_by_full_url()
	{
		$catData = array();  // array to contain category data
		
		// if this is a category: grab the category
		if($cat = uri::get('category'))
		{
			// get category data
			$catData = $this->get_category_by_url($cat);
		}
		// else if this is a product: find the product's category
		elseif($prod = uri::get('products'))
		{
			// get category data
			$catData = $this->get_category_by_product($prod);
		}
		// otherwise, there is no chain to retrieve
		else return false;
		
		return $catData;
			
		// else return false
	}

	// id is the id of the terminal category
	public function breadcrumb($id)
	{
		// get the path to that category
		$rawpath = $this->get_path($id);
		$path = array();
		// set the crumb url
		if($rawpath) 
		{
			foreach($rawpath as $segment)
			{
				$path[] = array (
					'url' 	=> $this->_config['cartName'] ."/category/". $segment['url'],
					'title' => $segment['name']
				);
			} 
		}
		
		return $path;
	}
	
	public function breadcrumb_by_product($url, $title = false)
	{
		// if the title isn't passed, we need to find it:
		if (! $title) 
		{
			$prod = ecom_functions::get_basic_product($url);
			$title = $prod['title'];
		}
		
		$cat = $this->get_category_by_product($url);
		if ($cat['id'] === false) return false;
		
		$partPath = array();
		$partPath = $this->breadcrumb($cat['id']);
		// now add the product data
		if($url && $title) $partPath[] = array ('url' => $this->_config['cartName'] . "/products/" .$url, 'title' => $title);
		
		return $partPath;
	}
	


}