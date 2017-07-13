$(document).ready(function() {
	
	var $select = $('#catSelect'),
		$tree = $('#categoryTree'),
		$cN = $('#catName'),
		$btn = $('#newCat');
	
	function sendRequest(n, p)
	{
		var $loadingImage = $('#ajaxLoader');
		
		$.ajax({
			url: 'new_cat_query.php',
			data: {f: (p) ? 'newSub' : 'newCat', cat: n, parentCat: p},
			beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				$loadingImage.hide();
				alert('Failed to create new Menu Item'); },
			success: function (jsonResponse) {
				jsonResponse = $.parseJSON(jsonResponse);
				$loadingImage.hide();
				var n = jsonResponse[0].notice;
				(n.message) ? alert(n.message) : updateAssets(jsonResponse[1].html);		
			}
		});
	}
	
	function updateAssets(data) {
			$select.html(data.select);
			$tree.html(data.tree);
		}
	
	$btn.click(function(){
		var n = $.trim($cN.val()),
			p = parseInt($select.val());
			(!n || n == '') ? alert('Page name is required.') : sendRequest(n, p);
	});

	// toggle the add page section
	$('#addCategoryForm').hide()
	$('#toggleAddForm').click(function(){
		$('#addCategoryForm').slideToggle('slow');
		return false;
	});
});
