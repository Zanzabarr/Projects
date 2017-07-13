<?php
class uri {
	private static $segments = array();
	
	// passed the page portion of the url: /shopping_cart/category
	// 									   /about_us
	public static function initialize($page_url)
	{
		// only for use with front end: backend doesn't use uri
		self::$segments = explode('/', $page_url);
	}
	
	// if key is numeric, returns the value at that position
	// else returns the value associated with the passed key
	public static function get($key)
	{
		if (is_pos_int($key,true) ) return self::get_posn($key);
		return self::get_value($key);
	}
	
	public static function get_posn($posn)
	{
		// must be an integer
		if(! is_pos_int($posn, true) ) return false;
		
		if(isset(self::$segments[$posn])) return self::$segments[$posn];
		return false;
	}
	
	// in the uri, value is the next element after $key
	// return $value if $key and value exist
	// returns false if key or value don't exist
	public static function get_value($key)
	{
		$posn = array_search($key, self::$segments);
		if($posn !== false && isset(self::$segments[$posn+1])) return self::$segments[$posn+1]; 
		return false;
	}
	
	// returns true if $segment is part of the uri
	//	false otherwise
	public static function exists($segment)
	{
		return in_array($segment, self::$segments);
	}
	
}	