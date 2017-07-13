<?php

/* 	requires an extended class to set:
//		public function  insert_category($data)			database call to insert category data into a separate table
//															$data must be an array of `field_name` => 'value' pairs for insertion
//															with it's id = to `nested_set`.`category_data_id`
//		public function  update_category($data)			database call to update category data into the same table
//															$data must be an array of `field_name` => 'value' pairs for insertion
//															with it's id = to `nested_set`.`category_data_id`
//		public function  get_category($id)				database call to get table data from the category data table
//															$id is the id of id of the record to be retrieved
//															returns an associative array of table data
// the extended class must have a parameterless constructor for the ajax calls to work
// example at admin/includes/classes/ecom_category.php
*/ 
class nested_set {
	// required parameters
	private 	$nestedCategory;	// 	name of the database table
	private 	$productTable;		// 	table of individual children of the category
									// 		must have a field: category_id => id of product's category	
	protected	$_config;			//  home for the global config var
	
	// parameters that may be overloaded by extended class
	protected	$categoryDataTable 	= 'nested_category_data';	//	table name that contains the category data
	protected	$tmpCategoryTable	= 'nested_tmp_category';	// 	name of tmp category for node moves
	protected	$base 				= 'BASE';
	protected	$divider 			= "&raquo;";				// 	divider used in BuildPath and BuildList (can be replaced with an <img>)
																//		default is >>
	protected 	$backDivider		= "&laquo;";				//	used in BuildList for back arrow on Parent level
																//  	default is <<
	protected	$divGroup;										//	divider group: user defined divider  wrapped in a span with class: path_divider
																//		used by the build path function
																
	public function __construct( $nestedCategory = 'nested_category', $productTable = 'nested_product' )
    {
		global $_config ;
		$this->_config 			= $_config;
		$this->nestedCategory	= $nestedCategory;
		$this->productTable 	= $productTable;
		$this->divGroup			= "<span class='path_divider'>{$this->divider}</span>";
		
		
	}
	
	// build the required tables
	// returns true on success: error message on failure
	public function create_nested_table()
	{
		$query = "
			CREATE TABLE IF NOT EXISTS {$this->nestedCategory} (
				`category_data_id` INT NOT NULL PRIMARY KEY,
				`lft` INT NOT NULL,
				`rgt` INT NOT NULL
			);
		";
		$result = logged_query($query,0,array());
		if($result===false) return "Error creating nested set tables";
		
		$query = "
			CREATE TABLE IF NOT EXISTS {$this->tmpCategoryTable} (
				`category_data_id` INT NOT NULL PRIMARY KEY,
				`lft` INT NOT NULL,
				`rgt` INT NOT NULL
			);
		";
		$result = logged_query($query,0,array());
		if($result!==false) return true;
		else return "Error creating nested set tables";
	}
	
