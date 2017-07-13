<?php	/** REQUIRED FILE FOR SITE SEARCH ENGINE PLUGIN **/
		
		$query = "SELECT title, url, desc, MATCH(desc) AGAINST ('{$terms}' IN BOOLEAN MODE) AS m FROM events WHERE status > 0 && MATCH(desc) AGAINST ('{$terms}' IN BOOLEAN MODE) ORDER BY m desc";
		
		
		$result = logged_query($query,0,array());
		
		if($result !== false && !empty($result)) {
			for($c=0; $c < count($result); $c++) {
				$result[$c]['url'] = "{$mod}/".$result[$c]['url'];
				$result[$c]['intro'] = substr(strip_tags(htmlspecialchars_decode($result[$c]['desc'])),0,120);
			}
		
			$result['q'] = $terms;
		
			buildFrontEnd($result, $mod);
		}
?>