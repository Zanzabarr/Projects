<?php 
class ecom_purchases{
	
	public function __construct()
	{
		parent::__construct('ecom_category', 'ecom_product');
		$this->categoryDataTable = "ecom_category_data";
		$this->tmpCategoryTable	 = "ecom_tmp_category";

	}
}	