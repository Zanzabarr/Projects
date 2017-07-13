<?php
$news = logged_query("SELECT url, news_item_title, content FROM `news_items` WHERE `status` = 1 ORDER BY `date` DESC",0,array()); 

?>
<style>
#news-slider {
	display:none;
	width:100%;
	background:#000;
	height:3em;
}
div.newslabel {
	background:#0076c0;
	float:left;
	padding:.78em 1em;
	color:#fff;
	font-weight:600;
}
.scrollable {
    /* required settings */
    position:relative;
    overflow:hidden;
	width:480px;
    height:3em;
	float:left;
}
.scrollable .items {
    /* this cannot be too large */
    width:2000em;
    position:absolute;
	clear:both;
	background:transparent;
}
.items div {
    float:left;
	padding-left:5px;
	color:#fff;
	height:3em;
	width:470px;
	overflow:hidden;
	white-space:nowrap;
	text-overflow:ellipsis;
	padding:.7em 1.5em .7em 1em;
}
.items div a {
	font-size:1em;
	color:#fff;
	font-weight:300;
}

a.browse {
    display:block;
    width:19px;
    height:19px;
    cursor:pointer;
    font-size:1px;
	position:relative;
	float:right;
	margin-top:1.2%;
}
a.prev {
	background:url('admin/modules/news_items/images/prev-arrow.png') no-repeat #0076c0;
	margin-right:.5%;
}
a.next {
	background:url('admin/modules/news_items/images/next-arrow.png') no-repeat #0076c0;
	margin-right:1%;
}
@media screen and (min-width:635px) {
	#news-slider {
		display:block;
	}
}
@media screen and (min-width:960px) {
	.items div {
		width:780px;
	}
	.scrollable {
	    width: 790px;
	}
}

</style>
<div id="news-slider"><div class="newslabel">NEWS:</div><div id="scrollnews" class="scrollable"><div class="items">
<?php
foreach($news as $n) {
	$title = strtoupper($n['news_item_title']);
	echo "<div class='newslide'><a href='news_items#{$n['url']}'><strong>{$title}:</strong>&nbsp;".substr(strip_tags(htmlspecialchars_decode($n['content'])),0,150)."</a></div>";
}
?>
</div></div><a class="next browse"></a><a class="prev browse"></a></div>

<script src="js/jquery.tools.min.js"></script>
<script>
$(document).ready( function() {
	$(".scrollable").scrollable({circular: true});
	var scrollable = $(".scrollable").data("scrollable");
	
	function slidenews() {
		scrollable.next();
	}
	
	var $newsInterval = setInterval(slidenews, 5000);
});
</script>