	// parentId is the id of the currently selected category position
	// childId is the optional id of the value to be added to the end of the parent list
	// idOfName is the js selector for the page field that controls the name of the category (used in maintaining the category)
	public function build_category_selector($parentId, $childId, $idOfName = '#name')
	{
		?>
			<div class='input_wrap'>
				<label class="tipRight" title="About this category">Position</label>
				<div id="cat_wrap" class="input_inner" style="position:relative;">
					<div class="cat_sel no_counter"><?php $immediateParent = $this->build_path($parentId,$childId); ?></div>
					<div class="cat_list"><?php $this->build_child_list($parentId, $immediateParent); ?></div>
				</div>
			</div>
			<div class='info'>
				<?php // values for updating the Position fields?>
				<input type='hidden' id='caller' value='<?php echo get_class($this); ?>' />
				<input type='hidden' id='parent_id' value='<?php echo $immediateParent; ?>' />
				<input type='hidden' id='category_id' value='<?php echo $parentId; ?>' />
				<input type='hidden' id='id_of_name' value='<?php echo $idOfName; ?>' />
				<?php // actual fields for handling insert/update of category: js fills values ?>
				<input type='hidden' id='curParentId' name='cur_parent_id'  />
				<input type='hidden' id='newCatId' name='new_cat_id'  />
				<input type='hidden' id='hasSibling' name='has_sibling'  />
				<input type='hidden' id='afterIsParent' name='after_is_parent'  />
				<input type='hidden' id='afterId' name='after_id'  />
			</div>
			<div class="clearFix"></div>
			<style type="text/css">
				.cat_sel, .cat_list {   
					background: none repeat scroll 0 0 #EEEEEE;
					border-color: #BBBBBB #DDDDDD #DDDDDD #BBBBBB;
					border-radius: 5px 5px 5px 5px;
					border-style: solid;
					border-width: 1px;
					display: inline-block;
					width:220px;
					padding:5px;
					max-height:132px;
					font-size:10pt;
				}
				.cat_sel span {display:inline-block; cursor:default;}
				.cat_sel a 	{text-decoration:none;color:black;}
				.cat_sel a.path_child {color:red; cursor:default;}
				.cat_sel span.path_base {color:grey; cursor:pointer;}
				.path_divider {padding:3px;}
				
				.cat_list {position:absolute; top: 0px; right:42px;}
				.cat_list .selected_category{color:red; cursor: pointer;}
				
				.path_parent_hover {color: grey}
				
				.category_child, .category_parent {cursor: pointer}
				.category_child, .selected_category {padding-left:10px;}
				.category_child span {display:none;}
				.category_child:hover span{display:inline;}
				.selected_category:hover span{display:none;}
				
				.category_parent .back {display:none;}
				.category_parent:hover .back {display:inline-block;}
				.category_parent:hover .forward {display:none;}
			</style>
			
			<script type="text/javascript">
				$(document).ready(function() {
							
				//variables :
				var ajaxPath 		= config.admin_url + "components/nested_set/ajax.php",
					callingClass	= $('#caller').val(),
					parentId		= $('#parent_id').val(),
					categoryId		= $('#category_id').val(),
					idOfName		= $('#id_of_name').val();
					
				// initialize updateValues	
				updateInputs();
					
				// maintain the category name
				$(idOfName).keyup(function(e){
					$('.cat_sel .path_child, .cat_list .selected_category').html($(this).val());
				});

				// allow sorting of the category
				$('.cat_list').sortable({
					items : '.category_child, .selected_category',
					containment: 'parent',
					cursor:	'N-resize',
					cursorAt: {bottom: 0},
					cancel: '.category_child',
					update: updateInputs
				});

				// trigger ajax call to select a new parent level
				$('.cat_sel').on('click', 'a, .path_base', resetParentFromPath);
				// trigger ajax call to go back a parent level
				$('.cat_list').on('click', '.category_parent', resetParentBack);
				// trigger ajax call to go forward a parent level
				$('.cat_list').on('click', '.category_child', resetParentForward);


				// ************************************ functions ******************************** //
				// takes the information in cat_list and updates hidden inputs that tell the system what kind of insert/update to do and passes the req'd info
				function updateInputs()
				{
					var	curParentId = $('.cat_list .category_parent').attr('rel'),
						$newCat = $('.cat_list .selected_category'),
						newCatId = $newCat.attr('rel'),
						hasSibling = $('.cat_list .category_child').length ? 1 : 0,
						$afterElement = $('.cat_list .selected_category').prev(),
						afterIsParent = $afterElement.hasClass('category_parent') ? 1 : 0,
						afterId	= $afterElement.attr('rel');
					
						$('input#curParentId').val(curParentId);
						$('input#newCatId').val(newCatId);
						$('input#hasSibling').val(hasSibling);
						$('input#afterIsParent').val(afterIsParent);
						$('input#afterId').val(afterId);
				}

				// *******************************  asynch actions  ******************************* //
				function resetParentFromPath(e){
					e.preventDefault();
					var $this   		= $(this),
						selectedType	= $this.attr('class'),
						newParent		= $this.attr('rel');

					// don't do anything if this is the child element
					if(selectedType == 'path_child') return false;
					
					// use the newParent's ID instead of it's parent's id:
					prevCat = 0;
					ajaxResetParent( newParent, prevCat )
				}

				function resetParentBack(e){
					e.preventDefault();
					var newParent = $(this).attr('rel');
					
					// prevCat tells it to use newParent's parent as the new category 
					prevCat = 1;
					ajaxResetParent( newParent, prevCat );
					
					
				}

				function resetParentForward(e){
					e.preventDefault();
					var $this = $(this),
						newParent = $this.attr('rel'),
						isSelected = $this.hasClass('selected_category');
					
					// can't go forward with selected(new) category
					if(isSelected) return false;
					
					// prevCat tells it to use newParent's parent as the new category 
					prevCat = 0;
					ajaxResetParent( newParent, prevCat );
				}

				// set the field's data to reflect the category having this new parent
				//		prevCat = 1: set the parent of parentId as the new parent instead
				//				= 0: use parent as normal
				function ajaxResetParent(parentId, prevCat )
				{
					// build ajax call:
					$.ajax({
						url : ajaxPath,
						type : 'POST',
						data: 
						{
							option 		: 'reset_parent',
							parentId 	: parentId,
							categoryId	: categoryId,
							prev_cat	: prevCat,
							callingClass: callingClass
						},
						error : function (strResponse)
						{
							//openBanner('error', 'Error changing category', '"' + strResponse +'"'); 
							//console.log ('error');
						},
						success: function (strResponse) 
						{
							var jsonResp = jQuery.parseJSON(strResponse);
							{	
								if(! jsonResp.error)
								{
									// set the new values
									$('.cat_sel').html(jsonResp.path);
									$('.cat_list').html(jsonResp.list);
									// since the name may have been changed and not updated in db, update the name from the name field
									$('.cat_sel .path_child, .cat_list .selected_category').html($(idOfName).val());
									
									// create the inputs that will tell what kind of insert/update to perform...involved!
									updateInputs();
								}
							}
							
						}
					});
				}
			});	
			</script>
		<?php
	}
	// *****************************Access Function ********************************** //
	

