<?php

class InputField {

	private $name			=	false;
    private $type 			= 	'text';
	private $id				=	false;
	private $size			=	'small';
	private $heading 		=	'Add a Heading';
	private $toolTip 		= 	false;
	private	$tipType		=	'right';
	private	$value			=	'';
	private	$counterMax		=	0;
	private $counterWarning	= 	false;
	private $arErr			=  	false;
	private $options		=	array();
	private $disabled		= 	false;
	private $selected		= 	false;	// may be a string or (for multiselect only) an array of strings
    private $readonly       =   false;
	private $checkValue		= 	1;		// default value of checked checkboxes, can be set with checkValue();


	private $extraAttributes= 	array();		// additional attributes to be added(array of attribute_name/attribute_value pairs)
	private $extraClasses	=	array();		// additional classes 
	
	public function __construct($heading, $name, $id = false )
    {
		if ($id === false) $id = $name;
		if (! $name) die('<p>name must have a unique value</p>');
		if (! $id) die('<p>id must have a unique value</p>');
		if (! $heading) die('<p>Need a heading</p>');
		$this->name 	= $name;
		$this->id		= $id;
		$this->heading 	= $heading;
	}
	
	private function createOptions()
	{
		$optionList = '';
		foreach ($this->options as $value => $parms)
		{	
			// even if selected isn't an array, make it one
			$selected = $this->selected;
			if ($selected !== false)
			{
				if (! is_array($selected) )
				{
					$selected = array($selected);
				}
			}
			else $selected = array();
			//print_r($selected);

			$is_selected = in_array($value, $selected ) ? " selected='selected'" : '';
			$optionList .= "\t\t\t<option value='{$value}'{$is_selected} ";
			foreach ($parms['arAttr'] as $attr => $attrVal)
			{
				$optionList .= " {$attr}='{$attrVal}' ";
			}
			$optionList .= ">" . $parms['heading'] . "</option>\n";
		}
		return $optionList;
	}
	
	private function createJS()
	{	
		if ( ! $this->id ) die('Input fields with counters must have an id set');
		
		// it it's a multi select: add js to change it's size on click
		$multi = '';
		if($this->type=='multiselect')
		{
			$multi  = "var \$thisId = $('#{$this->id}');\n";
			$multi .= 'var numOptions = $thisId.children("option").length;'."\n";
			$multi .= 'var startSize = 1; var expSize = 1;'."\n";
			$multi .= 'if (numOptions > 1){'."\n";
			$multi .= "\t".'  startSize = 2;'."\n";
			$multi .= "\t".'  expSize = numOptions > startSize ? numOptions : startSize;'."\n";
			$multi .= "\t".'  expSize = numOptions >5 ? 5: expSize;'."\n";
			$multi .= "\t".'  $thisId.parents(".multi-sel").addClass("size2");'."\n";
			$multi .= '}'."\n";
			$multi .= '$thisId.css("height",startSize *15 + 12);'."\n";
			$multi .= '$thisId.focus(function(){$thisId.css("height",expSize *15 + 12); });'."\n";
			$multi .= '$thisId.blur(function(){ $thisId.css("height",startSize *15 + 12); })'."\n";
		}
		
		// set the counter plugin
		$ctr = '';
		if ( $this->counterMax > 0 )
		{
			$cntWarn = $this->counterWarning ? $this->counterWarning : $this->counterMax;
			$ctr  = "\t$('#{$this->id}').jqEasyCounter({\n";
			$ctr .= "\t\t'maxChars': {$this->counterMax}, \n";
			$ctr .= "\t\t'maxCharsWarning': {$cntWarn}, \n";
			$ctr .= "\t\t'msgFontSize': '10px', \n";
			$ctr .= "\t\t'msgFontColor': '#000', \n";
			$ctr .= "\t\t'msgTextAlign': 'left', \n";
			$ctr .= "\t\t'msgWarningColor': '#F00', \n";
			$ctr .= "\t\t'msgAppendMethod': 'insertAfter' \n";
			$ctr .= "\t});\n";
		}
		
		
		$js = "<script type='text/javascript'>\n";
		$js .= "$(document).ready(function(){\n";
		$js .= $ctr;
		$js .= $multi;
		$js .= "});\n";
		$js .= "</script>\n";

		return $js;
	}
	
