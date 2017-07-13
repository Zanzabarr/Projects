<?php
// 	header class is the first thing instantiated after component vars are set.
// 		eg: $headerComponents( 'revisions', 'uploads' );
//			$headerModule('blog');		
//			include(/path/to/headerClass.php);
//			$header = new headerClass($headerComponents, $headerModule);

// 	start by doing all actions that occur for every page
include_once dirname(__FILE__)."/../../includes/config.php";
include_once dirname(__FILE__)."/../../includes/functions.php";
createTinySalt();
// set globals: baseUrl: 	url up to the admin level
//				pageName:	name of the current page
//				curUser:	all salient data about the currently logged in user
$baseUrl = $_config['admin_url'];
global $baseUrl;

// find out the current page name and make it available to all pages.
$getpageName = $_SERVER['SCRIPT_NAME']; //Pulls Page Info
$regPage = pathinfo($getpageName); //Puts page name and extension into an array
$pageName = $regPage['filename'].".".$regPage['extension'];
global $pageName;

// validate user: kick to login if not logged in, otherwise set curUser data
$curUser = check_login();
global $curUser;


/*			----------------------------this may be silly, consider replacing ---------------------------- 			*/
// include required components
// note: uploads component should eventually be converted to a class so it can be instantiated at the top and called where approp.
if (isset($headerComponents) && is_array($headerComponents) && in_array('revisions', $headerComponents) )
	include_once($_config['components']."revisions/revisions.php");



/* 	CLASS: headerClass
*	
*/
class headerClass {
	private $validComponents = array('revisions', 'uploads');
	private $components;
	private $module;
	private $curUser;
	public $validModules;
	public $validPages = false; // admin doesn't have access to the pages section if false
	
	// $components	: array(string[,string][,...]) 
	//			   or string			matching currently deployed components
	// $module must be a string reflecting which (if any) module is currently in use.
	public function __construct($components = array(), $module = false )
    {	
		global $curUser, $pageName, $baseUrl;
		$this->curUser = $curUser;

		// validate components
		if ( ! is_array($components)) 
		{
			$components = array($components);
		}	
		foreach($components as $key => $comp)
		{	
			if ( ! in_array($comp, $this->validComponents) ) 
			{
				my_log("\t/admin/includes/headerClass.php \n\tThe component " . $comp . ' is not a valid component name.');
				unset($components[$key]);	
			}	
		}
		$this->components = $components;

		// determine all loaded modules
		if(isset($_SESSION['modules'])): foreach ( $_SESSION['modules'] as $modName => $arMod )
		{
			// need to add check here for sub-admin legality
			if ( $arMod['valid'] && ($this->curUser['user_id'] == 1 || $this->validateSubadminModule($modName))) $this->validModules[$modName] = $arMod['data'];
		} endif;
		// determine all loaded modules
		if(isset($_SESSION['modules'])): foreach ( $_SESSION['modules'] as $modName => $arMod )
		{
			// need to add check here for sub-admin legality
			if ( $arMod['valid'] && ($this->curUser['user_id'] == 1 || $this->validateSubadminModule($modName))) $this->validModules[$modName] = $arMod['data'];
		} endif;
		// pages aren't part of the module system but need to be added to the validModules array if allowed for user
		if($this->curUser['user_id'] == 1 || $this->validateSubadminModule('pages'))
		{
			$this->validPages = true;
		} elseif ($pageName == "pages.php" || $pageName == "edit_page.php" || $pageName == "view_page.php") {
			$location = $baseUrl ;
			header("Location: " . $location);	
		}

		// validate module against loaded modules
		if ( $module !== false)
		{ 
			if ( ! is_string($module) || ! array_key_exists($module, $_SESSION['modules']) )
			{
				my_log("\t/admin/includes/headerClass.php \n\tThe module " . $module . ' is not a valid module name.');
				$module = false;
			}	
		}
		else $module = 'dflt';
		$this->module = $module;
		// redirect user if user isn't authorized to use this module
		if ($this->module != 'dflt' && ! array_key_exists($this->module, $this->validModules) ) 
		{
			global $baseUrl;
			$location = $baseUrl;
			header("Location: " . $location);
		}
	}
	
