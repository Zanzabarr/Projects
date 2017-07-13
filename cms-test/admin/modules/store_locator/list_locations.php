<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'store_locator';
include('../../includes/headerClass.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

$locResources = "
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/store_locator/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/store_locator/js/list_locations.js\"></script>
";
// set the header variables and create the header
$pageInit->createPageTop($locResources);

	if (isset($_GET['removeid'])) {
		logged_query("DELETE FROM markers WHERE id = :markid",0,array(':markid' => filter_var(trim($_GET['removeid']),FILTER_SANITIZE_STRING)));
	}
	
?>
<div class="page_container">
	<div id="h1"><h1>Store Locations</h1></div>
    
    <div id="info_container">
    	<div id="store_location">
            <a href="add_location.php?action=add"><img src="../../images/add_store.jpg" /></a>
        </div>
    </div>
    <br />
    <div id="info_container" style="padding:5px 0 0 0;">
    	<div id="store_location">
            <div class="pagination">
            
            <?php
                $tableName="markers";
                $targetpage = "list_locations.php";
                $limit = 100;
                
                $total_pages = $_config['db']->count("SELECT * from $tableName");
                
                $stages = 3;
				if(isset($_GET['page'])) {
					$page = filter_var(trim($_GET['page']),FILTER_SANITIZE_STRING);
				} else {
					$page = 1;
				}
                if($page){
                    $start = ($page - 1) * $limit;
                } else {
                    $start = 0;
                }	
                
                // Get page data
                $query1 = "SELECT * FROM $tableName ORDER BY name ASC LIMIT $start, $limit ";
                $result = logged_query($query1,0,array());
                
                // Initial page num setup
                if ($page == 0){$page = 1;}
                $prev = $page - 1;
                $next = $page + 1;
                $lastpage = ceil($total_pages/$limit);
                $LastPagem1 = $lastpage - 1;
                
                $paginate = '';
                if($lastpage > 1)
                {
                    $paginate .= "<div class='paginate'>";
                    // Previous
                    if ($page > 1){
                        $paginate.= "<a href='$targetpage?page=$prev'>previous</a>";
                    }else{
                        $paginate.= "<span class='disabled'>previous</span>";	}
                        
                    // Pages	
                    if ($lastpage < 7 + ($stages * 2))	// Not enough pages to breaking it up
                    {	
                        for ($counter = 1; $counter <= $lastpage; $counter++)
                        {
                            if ($counter == $page){
                                $paginate.= "<span class='current'>$counter</span>";
                            }else{
                                $paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
                        }
                    }
                    elseif($lastpage > 5 + ($stages * 2))	// Enough pages to hide a few?
                    {
                        // Beginning only hide later pages
                        if($page < 1 + ($stages * 2))		
                        {
                            for ($counter = 1; $counter < 4 + ($stages * 2); $counter++)
                            {
                                if ($counter == $page){
                                    $paginate.= "<span class='current'>$counter</span>";
                                }else{
                                    $paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
                            }
                            $paginate.= "...";
                            $paginate.= "<a href='$targetpage?page=$LastPagem1'>$LastPagem1</a>";
                            $paginate.= "<a href='$targetpage?page=$lastpage'>$lastpage</a>";		
                        }
                        // Middle hide some front and some back
                        elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
                        {
                            $paginate.= "<a href='$targetpage?page=1'>1</a>";
                            $paginate.= "<a href='$targetpage?page=2'>2</a>";
                            $paginate.= "...";
                            for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
                            {
                                if ($counter == $page){
                                    $paginate.= "<span class='current'>$counter</span>";
                                }else{
                                    $paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
                            }
                            $paginate.= "...";
                            $paginate.= "<a href='$targetpage?page=$LastPagem1'>$LastPagem1</a>";
                            $paginate.= "<a href='$targetpage?page=$lastpage'>$lastpage</a>";		
                        }
                        // End only hide early pages
                        else
                        {
                            $paginate.= "<a href='$targetpage?page=1'>1</a>";
                            $paginate.= "<a href='$targetpage?page=2'>2</a>";
                            $paginate.= "...";
                            for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
                            {
                                if ($counter == $page){
                                    $paginate.= "<span class='current'>$counter</span>";
                                }else{
                                    $paginate.= "<a href='$targetpage?page=$counter'>$counter</a>";}					
                            }
                        }
                    }
                                
                            // Next
                    if ($page < $counter - 1){ 
                        $paginate.= "<a href='$targetpage?page=$next'>next</a>";
                    }else{
                        $paginate.= "<span class='disabled'>next</span>";
                        }
                        
                    $paginate.= "</div>";		
                
                
            }
            
             // pagination
             echo "<div style='text-align: center'>".$paginate."</div>";
            ?>
            

            
            <table cellspacing="0" cellpadding="0" style="padding:0 -10px;">
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Opt 1</th>
                    <th>Opt 2</th>
                    <th>Opt 3</th>
                    <th>Operation</th>
                </tr>
            <?php 
                    $table="";
            
                    foreach($result as $row)
                    {
                    $table .="<tr>";
                    $table .="<td style='text-align: center'><b>".$row['name']."</b></td>";
                    $table .="<td style='text-align: center'><b>".$row['address']."</b></td>";
                    $table .="<td style='text-align: center'>".$row['opt1']."</td>";
                    $table .="<td style='text-align: center'>".$row['opt2']."</td>";
                    $table .="<td style='text-align: center'>".$row['opt3']."</td>";
                    $table .="<td style='text-align: center'><a href='add_location.php?edit=".$row['id']."'><img src='{$baseUrl}images/edit.png'></a> <a id='del_location' href='list_locations.php?removeid=".$row['id']."'><img src='{$baseUrl}images/delete.png'></a></td>";
                    $table .="</tr>";
                    
                    }
                echo $table;
                ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include($_config['admin_includes']."footer.php"); ?>