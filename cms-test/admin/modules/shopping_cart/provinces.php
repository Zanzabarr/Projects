<?php

$arProvinces = array('','AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT','AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY');

/*	sample json to return:
    {"results":[  
        {"id":1,"name":"Ant"},   
        {"id":2,"name":"Bear"},  
        {...} // more results here...  
    ]}  
*/

$return = '{"results":[ ';
$i = 0;
foreach($arProvinces as $p) {
	$i++;
	$return .= "{\"id\":{$p},\"name\":\"{$p}\"}"
	if($i < count($arProvinces)) $return .= ",";
}
$return .= "]}";
echo $return;
?>