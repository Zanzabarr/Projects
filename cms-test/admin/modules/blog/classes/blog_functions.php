<?php 

class blog_functions  {

	static public function get_favourites($number = 1, $pad = false)
	{
		if (!is_web_int($number))
		{
			return false;
		}
		
		if($pad)
		{
		
			$sql = "
SELECT * 
FROM `blog_post` 
WHERE `id` > 0 
  AND `status` > 0
ORDER BY `featured` DESC, `date` DESC
LIMIT {$number}
";
		}
		else
		{
			$sql = "
SELECT * 
FROM `blog_post` 
WHERE `id` > 0 
  AND `status` > 0
  AND `featured` > 0
ORDER BY `date` DESC
LIMIT {$number}
";
		}
		
		return logged_query($sql,0,array());
	}

	static public function get_image_path($filename, $size = 'thumb')
	{
		global $_config;
		
		// let's try to get the image
		if (! $filename) return false;
		if ($filename == 'blog_post_dflt.jpg') 
		{
			$base = $_config['admin_url'] ."modules/blog/images/default_post_image/";
		}
		else
		{
			$base = $_config['upload_url'] . 'blog_post/';
		}
		return $base . $size .'/'. $filename;
	}
	
}