	public function validateSubadminModule($module)
	{
		$user_id = $this->curUser['user_id'];
		
		// get array of user's permitted modules;
		$validModules = logged_query_assoc_array("SELECT `module` FROM `auth_users_permit` WHERE `user_id` = :uid",
			null,0,
			array(':uid' => $user_id)
		);
		//echo $module;
		foreach($validModules as $validModule)
		{
			// special case: pages
			
			if($validModule['module'] == $module) return true;
		}
		return false;
	}
	
	// put together the top of the page in standard fashion(including doctype/head/header/sidebar)
	public function createPageTop($resources = '')
	{ 
		global $_config;
	?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo$_config['site'];?> CMS</title>
<?php
		$this->createPageHead($resources);
		echo "</head>\n";
		echo "<body>\n";
		$this->createHeader();
		$this->createSidebar();
	}
	
	// createHead outputs the beginnning of the page: up to the end of </head>
	// may be called independently for better customization or by createPageTop
	// $resources : string		must be the page specific resources required to be placed in the head of this page
	public function createPageHead($resources = '')
	{
		global $baseUrl;
		global $_config;
		// create js versions of all config variables
		// create opening
		$jsconfig = "
			<script type='text/javascript'>
				var config = new Array(); ";
		
		// get data
		foreach ($_config as $key => $value)	
			if(is_string($value))	$jsconfig .= "config['{$key}'] ='{$value}';";
		
		// close
		$jsconfig .= "
			</script>
		\n";

		$head = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		$head .= "<link rel='stylesheet' type='text/css' href='{$baseUrl}css/styles.css' />"."\n";
		$head .= "<link rel='stylesheet' type='text/css' href='{$baseUrl}css/tipTip.css' />"."\n";
		$head .= "<link href='//fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>"."\n";
		$head .= '<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>'."\n";
		
		$head .= $jsconfig;
		$head .= '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>'."\n";
		$head .= '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>'."\n";
		$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."js/jquery.tiptip.js\"></script>"."\n";
		$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."js/jquery.modal.js\"></script>"."\n";
		$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."js/jquery.jqEasyCharCounter.min.js\"></script>"."\n";
		$head .= '<script type="text/javascript" src="'.$baseUrl.'js/tiny_mce/tinymce.min.js"></script>'."\n";
		$head .= '<script type="text/javascript" src="'.$baseUrl.'js/tiny_custom_plugins.tinymce.plugin.js"></script>'."\n";
		$head .= '<script type="text/javascript" src="'.$baseUrl.'js/tiny_mce_settings.js"></script>'."\n";
		$head .= '<script type="text/javascript" src="'.$baseUrl.'js/functions.js"></script>'."\n";
		
		// component specific resources
		if ( in_array('uploads' ,$this->components))
		{
			$head .= "<link rel='stylesheet' type='text/css' href='{$baseUrl}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' />"."\n";
			$head .= "<link rel='stylesheet' type='text/css' href='{$baseUrl}components/uploading/styles.css' />"."\n";
			$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js\"></script>"."\n";
			$head .= "<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.ui.widget.js\"></script>"."\n";
			$head .= "<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.iframe-transport.js\"></script>"."\n";
			$head .= "<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.fileupload.js\"></script>"."\n";
			$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."components/uploading/uploads.js\"></script>"."\n";
		}
		
				// component specific resources
		if ( in_array('revisions' ,$this->components))
		{
			$head .= "<link rel='stylesheet' type='text/css' href='{$baseUrl}components/revisions/styles.css' />\n";
			$head .= "<script type=\"text/javascript\" src=\"".$baseUrl."components/revisions/revisions.js\"></script>\n";
		}

		// page specific resources
		$head .= $resources;

/*		// i'm not sure what(if any)pages use this, once I know: import it to the appropriate section
		$head .= "
<script> 
function checkForOther(obj) { 
	var c = document.getElementById('custom_status');
	c.style.display = (obj.value == 'CUSTOM') ? 'inline' : 'none';
	c.value = (obj.value == 'CUSTOM') ? c.value : ''; 
} 
$(function() {
		$('.date').datepicker();
		//size editable in modules/jobs/style.css
		
		$('input.prevtooltip').focus(function() {
			$(this).prev('div').show('fast');
		});
		
		$('input.prevtooltip').blur(function() {
			$(this).prev('div').hide('fast');
		});
	});
</script> 
\n";
*/
		echo $head;
	}

