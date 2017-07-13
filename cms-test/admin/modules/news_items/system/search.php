<?php	/** REQUIRED FILE FOR SITE SEARCH ENGINE PLUGIN **/
		
		$query = "SELECT news_item_title as title, url, content, MATCH(content) AGAINST ('{$terms}' IN BOOLEAN MODE) AS m FROM news_items WHERE status > 0 && MATCH(content) AGAINST ('{$terms}' IN BOOLEAN MODE) ORDER BY m desc";
		
		
		$result = logged_query($query,0,array());
		
		if($result !== false && !empty($result)) {
			for($c=0; $c < count($result); $c++) {
				$result[$c]['url'] = "{$mod}/".$result[$c]['url'];
				$result[$c]['intro'] = substr(strip_tags(htmlspecialchars_decode($result[$c]['content'])),0,120);
			}
		
			$result['q'] = $terms;
		
			buildFrontEnd($result, $mod);
		}
?>