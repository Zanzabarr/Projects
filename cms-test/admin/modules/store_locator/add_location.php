<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'store_locator';
include('../../includes/headerClass.php');
include('includes/functions.php');
include("locator_config.php");

$pageInit = new headerClass($headerComponents,$headerModule);
$newLoc = false;	//defaults to false
$error = false;		//defaults to false

if ( array_key_exists('action', $_GET) && ($_GET['action'] == 'add') ) {
	$newLoc = true;
}
elseif ( array_key_exists('action', $_GET) && ($_GET['action'] == 'edit') ) {
	$newLoc = false;
}

/* Address to Location */
function lookup($string){
 
   $string = str_replace (" ", "+", urlencode($string));
   $details_url = "//maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $details_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = json_decode(curl_exec($ch), true);
 
   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
   if ($response['status'] != 'OK') {
    return null;
   }
 
   $geometry = $response['results'][0]['geometry'];
 
    $longitude = $geometry['location']['lng'];
    $latitude = $geometry['location']['lat'];
 
    $array = array(
        'lat' => $geometry['location']['lat'],
        'lng' => $geometry['location']['lng'],
        'location_type' => $geometry['location_type'],
    );
 
    return $array;
 
}

if ( array_key_exists('edit', $_GET) ) {
	$id = filter_var(trim($_GET['edit']),FILTER_SANITIZE_STRING);
	if(is_numeric($id)) {
		$bind = array(':id' => $id);
		$pageInfo = logged_query("SELECT * FROM markers WHERE id = :id",0,$bind);
		$pageInfo = $pageInfo[0];
	} else {
		unset($id);
		$error = "<div class='error'>Record cannot be found.</div>";
	}	
}

if(isset($_POST['Submit']) && !$error) {
	unset($_POST['Submit']);
	foreach($option as $k=>$v) {
		if(!isset($_POST["opt{$k}"])) $_POST["opt{$k}"] = "";
	}
	foreach($_POST as $k=>$v) {
		if($k != "l") {
			$$k = filter_var(trim($v), FILTER_SANITIZE_STRING);
		} else {
			$location = filter_var(trim($v), FILTER_SANITIZE_STRING);
		}
	}

	
	//get geolocation info
	$geo = lookup($location);
	if($geo) {
		$lat = $geo['lat'];
		$lng = $geo['lng'];
	}
	
	if(isset($lat) && isset($lng)) {
		$saved = false;
		if($newLoc) {
			$querystring = "INSERT INTO markers (name, address, lat, lng, opt1, opt2, opt3) VALUES (:name, :address, :lat, :lng, :opt1, :opt2, :opt3)";
			$bind = array(
				":name" => $name,
				":address" => $location,
				":lat" => $lat,
				":lng" => $lng,
				":opt1" => $opt1,
				":opt2" => $opt2,
				":opt3" => $opt3
			);
		} else {
			$querystring = "UPDATE markers SET name= :name, address= :address,lat= :lat,lng= :lng,opt1= :opt1,opt2= :opt2,opt3= :opt3 WHERE id= :id";
			$bind = array(
				":name" => $name,
				":address" => $location,
				":lat" => $lat,
				":lng" => $lng,
				":opt1" => $opt1,
				":opt2" => $opt2,
				":opt3" => $opt3,
				":id" => $id
			);
		}
		if(!$error) $saved = logged_query($querystring,0,$bind);
		
		if($saved !== "false") {
			$id = $_config['db']->getLastInsertId();
			echo "<script type=\"text/javascript\">window.location = \"list_locations.php\"</script>";
		} else {
			$error = "<div class='error'>Please Make Sure to Fill in the Required Fields</div>";
		}
	} else {
		$error = "<div class='error'>Address can not be found</div>";
	}
	
	$pageInfo = logged_query("SELECT * FROM markers WHERE id = $id",0,array());
	$pageInfo = $pageInfo[0];
}

$locResources = "
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/store_locator/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/store_locator/js/list_locations.js\"></script>
";
// set the header variables and create the header
$pageInit->createPageTop($locResources);

//form vars
$formAction = $newLoc ? "add" : "edit";

?>
<style>
.store_locator label {
	display:block;
	clear:both;
}
.store_locator input[type=text] {
	width:70%;
}
</style>
<div class="page_container">
	<div id="h1"><h1><?php echo ucwords($formAction); ?> Store Location</h1></div>
    
    <div id="info_container">
        <div class="store_locator"> 
        <?php if ($error) { echo $error; } ?>
        <form action="add_location.php?action=<?php echo $formAction; ?>" method="post" style="margin:0 auto;">
			<input type="hidden" name="id" value="<?php echo isset($id) ? $id : ""; ?>" />
            <label>*Name: </label><input type="text" name="name" value="<?php echo isset($pageInfo['name']) ? $pageInfo['name'] : ""; ?>"/><br />
            <label>*Address: </label><input type="text" name="l" value="<?php echo isset($pageInfo['address']) ? $pageInfo['address'] : ""; ?>"/><br />
	<?php	foreach($option as $k=>$v) {
				if($v != "") {
					$info = "opt{$k}";
					echo "<label>{$v}</label><input type='text' name='{$info}' value='";
					if(isset($pageInfo[$info])) {
						echo $pageInfo[$info];
					} else {
						echo "";
					}
					echo "'/><br />";
				}
			}
    ?>
            <br />
            <label></label>
            <input type="submit" name="Submit" Value="Save Location" />
        </form>
        </div>
    </div>
</div>

<?php include($_config['admin_includes']."footer.php"); ?>