	public function show_nested()
	{
		// show nested in order
		$query = "
				SELECT CONCAT( REPEAT( '-', ( COUNT( parent.category_data_id ) -1 ) ) , data.name ) AS spaced_name, data.*
				FROM {$this->nestedCategory} AS node, {$this->nestedCategory} AS parent, {$this->categoryDataTable} AS data
				WHERE `node`.`category_data_id` = `data`.`id`
				AND node.lft BETWEEN parent.lft AND parent.rgt
				GROUP BY node.category_data_id
				ORDER BY node.lft
			";
		$result = logged_query_assoc_array($query,null,0,array());
		
		foreach($result as $row) echo "{$row['spaced_name']}<br>";
	}	
	
	public function get_nested($spaces = '-')
	{
		return logged_query_assoc_array("
			SELECT CONCAT( REPEAT( '{$spaces}', ( COUNT( parent.category_data_id ) -1 ) ) , data.name ) AS spaced_name, data.*, COUNT( parent.category_data_id ) AS depth
			FROM {$this->nestedCategory} AS node, {$this->nestedCategory} AS parent, {$this->categoryDataTable} AS data
			WHERE `node`.`category_data_id` = `data`.`id`
			AND node.lft BETWEEN parent.lft AND parent.rgt
			GROUP BY node.category_data_id
			ORDER BY node.lft
		",null,0,array());
	}

	// get the parent id of node = id
	public function get_parent($id = false)
	{
		if( ! is_pos_int($id) )return false;
		$result = logged_query_assoc_array("
			SELECT `category_data_id`, 
			   (SELECT `category_data_id`
				FROM `{$this->nestedCategory}` AS `t2` 
				WHERE `t2`.`lft`  < `t1`.`lft` 
				  AND `t2`.`rgt` > `t1`.`rgt`
				ORDER BY `t2`.`rgt`-`t1`.`rgt`ASC 
				LIMIT 1)
				AS `parent`
			FROM `{$this->nestedCategory}` AS `t1`
			WHERE `t1`.`category_data_id` = {$id}
			ORDER BY `rgt`-`lft` DESC
		",null,0,array());
		if( $result && count($result) > 0 ) return $result[0]['parent'];
		return false;
	}
	
	// returns the path to node by category_data_id
	public function get_path($id = false)
	{
		if ( ! is_pos_int($id) ) return false;
		return logged_query_assoc_array("
			SELECT DISTINCT `parent`.`category_data_id`, `data`.*
			FROM `{$this->nestedCategory}` AS `node`, `{$this->nestedCategory}` AS parent, `{$this->categoryDataTable}` AS `data`
			WHERE `data`.`id` = `parent`.`category_data_id`
			  AND `node`.`lft` BETWEEN `parent`.`lft` AND `parent`.`rgt`
			  AND `node`.`category_data_id`= {$id} 
			ORDER BY `parent`.`lft`;
		",null,0,array());
	}

	
	// 	returns info about children of node with id = $id
	//		by default, array includes the parent at first posn, 
	//		set $includeParent = false to leave the parent out of the list
	public function get_immediate_children($id = false, $includeParent = true)
	{
		if ( ! is_pos_int($id) ) return false;
		$depthOperator = $includeParent ? '<=' : '=';
		
		return logged_query_assoc_array("
			SELECT data.*, (COUNT(parent.category_data_id) - (sub_tree.depth + 1)) AS depth
			FROM {$this->nestedCategory} AS node,
				 {$this->nestedCategory} AS parent,
				 {$this->nestedCategory} AS sub_parent,
				 {$this->categoryDataTable} AS data,
				(
					SELECT node.category_data_id, (COUNT(parent.category_data_id) - 1) AS depth
					FROM {$this->nestedCategory} AS node,
						{$this->nestedCategory} AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					  AND node.category_data_id = {$id}
					GROUP BY node.category_data_id
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			  AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
			  AND sub_parent.category_data_id = sub_tree.category_data_id
			  AND data.id = node.category_data_id
			GROUP BY node.category_data_id
			HAVING depth {$depthOperator} 1
			ORDER BY node.lft;
		",null,0,array());
	}
	/* 	
	** PURPOSE: returns info about children of node with id = $id
	**		by default, array includes the parent at first posn, 
	**		set $includeParent = false to leave the parent out of the list
	** PARMS: id (int):		id of the parent node
	**		depth (int):	if 0, shows all results
	**						otherwise, only returns categories up to that depth from the parent
	**		include..(bool):returns category for the parent passed as id if true
	** RETURN: array(
	**			catData (array of strings) includes depth level
	*/
	public function get_descendants($id = false, $depth = 0, $includeParent = true)
	{
		if ( ! is_pos_int($id) || ! is_pos_int($depth, true) ) return false;

		if($depth && $includeParent) 		$havingClause = "HAVING `depth` <= {$depth}";
		elseif($depth && ! $includeParent) 	$havingClause = "HAVING `depth` > 0 AND `depth` <= {$depth}";
		else 								$havingClause = "";

		return logged_query_assoc_array("
			SELECT data.*, (COUNT(parent.category_data_id) - (sub_tree.depth + 1)) AS depth
			FROM {$this->nestedCategory} AS node,
				 {$this->nestedCategory} AS parent,
				 {$this->nestedCategory} AS sub_parent,
				 {$this->categoryDataTable} AS data,
				(
					SELECT node.category_data_id, (COUNT(parent.category_data_id) - 1) AS depth
					FROM {$this->nestedCategory} AS node,
						{$this->nestedCategory} AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					  AND node.category_data_id = {$id}
					GROUP BY node.category_data_id
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			  AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
			  AND sub_parent.category_data_id = sub_tree.category_data_id
			  AND data.id = node.category_data_id
			GROUP BY node.category_data_id
			{$havingClause}
			ORDER BY node.lft;
		",null,0,array());
	}
	
	// returns the top level of nodes
	public function get_top_nodes()
	{
		return logged_query_assoc_array("
			SELECT data.*, (COUNT(parent.category_data_id) - 1) AS depth
			FROM {$this->nestedCategory} AS node,
				 {$this->nestedCategory} AS parent,
				 {$this->categoryDataTable} AS data
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
			  AND data.id = node.category_data_id
			GROUP BY node.category_data_id
			HAVING depth = 0
			ORDER BY node.lft;
		",null,0,array());
	}
	
	// returns category data plus depth plus product_count
	public function get_full_cat_data($spaces="-")
	{
		return logged_query_assoc_array("
			SELECT 	data.*, coalesce(product_count,0) as product_count,  (depth_info.depth) as depth, 
					CONCAT( REPEAT( '{$spaces}', ( depth ) ) , data.name ) AS spaced_name
			FROM {$this->nestedCategory} AS node
			JOIN {$this->nestedCategory} AS parent
			  ON node.lft BETWEEN parent.lft AND parent.rgt
			
			JOIN (
				SELECT node.category_data_id, (COUNT(parent.category_data_id) - 1) AS depth
				FROM {$this->nestedCategory} AS node,
					 {$this->nestedCategory} AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				GROUP BY node.category_data_id
				ORDER BY node.lft
			) AS depth_info
			  ON depth_info.category_data_id = parent.category_data_id
			
			LEFT JOIN (
				SELECT parent.category_data_id, COUNT(product.category_id) as product_count
				FROM {$this->nestedCategory} AS node ,
					 {$this->nestedCategory} AS parent,
					 {$this->productTable} AS product
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.category_data_id = product.category_id
				GROUP BY parent.category_data_id
				ORDER BY node.lft
			) as product_info
			  ON product_info.category_data_id = parent.category_data_id
			
			JOIN `{$this->categoryDataTable}` as data
			  ON data.`id` = parent.category_data_id 
			
			GROUP BY parent.category_data_id
			ORDER BY parent.lft;
		",null,0,array());
	}
	
	// 	if parentId = 0 , starting at base
	// 		otherwise, is id of last element in list
	// 	if childId is false, the parentId (the last in path) is the child
	//		else if #, add the child to the end of the path
	//		else if = 0, add a blank child selector for js to fill
	//  returns immediateParent: this is the second last element in path
	//		if child is set, this is simply the parentId
	//		otherwise, it is the second last element in path
	// 		immediateParent returns 0 if at BASE
	public function build_path($parentId, $childId = false)
	{
		// set initial value for immediateParent (this is the value if childId is set, may be overwritten later if it isn't)
		$immediateParent = $parentId ? $parentId : 0;
		
		// set base path for categories
		$hrefBase = "{$this->_config['site_path']}{$this->_config['cartName']}/category/";
		
		// find the path up to the parentId
		if (!$parentId) $path = array();
		else $path = $this->get_path($parentId);
		$numInPath = count($path);
		
		// write out the base
		echo "<span class='path_base'>{$this->base}</span>";
		
		// write out the path, up to the parentID (if childId is false, last element is the child element)
		for($i = 0; $i < $numInPath ; $i++)
		{
			// if childId isn't set, recalculate the immediateParent
			if ($childId === false && $i == $numInPath - 2 ) $immediateParent = $path[$i]['id'];

			//	set the class for the element, either parent or child, 
			//		can only be a child if childId isn't set and this id is the same as parentID (last element in the list)
			$pathClass = $childId === false && $parentId == $path[$i]['id'] ? 'path_child' : 'path_parent';
			
			
			// write out the path element
			echo "{$this->divGroup}<a class='{$pathClass}' href='{$hrefBase}{$path[$i]['url']}' rel='{$path[$i]['id']}'>{$path[$i]['name']}</a>";
		}
		// weird case:
		// if no child has been passed and the count is one, the child is on the base level
		// 	therefore the parent is 0
		if ( $childId === false && $numInPath == 1) $immediateParent = 0;
		// 	if childId isn't set, the child element has already been written(last element in path)
		//		otherwise, if childID not found: write a blank element to be filled with js later
		//							 otherwise : write the element
		if ($childId !== false)
		{
			$child = $this->get_category($childId);
			if($child)
			{
				echo "{$this->divGroup}<a class='path_child' href='{$hrefBase}{$child['url']}' rel='{$child['id']}'>{$child['name']}</a>";
			} else {
				echo "{$this->divGroup}<a class='path_child' rel='0'></a>";
			}
		}
		
		return $immediateParent;
		
	}
	
	public function build_child_list($childId, $parentId )
	{	
		$count = 0;
		$selectedSet = false;		// tracks if the selected child has been displayed

		// prepare for special case where parent isn't set(parent is base)
		if ($parentId)
		{
			$list =  $this->get_immediate_children($parentId);
		} else {
			$list = $this->get_top_nodes();
			$count++;
			echo "<div class='category_parent' rel='0'>{$this->base} <span class='forward'>{$this->divGroup}</span></div>";
		}
		
		foreach ($list as $child)
		{
			// is this the parent?
			$parent = $count++ == 0 ;
			if ($parent) echo "<div class='category_parent' rel='{$child['id']}'><span class='back'>&laquo;&nbsp; </span>{$child['name']} <span class='forward'>{$this->divGroup}</span></div>";
			else 
			{	
				//set the divider for cases where this isn't the selected element
				$unselectedDivider = $this->divGroup;
				// is this the selected child?
				$selectedClass = 'category_child';
				if ($child['id'] == $childId) 
				{
					$selectedClass =  'selected_category';
					// get rid of the divider
					$unselectedDivider = '';
					$selectedSet = true;
				}
				
				echo "<div class='{$selectedClass}' rel='{$child['id']}'>{$child['name']} <span>{$unselectedDivider}</span></div>";
			}
		}
		// if the selected child has not yet been set, it isn't part of the parent group and needs to be set explicitly
		if(! $selectedSet)
		{
			if( $selectedChild = $this->get_category($childId))
				echo "<div class='selected_category' rel='{$selectedChild['id']}'>{$selectedChild['name']}</div>";
			else
				echo "<div class='selected_category' rel='0'></div>";
			}
		
	}

	
	// ************************* placeholder functions *********************** //
	// These functions should be overwritten by a class extension and are here as patterns only
	
	//	Insert data into category data table, returns insert_id on success and false on failure 
	public function  insert_category($arCatetoryData)
	{
		
		logged_query("INSERT INTO {$this->categoryDataTable}(`name`) VALUES('{$arCatetoryData['name']}');");
		return mysql_insert_id();

	}
	

	/* **************************** Editing Functions ******************************** //
	** this function takes category information then updates/inserts the category as required
	** @parms
	**		$parentId		int		0 if base level: otherwise id of parent category
	**		$catId			int		0 if insert category: otherwise id of category to update
	**		$hasSibling 	int		0 if no siblings, 1 if siblings exist 
	**		$afterIsParent 	int		0 if the category isn't first child of parent: 1 if category is first child of parent
	**		$afterId		int		0 if category is first child of 'BASE': otherwise, id of the category 'in front' of category to insert
	**		$arCategoryData	array	key=>value pairs of data to be inserted/updated into category (as defined in the extension of this class)
	**	@return
	**		$catId			int		returns the id of the category after insert(no change if update)
	*/
	public function maintain_category($parentId, $catId, $hasSibling, $afterIsParent, $afterId, $arCategoryData)
	{
	
		// new nodes are leaf nodes, as are leaf nodes
		$catIsLeaf = ! $catId || $this->is_leaf( $catId);
		
		// 	if updating (catId > 0 ) a leaf: delete original node (need to make room for its new placement) but don't delete categoryData
		//		will reinsert the leaf later
		if ( $catId && $catIsLeaf ) $this->delete_leaf( $catId ); 
	
		// insert ( catId = 0 ) or update ( catId > 0 ) CategoryData
			// returns new catId
		if ( $catId ) 
		{
			$arCategoryData['id'] = $catId;
			$this->update_category($arCategoryData);	
		}
		else $catId = $this->insert_category($arCategoryData);	

		// now figure out what to do with the category itself
		// if the category being moved is a leaf node
		if( $catIsLeaf )
		{
			// insert if very first element ( parentId = 0,  afterIsParent = 1 )
			if ( ! $parentId && $afterIsParent ) { $this->add_first_category($catId);  }
			// otherwise insert if first child 
			elseif ($afterIsParent ) { $this->add_first_child_category($catId, $afterId);  }
			// else insert after the preceding sibling category 
			else { $this->add_category_after($catId, $afterId);	  }
			// return the catId
		} else { // otherwise, we are moving an entire node (branch): do the much more expensive move
			// insert if very first element ( parentId = 0,  afterIsParent = 1 )
			if ( ! $parentId && $afterIsParent ) {$this->move_node_to_first($catId);}
			// otherwise insert if first child
			elseif ( $afterIsParent ) {$this->move_node_to_leaf($catId, $parentId);}
			// else, if there is a sibling in front of it , put it after the sibling
			else {$this->move_node_to_after_sibling($catId, $afterId) ;}
		}
		
		return $catId;
	}
	
	// takes a dirtyId, cleans it then performs a delete
	//   the delete shifts all children up a level
	// returns number of deleted records: 0 if none
	// $extraOrphans can be an array with 
	//		'table' = name of additional Table to cleanup (ie revisions table)
	//		'id'	= name of corresponding id Field
	public function delete_shift_up( $dirtyId, $extraOrphans = false )
	{
			$numDeleted = 0;
			if( is_pos_int($dirtyId) && $this->node_exists($dirtyId) )
			{
				$cleanId = $dirtyId;
				
				if( $this->is_leaf($cleanId) ) $numDeleted = $this->delete_leaf($cleanId);
				else $numDeleted = $this->delete_node_only($cleanId);
			}
			// now clean up the orphaned data files
			$this->delete_orphans();
			if( is_array($extraOrphans) )
			{
				$this->delete_orphans($extraOrphans['table'], $extraOrphans['id']);
			}
			
			return $numDeleted;
	}
	
// ************************** helper functions	************************** //
	
	//	either we are adding a node to the very beginning of the table or it is the very first entry
	protected function add_first_category($first_category)
	{
		$this->lock_table();
		
		// first update all existig lft/rght, increasing by two
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt + 2",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft + 2",0,array());
		
		// insert the new category
		logged_query("INSERT INTO {$this->nestedCategory}(`category_data_id`, `lft`, `rgt`) VALUES('{$first_category}', 1, 2);",0,array());
		
		$this->unlock_tables();	
	}
	
	// ADD LEAF
	
	//	adds new category after specified category
	//		(adds second and later nodes, not first)
	protected function add_category_after($new_category, $after_category)
	{
		$this->lock_table();
		
		$query = "
			SELECT @myRight := rgt FROM {$this->nestedCategory}
			WHERE category_data_id = '{$after_category}';
		";
		logged_query($query,0,array());
		
		$query = "
			UPDATE {$this->nestedCategory} SET rgt = rgt + 2 WHERE rgt > @myRight;
		";
		logged_query($query,0,array());
		
		$query = "
			UPDATE {$this->nestedCategory} SET lft = lft + 2 WHERE lft > @myRight;
		";
		logged_query($query,0,array());
	
		$query = "
			INSERT INTO {$this->nestedCategory}(category_data_id, lft, rgt) VALUES('{$new_category}', @myRight + 1, @myRight + 2);
		";
		logged_query($query,0,array());
		
		$this->unlock_tables();		
	}

	// adds the first child of a node
	protected function add_first_child_category($newCategory, $parentCategory)
	{
		$this->lock_table();
		
		$query = "
			SELECT @myLeft := lft FROM {$this->nestedCategory}
			WHERE category_data_id = '{$parentCategory}';
		";
		logged_query($query,0,array());
		
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt + 2 WHERE rgt > @myLeft;",0,array());
		
		$query = "
			UPDATE {$this->nestedCategory} SET lft = lft + 2 WHERE lft > @myLeft;
		";
		logged_query($query,0,array());
	
		$query = "
			INSERT INTO {$this->nestedCategory}(category_data_id, lft, rgt) VALUES('{$newCategory}', @myLeft + 1, @myLeft + 2);
		";
		logged_query($query,0,array());
		
		$this->unlock_tables();		
	}
	
	// MOVE NODES (move the node + children: the entire branch)
	
	// move node (entire branch) to very first position
	protected function move_node_to_first($node)
	{
		// first, lock tables
		logged_query("LOCK TABLES {$this->nestedCategory} WRITE, {$this->tmpCategoryTable} WRITE",0,array());
 
		// get the parameters for the sub tree to move 
		logged_query("
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1, @catId :=  category_data_id
			FROM {$this->nestedCategory}
			WHERE  category_data_id = '{$node}';
		",0,array());
		
		// get the offset to renumber the subtree lefts and rights 
		logged_query("SELECT @step := 1 - @myLeft;",0,array());
		 
		// transfer the subtree to a temp table 
		logged_query("
			INSERT {$this->tmpCategoryTable}
			SELECT * FROM {$this->nestedCategory}
			WHERE lft >= @myLeft AND lft <= @myRight;
		",0,array());
		
		// update the temp table - renumber the lefts and rights and make the catId neg temporarily 
		logged_query("
			UPDATE {$this->tmpCategoryTable}
			SET lft = lft + @step, 
				rgt = rgt + @step,
				category_data_id = - category_data_id;
		",0,array()); 
		 
		// update the rest of the tree to the right of the move point
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft + @myWidth ;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt + @myWidth ;",0,array());
		 
		// insert the sub tree in the temp table 
		logged_query("
			INSERT {$this->nestedCategory}
			SELECT * FROM {$this->tmpCategoryTable};
		",0,array()); 
		// delete the original subtree 
		logged_query("
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
			FROM {$this->nestedCategory}
			WHERE  category_data_id = @catId;
		",0,array());
		logged_query("DELETE FROM {$this->nestedCategory} WHERE lft BETWEEN @myLeft AND @myRight; ",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt - @myWidth WHERE rgt > @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft - @myWidth WHERE lft > @myRight;",0,array());
		 
		// reset neg. catId's to pos. and clean up {$this->tmpCategoryTable}
		logged_query("UPDATE {$this->nestedCategory} SET  category_data_id = - category_data_id WHERE  category_data_id < 0;",0,array());
		logged_query("DELETE FROM {$this->tmpCategoryTable};",0,array());
		
		$this->unlock_tables();
	}
	
	// moves node (entire branch) to parent that has no other children
	protected function move_node_to_leaf($node, $parentNode)
	{
		// Move Node to child of target node 
 
		logged_query("LOCK TABLES {$this->nestedCategory} WRITE, {$this->tmpCategoryTable} WRITE",0,array());
		
		
		// get the parameters for the sub tree to move 
		logged_query("
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1, @catId :=  category_data_id
			FROM {$this->nestedCategory}
			WHERE  category_data_id = '{$node}';
		",0,array());
		
		// get the lft and rgt value of the new parent cat 
		logged_query("
			SELECT @insLft := lft, @insRgt := rgt 
			FROM {$this->nestedCategory} 
			WHERE  category_data_id = '{$parentNode}';
		",0,array());
		
		// get the offset to renumber the subtree lefts and rights 
		logged_query("SELECT @step := @insLft - @myLeft + 1;",0,array());
		
		// transfer the subtree to a temp table 
		logged_query("
			INSERT {$this->tmpCategoryTable}
			SELECT * FROM {$this->nestedCategory}
			WHERE lft >= @myLeft 
			  AND lft <= @myRight;
		",0,array());
		
		// update the temp table - renumber the lefts and rights and make the catId neg temporarily 
		logged_query("
			UPDATE {$this->tmpCategoryTable}
			SET lft = lft + @step, 
				rgt = rgt + @step,
				category_data_id = - category_data_id;
		",0,array());
		
		// update the rest of the tree to the right of the move point
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft + @myWidth WHERE lft > @insLft;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt + @myWidth WHERE rgt >= @insLft;",0,array());
		
		
		// insert the sub tree in the temp table 
		logged_query("
			INSERT {$this->nestedCategory}
			SELECT * FROM {$this->tmpCategoryTable};
		",0,array());
		
		// delete the original subtree 
		logged_query("
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
			FROM {$this->nestedCategory}
			WHERE  category_data_id = @catId;
		",0,array());
		logged_query("DELETE FROM {$this->nestedCategory} WHERE lft BETWEEN @myLeft AND @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt - @myWidth WHERE rgt > @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft - @myWidth WHERE lft > @myRight;",0,array());
		
		// reset neg. catId's to pos. and clean up tmp category
		logged_query("UPDATE {$this->nestedCategory} SET  category_data_id = - category_data_id WHERE  category_data_id < 0;",0,array());
		logged_query("DELETE FROM {$this->tmpCategoryTable};",0,array());
				
		$this->unlock_tables();		
	}
	
	protected function move_node_to_after_sibling($node, $siblingNode)
	{
		// Move Node as sibling of target node (after node) 
		
		// first, lock tables
		logged_query("LOCK TABLES {$this->nestedCategory} WRITE, {$this->tmpCategoryTable} WRITE",0,array());

		// get the parameters for the sub tree to move 
		logged_query("
		SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1, @catId := category_data_id
		FROM {$this->nestedCategory}
		WHERE category_data_id = '{$node}';
		",0,array());
		
		// get the rgt value of the node to move next to
		logged_query("SELECT @insRgt := rgt FROM {$this->nestedCategory} WHERE category_data_id = '{$siblingNode}';",0,array());
		 
		// get the offset to renumber the subtree lefts and rights 
		logged_query("SELECT @step := @insRgt - @myLeft + 1;",0,array());
		 
		// transfer the subtree to a temp table 
		logged_query("
		INSERT {$this->tmpCategoryTable}
		SELECT * FROM {$this->nestedCategory}
		WHERE lft >= @myLeft AND lft <= @myRight;
		",0,array());
		
		// update the temp table - renumber the lefts and rights and make the catId neg temporarily
		logged_query("
		UPDATE {$this->tmpCategoryTable}
		SET lft = lft + @step, 
		rgt = rgt + @step,
		category_data_id = -category_data_id;
		",0,array());
		
		// update the rest of the tree to the right of the move point
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft + @myWidth WHERE lft > @insRgt;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt + @myWidth WHERE rgt > @insRgt;",0,array());
		 
		// bring the sub tree in the temp table back to the main table 
		logged_query("
		INSERT {$this->nestedCategory}
		SELECT * FROM {$this->tmpCategoryTable};
		",0,array());
		
		// delete the original subtree 
		logged_query("SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
		FROM {$this->nestedCategory}
		WHERE category_data_id = @catId;
		",0,array());
		
		logged_query("DELETE FROM {$this->nestedCategory} WHERE lft BETWEEN @myLeft AND @myRight; ",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt - @myWidth WHERE rgt > @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft - @myWidth WHERE lft > @myRight;",0,array());
		 
		// reset neg. catId's to pos. and clean up {$this->tmpCategoryTable}
		logged_query("UPDATE {$this->nestedCategory} SET category_data_id = -category_data_id WHERE category_data_id < 0;",0,array());
		logged_query("DELETE FROM {$this->tmpCategoryTable};",0,array());
		
		$this->unlock_tables();		
	}	
	
	// DELETE
	
	// 	deletes the item if it is a last leaf, 
	//		caller must guarantee it is last leaf before entering function
	
	protected function delete_leaf($leaf)
	{
		$this->lock_table();
		
		$query = "
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
			FROM {$this->nestedCategory}
			WHERE category_data_id = '{$leaf}';
		";
		logged_query($query,0,array());
		
		$query = "
			DELETE FROM {$this->nestedCategory} WHERE lft BETWEEN @myLeft AND @myRight;
		";
		$result = logged_query($query,0,array());
		
		$deleteSuccess = $result !== false ? 1 : 0;
		
		$query = "
			UPDATE {$this->nestedCategory} SET rgt = rgt - @myWidth WHERE rgt > @myRight;
		";
		logged_query($query,0,array());
	
		$query = "
			UPDATE {$this->nestedCategory} SET lft = lft - @myWidth WHERE lft > @myRight;
		";
		logged_query($query,0,array());
		
		$this->unlock_tables();
		
		return $deleteSuccess;
	}
	
	// delete a node and all its children
	protected function delete_node_all($node)
	{
		$this->lock_table();
		
		$query = "
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
			FROM {$this->nestedCategory}
			WHERE category_data_id = '$node';
		";
		logged_query($query,0,array());
		
		$query = "
			DELETE FROM {$this->nestedCategory} WHERE lft BETWEEN @myLeft AND @myRight;
		";
		logged_query($query,0,array());
		
		$query = "
			UPDATE {$this->nestedCategory} SET rgt = rgt - @myWidth WHERE rgt > @myRight;
		";
		logged_query($query,0,array());
	
		$query = "
			UPDATE {$this->nestedCategory} SET lft = lft - @myWidth WHERE lft > @myRight;
		";
		logged_query($query,0,array());
		
		$this->unlock_tables();
	}
	
	// delete a node and move the children up 
	// returns false if no files deleted
	// otherwise, returns the number of deleted files
	protected function delete_node_only($node)
	{
		$this->lock_table();
		
		logged_query("
			SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
			FROM {$this->nestedCategory}
			WHERE category_data_id = '{$node}';
		",0,array());
		
		$result = logged_query("DELETE FROM {$this->nestedCategory} WHERE lft = @myLeft;",0,array());
		
		$numDeleted = $result;
		$deleteSuccess = $numDeleted !== false ? $numDeleted : 0;
		
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt - 1, lft = lft - 1 WHERE lft BETWEEN @myLeft AND @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET rgt = rgt - 2 WHERE rgt > @myRight;",0,array());
		logged_query("UPDATE {$this->nestedCategory} SET lft = lft - 2 WHERE lft > @myRight;",0,array());
		
		$this->unlock_tables();
		
		return $deleteSuccess;
	}
	
	// node deletes only get rid of the nestedCategory references to the actual data field
	//	this function runs the cleanup, any orphaned data files are deleted
	
	// By default: clears orphans from the data table. In many cases (backup records) there may be orphaned files in other tables
	//		$table is the Table name; $id is the corresponding Field name
	protected function delete_orphans($table = false, $id = 'id')
	{
		$table = $table ? $table : $this->categoryDataTable;
		logged_query("
			DELETE FROM `{$table}` 
			WHERE `{$id}` NOT IN (
				SELECT DISTINCT `category_data_id`
				FROM `{$this->nestedCategory}`
			) 
		",0,array());
	}


	
	// returns true if is a leaf node (has no children)
	// assumes node exists
	public function is_leaf($id)
	{
		$result = logged_query_assoc_array("
			SELECT *
			FROM {$this->nestedCategory}
			WHERE rgt = lft + 1
			AND `category_data_id` = {$id};
		",null,0,array());
		return (count($result)>0);
	}
	
	public function node_exists($node)
	{
		$result = logged_query_assoc_array("
			SELECT *
			FROM {$this->nestedCategory}
			WHERE `category_data_id` = {$node};
		",null,0,array());
		return (count($result)>0);
	}
	
	// lock the table for block execution
	private function lock_table($table = false)
	{
		if (! $table) $table = $this->nestedCategory;
		$query = "
			LOCK TABLE {$table} WRITE
		";
		$result = logged_query($query,0,array());
		return;
	}
	
	// unlock all tables 
	private function unlock_tables()
	{
		$query = "
			UNLOCK TABLES;
		";
		$result = logged_query($query,0,array());
		return;
	}
	
}