<?php
class form {
	private $arValidate			= array(); 	//	array of key / value pairs (INPUT_NAME / INPUT_VALUE) to validate
	private $arError			= array();	// 	multidimensional assoc array of error messages
											//  $arError[INPUT_NAME][0]='MESSAGE 1'
											//						[1]='MESSAGE 2'
	private $validated			= false;	//  true once the validate function has run

	private $required_input 	= array();	// 	elements that must have valid values: else error
	private $decimal_input		= array();	// 		must have decimal values (if present)
	private $integer_input		= array();	//		must have integer value (if present)
	private $binary_input		= array();	//		must have 0 or 1 (absence of this input (checkbox) is treated as 0
	private $unique_input		= array();  // 	cannot be the same as existing value
											//	array of key (field) => array with the following values:		
													// original:   value on page load: create pages value should be blank or 0
													//								   edit pages, value is self
													// new: the value being changed to ( always valid if same as original )
													// table: the table the data is stored in
													// field: the relevant field	
	private $multi_check_input	= array();	//		special case: expects an array result : converts to comma separated string
	private $email_input		= array();	//		must be valid email
	private $password_pair		= array();	//	assoc array: ('password' => $password_name, 're_pass' => $re_pass_name)	
											// compares password to re_pass and kicks out mismatches
	private $less_than_input	= array();  // array of key value pairs, key is the field, value is the amount it must be below
	private $greater_than_input	= array();  // array of key value pairs, key is the field, value is the amount it must exceed
	private $exceptions			= array();  // array of items not to validate. Is used to exclude items that have already been set for validation
											//  	in certain cases. eg: user isn't admin, don't require admin only inputs
											
	
	private $required_message	= "Required field";
	private $decimal_message	= "Must be a decimal";
	private $binary_message		= "Error selecting this element";
	private $integer_message	= "Value must be an integer";
	private $email_message		= "Please provide a valid email address";
	private $re_pass_message	= "Must match Password";
	private $password_message	= "Please re-enter password";
	private $unique_message		= "Duplicate value, must be unique";
	private $greater_than_message = "Must be greater than: ";
	private $less_than_message = "Must be less than: ";

	public function __construct($arValidate)
	{
		if( is_assoc($arValidate) ) $this->arValidate = $arValidate;
		else throw new Exception("Associative array required");
	}
	
	// set the validation arrays
	public function set_required_input($required_input)
	{
		if ( is_array($required_input) ) $this->required_input = $required_input;
		else throw new Exception("Array expected");
	}
	public function set_decimal_input($decimal_input)
	{
		if ( is_array($decimal_input) ) $this->decimal_input = $decimal_input;
		else throw new Exception("Array expected");
	}
	public function set_integer_input($integer_input)
	{
		if ( is_array($integer_input) ) $this->integer_input = $integer_input;
		else throw new Exception("Array expected");
	}
	public function set_binary_input($binary_input)
	{
		if ( is_array($binary_input) ) $this->binary_input = $binary_input;
		else throw new Exception("Array expected");
	}
	public function set_multi_check_input($multi_check_input)
	{
		if ( is_array($multi_check_input) ) $this->multi_check_input = $multi_check_input;
		else throw new Exception("Array expected");
	}
	public function set_email_input($email_input)
	{
		if ( is_array($email_input) ) $this->email_input = $email_input;
		else throw new Exception("Array expected");
	}

	public function set_unique_input($unique_input)
	{
		if ( is_array($unique_input) ) 
		{
			foreach($unique_input as $ui)
			{
				if(!is_array($ui) || count($ui) != 4) throw New Exception("Four Parameters Expected");
				if(!is_string($ui[0]) && !is_numeric($ui[0])) throw New Exception("String or Number expected in Parameter 1");
				if(!is_string($ui[1]) && !is_numeric($ui[1])) throw New Exception("String or Number expected in Parameter 2");
				if(!is_string($ui[2])) throw New Exception("DB Table name expected in Parameter 3");
				if(!is_string($ui[3])) throw New Exception("DB Field name expected in Parameter 4");
			}
			
		}
		else throw new Exception("Array expected");
		
		$this->unique_input = $unique_input;
	}
	
	public function set_greater_than_input($input)
	{
		if ( is_array($input) ) 
		{
			foreach($input as $ui)
			{
				if(!is_string($ui) && !is_numeric($ui)) throw New Exception("String or Number Expected");
			}
			
		}
		else throw new Exception("Array expected");
		
		$this->greater_than_input = $input;
	}

