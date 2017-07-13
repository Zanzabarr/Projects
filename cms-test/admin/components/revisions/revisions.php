<?php
class Revisions {

	const REV_ID			=	'revise_id';// the revision table's id is POST['revise_id']
	
	private $page_id		=	false;		// id of the current page/blog post/blog category/...
	private $page_id_name	= 	false;		// name of the source id in the revision table, eg
											// pages table `id` is `page_id` in revisions
											// in the blog, it has been recorded as `post_id`
											
	private $table_name		=	false;		// name of the revision table: pages: revision, blog: blog_revision...
	
	private $revise_id		= 	false;		// the revision table id number for the selected id during revision
	private $message 		=	false;		// error/success message array
	private $extra_POST		= 	array();	// key/value pairs to be passed as POST data along with the data from the revision table
	private $extra_GET		= 	array(); 	// key/value pairs to be passed as GET data along with the data from the revision table
											// if not set, will use # to return to same page with same get data
	private $obj_select		= 	false;		// instance of inputField class: used to build the select input
	private $obj_date 		= '';			// instance of My_Date class: ensures uniform date representation
	
	private $preview_href	= 	false;		// if this data allows previews, this is the href for the preview
	private $preview_rel	=	false;		//    	and this is the name of the table element used in the href to identify the selected revision
											//		eg (pages uses:
											//			$preview_href = 'site_path/admin/view_page.php?page=
											//			$preview_rel  = 'slug'
											//      to create: (for the page 'about-dogs')
											//		'site_path/admin/view_page.php?page=about-dogs'
	private $order_by_field	= 	'date';
	
	public function __construct($table_name, $page_id_name, $preview_href = false, $preview_rel = false )
    {
		$this->preview_href =	$preview_href;
		$this->preview_rel  =	$preview_rel;
		$this->table_name	= 	$table_name;
		$this->page_id_name	= 	$page_id_name;
		$this->obj_date = new My_Date();
	}
	
	// change the order by field from the default: date
	// used to display revisions chronolgically. Pages and other older versions have a date field.
	//	but now we want a date_created, date_updated field
	public function setOrderByField($field)
	{
		$this->order_by_field = $field;
	}
	
	// build and return the select button (can be called without createRevisionsArea to do a custom design)
	// assumes page_id has been set
	// $message is an array of error/success data formatted for use with inputField 
	private function prepareInputField()
	{
		if(! $this->page_id) return false;
		// get all revisions for this page, drop out if there are none
		$query = "SELECT * FROM {$this->table_name} WHERE {$this->page_id_name} = :page_id ORDER BY {$this->order_by_field} DESC";
		$arRevisions = logged_query($query,0,array(
			":page_id" => $this->page_id
		));

		$numRevs = count($arRevisions);
		if($numRevs <= 1) return false;

		// build the select
		$this->obj_select 	= new inputField( 'Version Date', self::REV_ID );	
		$this->obj_select->toolTip("Restore a previous version.<br />Note: unsaved changes will be lost." );
		$this->obj_select->type('select');
		if($this->message !== false)$this->obj_select->arErr($this->message);

		// build select options, outputting the timezone
		$count = 0;
		foreach ( $arRevisions as $revision ) { 
			// the first record is the current version, don't include it
			if ($count++ != 0)
			{
				$date_time = $this->obj_date->makeDate($revision[$this->order_by_field]);
				// set up optional preview data
				$preview_data = $this->preview_href && $this->preview_rel ?  array ('rel' => $this->preview_href . $revision[$this->preview_rel] ) : array();

				$this->obj_select->option(  $revision['id'], $date_time , $preview_data);
			}
		}

	}
	
	private function setPageId ($page_id)
	{
		$this->page_id = $page_id;
	}
	
	private function setRevisionId ()
	{
		if ($this->isRevision()) $this->revise_id = $_POST[self::REV_ID];
		else 
		{
			my_log('Could not set revision id to get revision data');
			return false;
		}
	}
	
	private function setExtraPOST($data = array() )
	{
		if (! is_array($data) )
		{
			$data=array();
			$my_log('Revisions::instance->setExtraPOST($data): $data must be an array of key value pairs to be passed as POST data along with the normal revision data');
		}
		else $this->extra_POST = $data;
	}
	
	private function setGET($data = array() )
	{
		if (! is_array($data) )
		{
			$parms='';
			$my_log('Revisions::instance->setGET($data): $data must be an array of key value pairs to be passed as GET data instead of using # to send the current page and GET parms');
		}
		else 
		{
			if (count($data) > 0)
			{
				// build Get parameters
				
				$parms = "?".key($data).'='.array_shift($data);
				
				foreach($data as $key => $value)
				{
					$parms .= '&'.$key.'='.$value; 
				}
			}
			else $parms = '';
			$this->extra_GET = $parms;
		}
	}
	
