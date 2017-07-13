<?php


/*                
$tbl_name="";		//your table name	
// How many adjacent pages should be shown on each side?
	$adjacents = 3;
		$targetpage = "filename.php"; 	//your file name  (the name of this file)
			$limit = 2; 								//how many items to show per page
	$whereClause  // limits on tbl_name select
	$funcName = 'buildComments'
	$arFuncParms = array($options)
*/
class paginationClass {

	private $pageForms;
	private $pagination;
	private $uripagination;
	public	$result;
	private	$arFuncParms;
	private	$funcName;	
	
	public function __construct($tbl_name = '',$whereClause='', $adjacents = 3, 
							$targetpage='',$limit = 5,
							$funcName = '',
							$arFuncParms = array(),
							$arWhereBindings = false
							)
	{
	
		global $_config;
		if 	(
			!is_array($arWhereBindings) && 
			isset($_config['troubleshootdb']) && 
			$_config['troubleshootdb']
		) 
		{
			throw new Exception('paginationclass now takes new parameters');
			my_log('paginationclass now takes a new parameter: last parm is an array of bindings associated with $whereClause');
		}
		
		// if limit is 0 or a negative number, limit is effectively unlimited
		$limit = $limit <= 0 ? 500000 : $limit;
		/* 
		   First get total number of rows in data table. 
		   If you have a WHERE clause in your query, make sure you mirror it here.
		*/
		$query = "SELECT COUNT(*) as num FROM `{$tbl_name}` {$whereClause}";
		$total_pages = logged_query($query,0,$arWhereBindings);
		$total_pages = $total_pages[0]['num'];
	
		/* Setup vars for query. */
		$page = isset($_POST['part']) && is_web_int($_POST['part']) ? $_POST['part'] : false;
		$page = uri::get('page');
		if($page) 
			$start = ($page - 1) * $limit; 			//first item to display on this page
		else
			$start = 0;								//if no page var is given, set start to 0
		
		/* Get data. */
        if($limit == -1) {
            // display all set
            $sql = "SELECT * FROM `{$tbl_name}` {$whereClause} ";
        }
        else {
            $sql = "SELECT * FROM `{$tbl_name}` {$whereClause} LIMIT {$start}, {$limit}";
        }

		$result = logged_query($sql,0,$arWhereBindings);

		/* Setup page vars for display. */
		if ($page == 0) $page = 1;					//if no page var is given, default to 1.
		$prev = $page - 1;							//previous page is page - 1
		$next = $page + 1;							//next page is page + 1
		$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;						//last page minus 1
	

		/* 
			Now we apply our rules and draw the pagination object. 
			We're actually saving the code to a variable in case we want to draw it more than once.
		*/
		$pagination = "";
		$uripagination = "";
		$pageForms = "";
		
		if($lastpage > 1)
		{	
			$pagination .= "<div class=\"pagination\">";
			$uripagination .= "<div class=\"pagination\">";
			$pageForms .= "<div class=\"pagination\">";
			//previous button
			$disabled = false;
			if ($page > 1) 
			{
				
				$pagination.= "<a href=\"$targetpage?part=$prev\">&laquo; previous</a>";
				$uripagination .= "<a href=\"$targetpage/page/$prev\">&laquo; previous</a>";
			}
			else
			{
				$pagination.= "<span class=\"disabled\">&laquo; previous</span>";	
				$uripagination .= "<span class=\"disabled\">&laquo; previous</span>";	
				$disabled = true;
			}	
			$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
			$pageForms .= "<input type='hidden' name='part' value='{$prev}' />";
			$pageForms .= "<input type='submit' value='&laquo; previous' class='pagButton ";
			$pageForms .= $disabled ? "disabled' disabled='disabled'" : "'";
			$pageForms .= "/>";
			$pageForms .= "</form>";			
			
			//pages	
			if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
			{	
				for ($counter = 1; $counter <= $lastpage; $counter++)
				{
					$current = false;
					if ($counter == $page)
					{
						$pagination.= "<span class=\"current\">$counter</span>";
						$uripagination .= "<span class=\"current\">$counter</span>";
						$current = true;
					}
					else
					{
						$pagination.= "<a href=\"$targetpage?part=$counter\">$counter</a>";	
						$uripagination.= "<a href=\"$targetpage/page/$counter\">$counter</a>";	
					}
					
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='{$counter}' />";
					$pageForms .= "<input type='submit' value='{$counter}' class='pagButton ";
					$pageForms .= $current ? "current' disabled='disabled'" : "'";
					$pageForms .= "/>";
					$pageForms .= "</form>";
						
				}
			}
			elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
			{
				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2))		
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
					{
						$current=false;
						if ($counter == $page)
						{
							$pagination.= "<span class=\"current\">$counter</span>";
							$uripagination.= $pagination;
							$current=true;
						}
						else
						{
							$pagination.= "<a href=\"{$targetpage}?part=$counter\">$counter</a>";	
							$uripagination.="<a href=\"{$targetpage}/part/$counter\">$counter</a>";	
						}
							
						$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
						$pageForms .= "<input type='hidden' name='part' value='{$counter}' />";
						$pageForms .= "<input type='submit' value='{$counter}' class='pagButton ";
						$pageForms .= $current ? "current' disabled='disabled'" : "'";
						$pageForms .= "/>";
						$pageForms .= "</form>";							
					}
					$pagination.= "...";
					$pagination.= "<a href=\"$targetpage?part=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetpage?part=$lastpage\">$lastpage</a>";	
					
					$uripagination.= "...";
					$uripagination.= "<a href=\"$targetpage/page/$lpm1\">$lpm1</a>";
					$uripagination.= "<a href=\"$targetpage/page/$lastpage\">$lastpage</a>";					
					
					$pageForms .= "<input type='submit'  value='...' class='pagButton dots disabled' disabled='disabled' />";
					
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='{$lpm1}' />";
					$pageForms .= "<input type='submit' value='{$lpm1}' class='pagButton' />";
					$pageForms .= "</form>";
						
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='{$lastpage}' />";
					$pageForms .= "<input type='submit' value='{$lastpage}' class='pagButton' />";
					$pageForms .= "</form>";
				}
				//in middle; hide some front and some back
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
				{
					$pagination.= "<a href=\"$targetpage?part=1\">1</a>";
					$pagination.= "<a href=\"$targetpage?part=2\">2</a>";
					$pagination.= "...";
					
					$uripagination.= "<a href=\"$targetpage/page/1\">1</a>";
					$uripagination.= "<a href=\"$targetpage/page/2\">2</a>";
					$uripagination.= "...";
					
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='1' />";
					$pageForms .= "<input type='submit' value='1' class='pagButton' />";
					$pageForms .= "</form>";
						
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='2' />";
					$pageForms .= "<input type='submit' value='2' class='pagButton' />";
					$pageForms .= "</form>";
					
					$pageForms .= "<input type='submit'  value='...' class='pagButton dots disabled' disabled='disabled' />";
					
					$current=false;
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
					{
						$current = false;
						if ($counter == $page)
						{
							$pagination.= "<span class=\"current\">$counter</span>";
							$uripagination.= "<span class=\"current\">$counter</span>";
							$current = true;
						}
						else
						{
							$pagination.= "<a href=\"$targetpage?part=$counter\">$counter</a>";	
							$uripagination.= "<a href=\"$targetpage/page/$counter\">$counter</a>";	
						}
							
						$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
						$pageForms .= "<input type='hidden' name='part' value='{$counter}' />";
						$pageForms .= "<input type='submit' value='{$counter}' class='pagButton ";
						$pageForms .= $current ? "current' disabled='disabled'" : "'";
						$pageForms .= "/>";
						$pageForms .= "</form>";
					}

					$pagination.= "...";
					$pagination.= "<a href=\"$targetpage?part=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetpage?part=$lastpage\">$lastpage</a>";		
					
					$uripagination.= "...";
					$uripagination.= "<a href=\"$targetpage/page/$lpm1\">$lpm1</a>";
					$uripagination.= "<a href=\"$targetpage/page/$lastpage\">$lastpage</a>";	
					
					$pageForms .= "<input type='submit'  value='...' class='pagButton dots disabled' disabled='disabled' />";
					
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='{$lpm1}' />";
					$pageForms .= "<input type='submit' value='{$lpm1}' class='pagButton' />";
					$pageForms .= "</form>";
						
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='{$lastpage}' />";
					$pageForms .= "<input type='submit' value='{$lastpage}' class='pagButton' />";
					$pageForms .= "</form>";					
					
				}
				//close to end; only hide early pages
				else
				{
					$pagination.= "<a href=\"$targetpage?part=1\">1</a>";
					$pagination.= "<a href=\"$targetpage?part=2\">2</a>";
					$pagination.= "...";

					$uripagination.= "<a href=\"$targetpage/part/1\">1</a>";
					$uripagination.= "<a href=\"$targetpage/part/2\">2</a>";
					$uripagination.= "...";
					
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='1' />";
					$pageForms .= "<input type='submit' value='1' class='pagButton' />";
					$pageForms .= "</form>";
						
					$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
					$pageForms .= "<input type='hidden' name='part' value='2' />";
					$pageForms .= "<input type='submit' value='2' class='pagButton' />";
					$pageForms .= "</form>";
					
					$pageForms .= "<input type='submit'  value='...' class='pagButton dots disabled' disabled='disabled' />";
					
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
						$current = false;
						if ($counter == $page)
						{
							$pagination.= "<span class=\"current\">$counter</span>";
							$uripagination.= "<span class=\"current\">$counter</span>";
							$current = true;
						}
						else
						{
							$pagination.= "<a href=\"$targetpage?part=$counter\">$counter</a>";	
							$uripagination.= "<a href=\"$targetpage/page/$counter\">$counter</a>";	
						}
						
						$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
						$pageForms .= "<input type='hidden' name='part' value='{$counter}' />";
						$pageForms .= "<input type='submit' value='{$counter}' class='pagButton ";
						$pageForms .= $current ? "current' disabled='disabled'" : "'";
						$pageForms .= "/>";
						$pageForms .= "</form>";				
					}
				}
			}
			
			//next button
			$disabled = false;
			if ($page < $counter - 1) 
			{
				$pagination.= "<a href=\"$targetpage?part=$next\">next &raquo;</a>";
				$uripagination.= "<a href=\"$targetpage/page/$next\">next &raquo;</a>";
			}
			else
			{
				$pagination.= "<span class=\"disabled\">next &raquo;</span>";
				$uripagination.= "<span class=\"disabled\">next &raquo;</span>";
				$disabled = true;
			}
			$pageForms .= "<form action='{$targetpage}' method='post' enctype='application/x-www-form-urlencoded' >";
			$pageForms .= "<input type='hidden' name='part' value='{$next}' />";
			$pageForms .= "<input type='submit' value='next &raquo;' class='pagButton ";
			$pageForms .= $disabled ? "disabled' disabled='disabled'" : "'";
			$pageForms .= "/>";
			$pageForms .= "</form>";	

			
			$pageForms .= "</div><div style='clear:both'></div>\n";	
			$pagination.= "</div>\n";		
			$uripagination.= "</div>\n";	
		}
		$this->pageForms = $pageForms;
		$this->pagination = $pagination;
		$this->uripagination = $uripagination;
		$this->result = $result;
		$this->arFuncParms = $arFuncParms;
		$this->funcName = $funcName;
	}
	
	public function showResults()
	{
			if($this->result) : foreach($this->result as $row)
			{	

				$tmpParm = $this->arFuncParms;
				array_unshift($tmpParm,$row);
				call_user_func_array($this->funcName,$tmpParm);
		
			} endif;
		
	}
	
	public function paginate()
	{
		return $this->pagination;
	}	
	
	public function uriPaginate()
	{
		return $this->uripagination;
	}
	
	public function pageForms()
	{
		return $this->pageForms;
	}
	
}