	public function set_less_than_input($input)
	{
		if ( is_array($input) ) 
		{
			foreach($input as $ui)
			{
				if(!is_string($ui) && !is_numeric($ui)) throw New Exception("String or Number Expected");
			}
			
		}
		else throw new Exception("Array expected");
		
		$this->less_than_input = $input;
	}	
	// don't validate if in this list (used for different conditions)
	//   eg: some items are for admin only, don't validate them if not admin
	//		 in this case, an array of admin only items will be sent if user not admin
	public function set_exceptions($exceptions)
	{
		if ( is_array($exceptions) ) $this->exceptions = $exceptions;
		else throw new Exception("Array expected");
	}
	public function set_password_pair($password, $re_pass)
	{
		if ( is_string($password) ) $this->password_pair['password'] = $password;
		else throw new Exception("String expected");
		if ( is_string($re_pass) ) $this->password_pair['re_pass'] = $re_pass;
		else throw new Exception("String expected");
	}
	// set standard error messages
	public function set_required_message($message)
	{
		if ( is_string($message) ) $this->required_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_decimal_message($message)
	{
		if ( is_string($message) ) $this->decimal_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_binary_message($message)
	{
		if ( is_string($message) ) $this->binary_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_integer_message($message)
	{
		if ( is_string($message) ) $this->integer_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_email_message($message)
	{
		if ( is_string($message) ) $this->email_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_re_pass_message($message)
	{
		if ( is_string($message) ) $this->re_pass_message = $message;
		else throw new Exception('$message: String expected');
	}	
	public function set_greater_than_message($message)
	{
		if ( is_string($message) ) $this->greater_than_message = $message;
		else throw new Exception('$message: String expected');
	}

	public function set_less_than_message($message)
	{
		if ( is_string($message) ) $this->less_than_message = $message;
		else throw new Exception('$message: String expected');
	}
	public function set_unique_message($message)
	{
		if ( is_string($message) ) $this->unique_message = $message;
		else throw new Exception('$message: String expected');
	}
	// TODO
	// set specific error messages
	
	public function validate()
	{	
		$arValid = array();
		$arError = array();
		foreach($this->arValidate as $k=>$v)
		{
			// if this key has been excluded, skip it
			if ( in_array($k, $this->exceptions ) ) continue;
			
			if ( in_array($k, $this->multi_check_input ) ) $arValid[$k] = pack_multi_index($v);
			else $arValid[$k] = trim(htmlspecialchars($v, ENT_QUOTES)); 

			if ($arValid[$k] == '' && in_array($k, $this->required_input) ) 
			{
				$arError[$k][] = $this->required_message;
			}
			if ( in_array($k, $this->integer_input) && $arValid[$k] != '' && ! is_web_int($arValid[$k]) ) 
			{
				$arError[$k][] =  $this->integer_message;
			}
			if ( in_array($k, $this->decimal_input) && $arValid[$k] !='' && ! is_numeric($arValid[$k]) ) 
			{
				$arError[$k][] = $this->decimal_message;
			}
			if ( array_key_exists($k, $this->less_than_input) && $v >=  $this->less_than_input[$k] ) 
			{
				$arError[$k][] = $this->less_than_message . $this->less_than_input[$k] ;
			}
			if ( array_key_exists($k, $this->greater_than_input) && $v <= $this->greater_than_input[$k] ) 
			{
				$arError[$k][] = $this->greater_than_message . $this->greater_than_input[$k];
			}
			if ( in_array($k, $this->binary_input) && $arValid[$k] !='1' && $arValid[$k]!='0')
			{
				$arError[$k][] = $this->binary_message;
			}
			if ( $arValid[$k] != '' && in_array($k, $this->email_input) && ! check_email_address( $arValid[$k] ) )
			{
				$arError[$k][] = $this->email_message;
			}
			if (array_key_exists($k, $this->unique_input) && ! $this->validate_unique( $this->unique_input[$k] ) )
			{
				$arError[$k][] = $this->unique_message;
			}
		}
		// multi input indexes that aren't set or come up as false need to be set to empty
		foreach ($this->multi_check_input as  $multi_check_name )
			if (!isset($arValid[$multi_check_name]) || $arValid[$multi_check_name] === false) $arValid[$multi_check_name] = '';

		// binary inputs that aren't set need to be set as 0 (catches unchecked checkboxes
		foreach ($this->binary_input as $binary_name)
			if ( ! isset($arValid[$binary_name]) )	$arValid[$binary_name] = '0';
		
		// test password - re_pass matching
		// this test has to be last, it checks to see if any other errors have been submitted
		//	if they have and there is no error matching, then these need an error saying to
		//	resubmit password
		if(count($this->password_pair))
		{
			$p = $this->password_pair['password'];
			$r = $this->password_pair['re_pass'];
			if(	!isset($arValid[$p]) 
				|| !isset($arValid[$r])
				|| $arValid[$r] == ''
				|| $arValid[$p] != $arValid[$r] 
			) 
			{
				$arError[$r][] = $this->re_pass_message;
				if(!isset($arError[$p])) $arError[$p][] = $this->password_message;
			}
			elseif (count($arError))
			{
				// no mismatch error occured but there are other errors,
				// give password resubmit message
				$arError[$p][] = $this->password_message;
				$arError[$r][] = $this->password_message;
			}
		}
		
		$this->arError = $arError;
		$this->validated = true;
		
		return $arValid;
	}

	public function get_errors()
	{
		if (! $this->validated) throw new Exception("Form:validate() must be run first");
		return $this->arError;
	}
	
	private function validate_unique($arParms)
	{
		$original = trim($arParms[0]);
		$new = trim($arParms[1]);
		$table = $arParms[2];
		$field = $arParms[3];

		
		
		if(! is_numeric($new) ) $new = '"'.mysql_real_escape_string($new).'"';
		$result = logged_query("
			SELECT `{$field}`
			FROM `{$table}`
			WHERE `{$field}`={$new}
		");
		$number_of_matches = mysql_num_rows($result);
		
		// if there are more than one of these already, require this one to change 
		if($number_of_matches > 1) return false;
		// if this started as itself: good
		if( $original == $new ) return true;
		// this has been changed from it's original value: is the new value good?
		return ! (bool) mysql_num_rows($result);
	}
}	