	public function isRevision()
	{
		if (array_key_exists(self::REV_ID, $_POST) && is_numeric($_POST[self::REV_ID])) return true;
		return false;
	}
	public function getRevisionData($page_id)
	{
		$this->setPageId($page_id);
		$result = $this->createRevisionData();
		if ($result === false)
		{
			$this->message['banner'] = array (
				'heading' => 'Failed to Load Revision', 
				'type' => 'error', 
				'message' => "Using current instead."
			);
		}
		else
		{
			$this->message['banner'] = array (
				'heading' => 'Using Revision', 
				'type' => 'success', 
				'message' => $this->obj_date->makeDate($result[$this->order_by_field]) . "&nbsp;&nbsp;&nbsp;&nbsp; Revision not permanent until saved."
			);
		}
		return $result;
	}
	
	public function getResultMessage()
	{
		if ($this->message === false) 
		{
			my_log('Result message has not been set: Make sure revisions::getRevisionData has already been called. It generates the message.');
			return false;
		}
		return $this->message;
	}
	
	private function createRevisionData()
	{
		if ($this->setRevisionId() === false) return false;
		
		// get the revision array data 
		// it should have all the data fields from the original plus revision:id, revision:date (unless exceptions are declared)
		$query = "SELECT * FROM `{$this->table_name}` WHERE id =:revise_id";
		$data = logged_query($query,0,array(":revise_id" => $this->revise_id ));
		if(!isset($data[0])) return false;
		$data = $data[0];
		
		// get all POST data from the revision call (it will overwrite any revise table data that shares it's name!)
		foreach($_POST as $key => $value)
		{	
			if (!is_numeric($key))	$key = $key;
			if (!is_numeric($value))	$value = $value;
			$data[$key] = $value;
		}
		
		// get all GET data and store it in 2nd dimension: GET
		
		foreach ($_GET as $key => $value)
		{
			if (!is_numeric($key))	$key = $key;
			if (!is_numeric($value))	$value = $value;		
			$data['GET'][$key] = $value;
		}
		
		// the id from the query is the revision table's id, not the original table's, use the id passed as the actual id
		$data['id'] = $this->page_id;
		return $data;
	}
	
	// build the Revisions area and return the Revisions input field
	public function createRevisionsArea($pageId, $extraPost = array(), $extraGet = array())
	{
		$this->setPageId($pageId);
		$this->setExtraPost($extraPost);
		$this->setGET($extraGet);
		
		//if(!is_numeric($this->page_id)) my_log($this->page_id_name.':'.$this->page_id.' must be a number');
		
		echo "<hr /><input type='hidden' id='tname' value='{$this->table_name}' />";
		
		if($this->prepareInputField($this->message) === false )
		{
			echo '<div class="clearFix"></div><h2 class="tiptip toggle" id="version-toggle" style="color: grey;" title="There are no saved versions for this page." >Previous Versions</h2>';
		}
		else // build the revisions area 
		{
		if (true) : 
		?>
<h2 id="version-toggle" class="tiptip toggle" title="Go back to previously saved versions of the page.<br />Note: the reverted version will only become permanent if saved">Previous Versions</h2><br />
<div id= 'version-toggle-wrap'>
	<form action="<?php echo $_SERVER['PHP_SELF'].$this->extra_GET; ?>" method="post" enctype="application/x-www-form-urlencoded" id="version-form" name="form-version" >
		
		<?php // create hidden inputs for extra post data
			foreach($this->extra_POST as $postName => $postVal) : ?>
			
			<input type="hidden" name="<?php echo $postName; ?>" value="<?php echo $postVal; ?>" />
			
			<?php
			endforeach;
		?>

		<?php $this->obj_select->createInputField(); ?>
	
		<div class='clearFix' ></div>
		
		<?php // show the preview button if this is pages section
		if ($this->preview_href && $this->preview_rel) :
		?>
		<a class="blue button" id="preview" target="_blank" href="#">Preview</a>
		<?php endif; ?>
		
		<a class="blue button" id="revert" href="#">Revert</a>
		<a class="red button" id="del-version" href="#" >Delete</a>
		<div class="clearFix"></div>
	</form>				
</div>			
		<?php
		endif;
		}
	}

	// public version so the input field can be placed inside a custom built Version area
	public function createInputField()
	{
	//	if(!is_numeric($this->page_id)) my_log($this->page_id_name.':'.$this->page_id.' must be a number');
		if( $this->prepareInputField($this->message) === false) return false;
		
		// echo out the input
		$this->obj_select->createInputField();
	}
}	