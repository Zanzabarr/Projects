<?php
class Pages 
{
	private $pages = array(); // an array of Page objects, indexed by the page's id number
	private $top_ids = array(); // array of id's (in menu order) that exist in the top menu
	private $side_ids = array(); // array of id's that exist in the side menu
	private $slugs = array(); // an array of slug => id pairs for all pages
	private $db;
	public 	$error = '';
	
	public function __construct($db)
    {
		$this->db = $db;
	
		// new constructor!
		$sql = "SELECT 
	head.id as head_id, head.slug as head_slug, head.page_title as head_title, top.id, top.parent_id, 
	top.has_menu, top.menu_order, top.slug, top.page_title, top.status, top.visibility, top.date,
	(COALESCE(bot.menu_order, mid.menu_order, top.menu_order) ) * 10000 +
		(IF(IsNULL(bot.menu_order), -1, top.menu_order) + 1) +
		(IF(IsNULL(bot.menu_order), (IF(IsNULL( mid.menu_order),-1, top.menu_order) ),  mid.menu_order) + 1) *100 
		as thisOrder 
FROM `pages` top
LEFT JOIN
	`pages` mid
	ON top.parent_id = mid.id
LEFT JOIN
	`pages` bot
	ON mid.parent_id = bot.id
LEFT JOIN
	`pages` head
	ON COALESCE(bot.id, mid.id, top.id) = head.id
WHERE top.id > 0
ORDER BY top.has_menu, thisOrder ";
		
		$tmpPages = $this->db->run($sql);	
		if($tmpPages === false) 
		{	
			$this->error = 'Error getting page data from the database';
			my_log($this->error);
		}
		else
		{
			// need a temporary array of children for each parent since the parent may not have been written
			$tmpParents = array();
			
			foreach ($tmpPages as $page)
			{
				$this->pages[ $page['id'] ] = new Page($page['id'], $page['parent_id'], $page['has_menu'], $page['menu_order'], $page['slug'], $page['page_title'], $page['status'], $page['visibility'], $page['head_id'],$page['head_slug'],$page['head_title'],$page['date'],$page['thisOrder'] );
				
				$this->slugs[ $page['slug'] ] = $page['id'];
				
				// if page is a child of another page: update its parent
				if ( $page['parent_id'] )
				{
					$tmpParents[$page['parent_id'] ][ $page['menu_order'] ] = $page['id'];
				}
				
				if ($page['has_menu'] == 1) $this->top_ids[] = $page['id'];
				elseif ( $page['has_menu'] == 2) $this->side_ids[] = $page['id'];
			}
			// add the children to each parent
			foreach ($tmpParents as $parent_id => $arChild)
			{
				if(isset($this->pages[ $parent_id ]))$this->pages[ $parent_id ]->set_children($arChild);
			}
		}
	}
	
	// calls db to get content
	public function get_page($id)
	{
		if ( $this->valid_page($id) )
		{
			$tmpPage =  $this->db->select('pages', "`id`=:id", array(':id' => $id), $fields="*");
			
			return $tmpPage[0];
		}
		else return false;
	}
	
	// returns full page data
	public function get_page_by_slug($slug)
	{
		// find the id of the slug, if it doesn't exist, or isn't a valid page, return false
		$id = $this->get_id_by_slug($slug);
		if($id === false ) return false;
		return $this->get_page($id);
		
	}
	
	public function get_id_by_slug($slug)
	{
		return isset($this->slugs[$slug]) ? $this->slugs[$slug] : false;
	}
		
	
	public function get_page_title_by_id($id)
	{
		if (isset($this->pages[$id])) return $this->pages[$id]->get_page_title();
	}
	
	public function get_page_obj_by_slug($slug)
	{
		$id = isset($this->slugs[$slug]) ? $this->slugs[$slug] : false;
		// fail if no such page
		if ($id === false) return false;
		return $this->pages[$id];
	}
	
	public function get_href($id)
	{
		global $logged_in; 

		if ( $this->valid_page($id) ) return $this->pages[$id]->get_href();
		else return false;
	}
	
	// id is the page's id
	// text is the text to appear in the link: default: page title
	// class is the class associated with the link (comma separated list of classes)
	public function make_link($id, $text = false, $class = '', $linkextras = '')
	{
		$href = $this->get_href($id);
		if ($href === false) return false;
		if (! $text) 
		{
			$text = $this->pages[$id]->get_page_title();
		}
		if ($class != '')
		{
			$class = " class='{$class}'";
		}
		echo "<a href='{$href}'{$class} {$linkextras}>{$text}</a>";
	}
	
	// returns true if the id is a valid page with published status and (page is for all users or user is currently logged in)
	// otherwise false
	public function valid_page($id)
	{
		global $logged_in;
		return isset($this->pages[$id]) && 
				($this->pages[$id]->get_status() == 1 || $logged_in)
				&& 
				(
					$this->pages[$id]->get_visibility() == 0 || 
					($this->pages[$id]->get_visibility() == 2 && logged_in_as_member()) || 
					$logged_in 
				);
	}
	
	// returns array of parent data objects, in parent->child->grandchild order 
	//		(does include the calling slug's data)
	public function breadcrumb_data($slug)
	{

		$currentPage = $this->get_page_obj_by_slug($slug);
		if ($currentPage === false) return false;
		
		$pageChain[] = $currentPage;
		$tmpId = $currentPage->get_parent_id();
		while ( $tmpId != 0) 
		{
			$currentPage = $this->pages[$tmpId];
			array_unshift($pageChain,$currentPage) ;
			$tmpId = $currentPage->get_parent_id();
		}
		return $pageChain;
	}
	