	//	createHeader outputs the system header
	public function createHeader() 
	{
		global $baseUrl, $_config;
		if(true) { ?>
	<!-- common js variables -->
	<input type='hidden' id='adminUrl' value='<?php echo $_config['admin_url']; ?>' />
	<div class="header">
    	<div id="logo"><a href="<?php echo $baseUrl;?>"><img src="<?php echo $baseUrl;?>images/logo.jpg" width="185" height="100"/></a></div>
        <div id="welcome"><span>HELLO <?php echo $_SESSION['username'];?>!</span><br />If you are not <?php echo $_SESSION['username'];?>, please <a href="<?php echo $baseUrl;?>login.php?action=logout">log-out!</a></div>
		<div id="header_menu">
        	<span><?php echo date("F j, Y"); ?></span>
        	<ul>
            	<li style="margin-right:40px"><a href="<?php echo $baseUrl;?>"><img src="<?php echo $baseUrl;?>images/header_items/home.jpg" /></a></li>
                <li><a href="<?php echo $baseUrl;?>dashboard.php#admpwd"><img src="<?php echo $baseUrl;?>images/header_items/passwords.jpg" /></a></li>
                <li><a href="<?php echo $baseUrl;?>#"><img src="<?php echo $baseUrl;?>images/header_items/settings.jpg" /></a></li>
              <!--   <li><a href="<?php // echo $baseUrl;?>#"><img src="<?php // echo $baseUrl;?>images/header_items/inbox.jpg" /></a></li>
                <li><a href="<?php // echo $baseUrl;?>#"><img src="<?php echo $baseUrl;?>images/header_items/schedule.jpg" /></a></li>
                <li><a href="<?php // echo $baseUrl;?>#"><img src="<?php echo $baseUrl;?>images/header_items/warnings.jpg" /></a></li> -->
                <li style="margin-left:40px;"><a href="<?php echo $baseUrl;?>login.php?action=logout"><img src="<?php echo $baseUrl;?>images/header_items/logout.jpg" /></a></li>
            </ul>
        </div>
		<?php 
		if ( ! is_installed() ) echo "<script type=\"text/javascript\">window.location = \"{$_config['admin_url']}install/index.php\"</script>";
		?>
	</div>		
		
		<?php };
	}
	
	// createSidebar outputs the sidebar
	public function createSidebar()
	{ 	
		global $baseUrl, $_config, $pageName, $curUser;
//		include($_config['admin_modules'].'blog/system/validate.php');
		
		if (true) {?>
		<div class="sidebar">
			<ul>
				<li id="none">&nbsp;</li>
				
				<?php /* get rid of index
				if ($pageName == "index.php" && $adminmodule = 'dflt') {
					echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}index.php'\"><i id=\"admin_active\"></i></li>";
				} else { 
				echo "<li onclick=\"window.location='{$baseUrl}index.php'\"><i id=\"admin\"></i></li>";
				} 
				*/
				if ($pageName == "dashboard.php" && $adminmodule = 'dflt') {
					echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}dashboard.php'\"><i id=\"dashboard_active\"></i></li>";
				} else { 
				echo "<li onclick=\"window.location='{$baseUrl}dashboard.php'\"><i id=\"dashboard\"></i></li>";
				}
				
				// check for authorization to edit these pages...don't provide link if user not authorized;
				if ($this->validPages)
				{
					// display highlighted if on a 'pages' page
					if (($pageName == "pages.php" || $pageName == "edit_page.php" || $pageName == "view_page.php")&& $adminmodule = 'dflt') {
						echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}pages.php'\"><i id=\"pages_active\"></i></li>";
					} else { 
					echo "<li onclick=\"window.location='{$baseUrl}pages.php'\"><i id=\"pages\"></i></li>";
					} 
				}
				
				// link all modules that this user is allowed to use.
				// data is drawn from the module's /system/validate.php page.
				if( count($this->validModules) > 0 ) : foreach ($this->validModules as $modName => $module)
				{
					if ($this->module == $modName) 
					{
						echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$module['mainUrl']}'\"><i id=\"{$module['sideBarId']}_active\"></i></li>";
					}
					else 
					{ 
					echo "<li onclick=\"window.location='{$module['mainUrl']}'\"><i id=\"{$module['sideBarId']}\"></i></li>";
					} 
				} endif;
				?>

			</ul>
		</div>
<?php	
		}
	}
}	
?>