	public function type ($type)
	{
		$this->type = $type;
	}
		
	public function size ($size)
	{
		if ( $size == 'verytiny' || $size == 'tiny' || $size == 'verysmall' || $size == 'small'  || $size == 'medium'  || $size == 'large') $this->size = $size;
		else die ($size . ' is not a valid input field size. <br />Valid sizes: verytiny, tiny, verysmall, small, medium, large');
	}
	
	public function toolTip ($toolTip)
	{
		$this->toolTip = $toolTip;
	}
		
	public function tipType ($tipType)
	{
		$this->tipType = $tipType;
	}
		
	public function value ($value)
	{
		$this->value = $value;
	}
		
	public function checkValue ($checkValue)
	{
		$this->checkValue = $checkValue;
	}
	










	public function counterMax ($counterMax)
	{
		$this->counterMax = $counterMax;
	}
		
	public function counterWarning ($counterWarning)
	{
		$this->counterWarning = $counterWarning;
	}
		
	public function arErr ($arErr)
	{
		$this->arErr = $arErr;
	}
	
	// @parms:	$value: 	the option's value
	//			$heading: 	the option to be displayed
	//			$arAttr:	an optional array of attribute => value pairs
	public function option ($value, $heading, $arAttr = array() )
	{
		$this->options[$value] = array('heading' => $heading, 'arAttr' => $arAttr);
	}
	
	public function disable ($disabled = true)
	{
		if (! is_bool($disabled) ) die('disabled field requires a boolean value, default is true (disables input)');
		$this->disabled = $disabled;
	}
	
	public function selected ($selected)
	{
		$this->selected = $selected;
	}
		
    public function readonly ($readonly)
    {
        $this->readonly = $readonly;
    }



	public function extraClasses ($extraClasses)
	{
		if (! is_array($extraClasses) ) die('extra_class field requires an array of classes to add');
		$this->extraClasses = $extraClasses;
	}
	
	public function extraAttributes ($extraAttributes)
	{
		if (! is_array($extraAttributes) ) die('extraAttributes field requires an array of (attribute_name => value) pairs');
		$this->extraAttributes = $extraAttributes;
	}
	
	private function getAttributes()
	{
		$result = '';
		foreach($this->extraAttributes as $key => $value)
		{
			$result .= ' ' . $key . '="' . $value . '"';
		}
		return $result;
	}
	
	private function getClasses()
	{
		$result = '';
		foreach($this->extraClasses as $value)
		{
			$result .= ' ' . $value;
		}
		return $result;
	}
	
	public function createInputField()
	{	
		// build the javascript if the field has a counter
		$js = $this->counterMax > 0 || $this->type == 'multiselect' ? $this->createJS() : '';
		
		// create input_wrap
		$sizeClass = $this->size == 'small' ? '' : $this->size ;
		$isMultiSelect = $this->type == 'multiselect' ? ' multi-sel' : '' ;
		$inputWrap = "<div class='input_wrap {$sizeClass}{$isMultiSelect}' >\n";
		
		// build the label
		$labelClass = $this->toolTip ? "class='tip" . ucwords($this->tipType) . "' " : '';
		$tipMsg = $this->toolTip ? "title='{$this->toolTip}' " : '';
		$label = "\t<label {$labelClass} {$tipMsg} >{$this->heading}</label>\n";
		
		// build the input box
		$noCounter = $this->counterMax > 0 ? '' : ' no_counter' ;
		$disabled = $this->disabled ? " disabled='disabled' " : '' ;
		$autoOff = $this->type == 'password' ? ' autocomplete="off" ' : '';

		$strClasses = $this->getClasses();
		$strAttributes = $this->getAttributes();
		
        $readonly = $this->readonly ? " readonly='readonly' " : '' ;	
		
		// set checkbox variables
		$checked = "";

		if ( $this->type == 'checkbox'  )
		{

			if ($this->value == $this->checkValue) $checked = " checked='checked' " ;







			$this->value = $this->checkValue;







		}
		
		if ( $this->type == 'textarea' ) 
		{
			$inputBox = "\t\t<textarea name='{$this->name}'  id='{$this->id}' class='mceNoEditor{$noCounter}{$strClasses}'{$strAttributes}{$disabled}{$readonly}>{$this->value}</textarea>\n";
		}
		elseif ( $this->type == 'select' )
		{
			$inputBox = "\t\t<select name='{$this->name}' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes}{$disabled}>\n";
			$inputBox .= $this->createOptions();
			$inputBox .= "\t\t</select>\n";
			
		}
		elseif ( $this->type == 'multiselect' )
		{
			$inputBox = "\t\t<select name='{$this->name}[]' multiple='multiple' size='1' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes}{$disabled}>\n";
			$inputBox .= $this->createOptions();
			$inputBox .= "\t\t</select>\n";
		}
		else $inputBox = "\t\t<input type='{$this->type}' {$autoOff} name='{$this->name}' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes} value='{$this->value}' {$disabled}{$readonly} {$checked}/>\n";
		
		// build the error field
		$hasMsg = isset($this->arErr['inline']) && array_key_exists( $this->name, $this->arErr['inline'] ) ? true : false;
		$errClass = $hasMsg ? "class='{$this->arErr['inline'][$this->name]['type']}'" : '' ;
		$errMsg = $hasMsg ? $this->arErr['inline'][$this->name]['msg'] : '';
		$errField = "\t\t\t<span id='err_{$this->name}' {$errClass}>{$errMsg}</span>\n";
		
		//output the results
		echo $js;
		echo $inputWrap;
		echo $label;
		echo "\t<div class='input_inner'>\n";
		echo "\t\t<div class='message_wrap'>\n";
		echo $errField;
		echo "\t\t</div>";
		echo $inputBox;
		
		echo "\t</div>\n</div>\n<div class='clearFix'></div>\n";
	}

