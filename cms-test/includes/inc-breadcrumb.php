<?php
// get the right breadcrumb
$breadcrumb = false;

if ($module == 'default' || $module == 'testimonials')
{
	$breadcrumb = $pages->breadcrumb(uri::get(0));
}
if (isset($_config['cartName']) && uri::exists($_config['cartName'] )  )
{
	
	if(uri::exists('products') )
	{		
		if(uri::get('products')) $breadcrumb = $objCategory->breadcrumb_by_product($item_data['url'], $item_data['title']);
		
	}
	elseif (uri::exists('category')) 
	{
		$tmpCat = $objCategory->get_category_by_url(uri::get('category'));
		if(!$tmpCat) $breadcrumb = false;
		else { 
			$id = $tmpCat['id'];
			$breadcrumb = $objCategory->breadcrumb($id);
		}
	}	
	// TODO //	elseif (uri::exists('collection'))	
	elseif (uri::exists('search'))	
	{
		$breadcrumb[0] = array(
			'url' => $_config['cartName'] . "/search",
			'title' => "Search"
		);
		if($searchVal = uri::get('search') )
		{
			$breadcrumb[1] = array(
				'url' => $_config['cartName'] . "/search/" . $searchVal,
				'title' => $searchVal
			);	
		}
	}
	else {
		$breadcrumb = $pages->breadcrumb(uri::get(0));
	}
}




//build breadcrumb
if ($breadcrumb && count($breadcrumb) > 0 && $hasSidebar) :
?>
<ul id="breadcrumb">
   <li>YOU ARE HERE &nbsp;&gt;&gt;&nbsp;</li>
<?php 
$i = 0;
$length = count($breadcrumb) -1;
foreach($breadcrumb as $crumb) : 
	if ($i++ < $length) :
?>
	<li><a href="<?php echo $crumb['url']; ?>">&nbsp;<?php echo $crumb['title']; ?>&nbsp;&gt;&nbsp;</a></li>
<?php else : ?>
	<li>&nbsp;<?php echo $crumb['title']; ?> </li>
<?php endif;  endforeach; ?>
	
</ul>
<?php
endif;