	// returns array of parent data url/titles in parent->child->grandchild order
	// 	includes calling slug's data
	public function breadcrumb($slug)
	{
		$crumbs = $this->breadcrumb_data($slug);
		
		// break out the required data into a simple array
		$breadcrumb = array();
		if(! $crumbs) return false;
		foreach ($crumbs as $crumb)
		{
			$breadcrumb[] = array(
				'url' => $crumb->get_slug(),
				'title' => $crumb->get_page_title()
			);
		}
		
		return $breadcrumb;
	}
	
	// if nested is true, gr_children are returned as a sub array of children
	// otherwise, children and grandchildren are put of the same array
	public function get_chain_by_slug($slug, $nested = false)
	{
		$data = array();
		// find the head id  in top/side_ids
		if (array_key_exists($slug, $this->slugs))
		{
			
			$tmpId = $this->slugs[$slug];

			if(in_array($tmpId, $this->top_ids) || in_array($tmpId, $this->side_ids) && $this->valid_page($tmpId) )
			{
				// this page is in the top_menu
				// it is the currently selected page
				$data['selected']['id'] = $tmpId;
				
				// this is the highest parent id
				$data['head']['id'] = $this->pages[$tmpId]->get_head_id();
				$data['head']['slug'] = $this->pages[$tmpId]->get_head_slug();
				$data['head']['title'] = $this->pages[$tmpId]->get_head_title();
				
			}
			else return false; // slug exists but isn't in top or side menu
		} else return false; // slug doesn't exist
		
		// get all children of the head: store by sorted_order
		foreach( $this->pages[ $data['head']['id'] ]->get_children() as $child_id )
		{
			$descendant = $this->get_descendants_menu_data($child_id, $nested);
			if ($descendant !== false) $data['descendants'][] = $descendant;
		}
		return $data;
	}
	
	// takes an id# and returns all that item's children
	//		if nested is true, return grand children as sub array
	//		otherwise children and grand children are part of the same array
	public function get_descendants_menu_data($parent_id, $nested = false)
	{	
		if ($this->valid_page($parent_id) )
		{		
			$objChild = $this->pages[$parent_id];
			$intOrder = $objChild->get_sorted_order();
			$data[$intOrder]['slug'] = $objChild->get_slug();
			$data[$intOrder]['id'] = $objChild->get_id();
			$data[$intOrder]['page_title'] = $objChild->get_page_title();
			$data[$intOrder]['is_gr_child'] = false;
			
			// now all grand-children for this child
			foreach( $this->pages[ $data[$intOrder]['id'] ]->get_children() as $gr_child_id )
			{
				// if nested version is called, build the grChild sub-array
				if	($nested && $this->valid_page($gr_child_id) )
				{
					$objGrChild = $this->pages[$gr_child_id];
					$grIntOrder = $objGrChild->get_sorted_order();
					$data[$intOrder]['grChild'][$grIntOrder]['slug'] = $objGrChild->get_slug();
					$data[$intOrder]['grChild'][$grIntOrder]['id'] = $objGrChild->get_id();
					$data[$intOrder]['grChild'][$grIntOrder]['page_title'] = $objGrChild->get_page_title();
					$data[$intOrder]['grChild'][$grIntOrder]['is_gr_child'] = true;
				}
				elseif (!$nested && $this->valid_page($gr_child_id) )
				{
					$objGrChild = $this->pages[$gr_child_id];
					$intOrder = $objGrChild->get_sorted_order();
					$data[$intOrder]['slug'] = $objGrChild->get_slug();
					$data[$intOrder]['id'] = $objGrChild->get_id();
					$data[$intOrder]['page_title'] = $objGrChild->get_page_title();
					$data[$intOrder]['is_gr_child'] = true;
				}
				else return false;
			}
		}else return false;
		return $data;
	}
	
	public function get_top_menu_heads($type = "top")
	{
		$data = array();
		if($type != 'top') $menu_ids = $this->side_ids;
		else $menu_ids = $this->top_ids;
		
		foreach($menu_ids as $id)
		{
			$head = $this->pages[$id];
			if ($this->valid_page($id) && $head->get_parent_id() == 0  )
			{			
				$data[] = array(
					'id' => $id,
					'title' => $head->get_page_title(),
					'slug' => $head->get_slug()
				);
			}	
		}
		return count($data) > 0 ? $data : false;
	}

	// outputs the chain of titles/href as a breadcrumb trail
	public function breadcrumbs($slug, $separator = ' &gt;&gt; ')
	{
		$breadcrumbs = $this->breadcrumb_data($slug);
		// if there is no data, exit
		if($breadcrumbs === false) return; 
?>		
		<div class="breadcrumb">
        YOU ARE HERE: 
<?php		
		for($i = 0; $i< count($breadcrumbs); $i++)
		{
			$title = strtoupper($breadcrumbs[$i]->get_page_title());
			 echo "<a href='{$breadcrumbs[$i]->get_slug()}'>{$title}</a>" ;
			 echo $i + 1 == count($breadcrumbs) ? '' : $separator;  
		}
?>
		</div> <!-- breadcrumb --> 
<?php		
	}
} 