	public function createBareInputField() {
        // build the javascript if the field has a counter
        $js = $this->counterMax > 0 || $this->type == 'multiselect' ? $this->createJS() : '';

        // create input_wrap_inline
        $sizeClass = $this->size == 'small' ? '' : $this->size ;
        $isMultiSelect = $this->type == 'multiselect' ? ' multi-sel' : '' ;
        $inputWrap = "<div class='input_wrap_inline {$sizeClass}{$isMultiSelect}' >\n";

        // build the input box
        $noCounter = $this->counterMax > 0 ? '' : ' no_counter' ;
        $disabled = $this->disabled ? " disabled='disabled' " : '' ;
        $autoOff = $this->type == 'password' ? ' autocomplete="off" ' : '';
        $readonly = $this->readonly ? " readonly='readonly' " : '' ;



		$strClasses = $this->getClasses();
		$strAttributes = $this->getAttributes();

        if ( $this->type == 'textarea' )
        {
            $inputBox = "\t\t<textarea name='{$this->name}'  id='{$this->id}' class='mceNoEditor{$noCounter}{$strClasses}'{$strAttributes}{$disabled}{$readonly}>{$this->value}</textarea>\n";
        }
        elseif ( $this->type == 'select' )
        {
            $inputBox = "\t\t<select name='{$this->name}' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes}{$disabled}>\n";
            $inputBox .= $this->createOptions();
            $inputBox .= "\t\t</select>\n";
        }
        elseif ( $this->type == 'multiselect' )
        {
            $inputBox = "\t\t<select name='{$this->name}[]' multiple='multiple' size='1' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes}{$disabled}>\n";
            $inputBox .= $this->createOptions();
            $inputBox .= "\t\t</select>\n";
        }
        else $inputBox = "\t\t<input type='{$this->type}' {$autoOff} name='{$this->name}' id='{$this->id}' class='{$noCounter}{$strClasses}'{$strAttributes} value='{$this->value}' {$disabled}{$readonly}/>\n";

        // build the error field
        $hasMsg = isset($this->arErr['inline']) && array_key_exists( $this->name, $this->arErr['inline'] ) ? true : false;
        $errClass = $hasMsg ? "class='{$this->arErr['inline'][$this->name]['type']}'" : '' ;
        $errMsg = $hasMsg ? $this->arErr['inline'][$this->name]['msg'] : '';
        $errField = "\t\t\t<span id='err_{$this->name}' {$errClass}>{$errMsg}</span>\n";

        //output the results
        echo $js;
        echo $inputWrap;
        echo $inputBox;

        echo "\t</div>\n";
    }	
}

