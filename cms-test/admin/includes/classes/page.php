<?php
class Page
{
	private $id;
	private $parent_id;
	private $has_menu;
	private $menu_order;
	private $slug;
	private $page_title;
	private $status;
	private $visibility;
	private $children = array();	// key: menu_order, value: id
	
	public function __construct($id, $parent_id, $has_menu, $menu_order, $slug, $page_title, $status, $visibility,$head_id = 0, $head_slug = 'fill', $head_title = 'fill', $date = '00-00-00 00:00:00', $thisOrder = 0 )
	{
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->has_menu = $has_menu;		// 0 = no menu / 1 = top menu / 2 = side menu //
		$this->menu_order = $menu_order;
		$this->slug = $slug;
		$this->page_title = $page_title;
		$this->status = $status;
		$this->visibility = $visibility;
		// the following have been added and aren't included in original constructor: have default values for transition
		$this->head_id = $head_id;
		$this->head_slug = $head_slug;
		$this->head_title = $head_title;
		$this->date = $date;
		$this->sorted_order = $thisOrder; // this is the expanded order: represents parent/child/gr_child order

		

	}
	public function get_id()
	{
	
		return $this->id;
	}
	
	public function get_parent_id()
	{
	
		return $this->parent_id;
	}
	
	public function get_slug()
	{
	
		return $this->slug;
	}
	
	public function get_page_title()
	{
		return $this->page_title;
	}
	
	public function get_head_id()
	{
	
		return $this->head_id;
	}
	public function get_head_slug()
	{
	
		return $this->head_slug;
	}
	public function get_head_title()
	{
	
		return $this->head_title;
	}
	public function get_sorted_order()
	{
	
		return $this->sorted_order;
	}
	
	public function get_status()
	{
		return $this->status;
	}
	public function get_visibility()
	{
		return $this->visibility;
	}
	// assumes config has been included
	public function get_href()
	{
		global $_config;
		return $_config['site_path']. $this->slug;
	}
	
	public function get_children()
	{
		return $this->children;
	}
	
	// children is an array of key value pairs:
	// key is page_order, value is id
	public function set_children($children)
	{
		$this->children = $children;
	}
	
	// true if this is the parent of any other page   ??????
	public function is_parent()
	{
		if (count($this->children) > 0 ) return true;
		return false;
	}
	
	// to get the level, we need to pass it the pages object to refer to other pages
	public function get_level(&$pages)
	{
		
		if ($this->parent_id == 0 ) return 1;
		
		// get the parent's id
		$parentId = $pages[ $this->parent_id ]->get_parent_id();
		// there are only two more possible levels, if the parent id is 0, this is level 2. Otherwise, level 3
		if ($parentId == 0) return 2; 
		return 3;
	}
	
	// a less intense version of the above that only checks for first level
	public function is_top_level()
	{
		if ( $this->parenet_id == 0 ) return true;
		return false;
	}
}
