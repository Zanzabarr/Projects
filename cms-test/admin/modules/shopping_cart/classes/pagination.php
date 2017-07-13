<?php 

 class pagination
  {

    /**
     * Properties array
     * @var array   
     * @access private 
     */
    private $_properties = array();

    /**
     * Default configurations
     * @var array  
     * @access public 
     */
    public $_defaults = array(
      'pg' => 1,
      'perPage' => 10,
	  'uriString' => ''	// $uriString = implode('/',$uri) for link href attributes
    );

    /**
     * Constructor
     * 
     * @param array $array   Array of results to be paginated
     * @param int   $curPage The current page integer that should used
     * @param int   $perPage The amount of items that should be show per page
     * @return void
     * @access public
     */
    public function __construct($array, $curPage = null, $perPage = null, $uriString = "")
    {
      $this->array   = $array;
      $this->curPage = ($curPage == null ? $this->defaults['pg']    : $curPage);
      $this->perPage = ($perPage == null ? $this->defaults['perPage'] : $perPage);
	  $this->uriString = ($uriString == null ? $this->defaults['uriString'] : $uriString);
    }

    /**
     * Global setter
     * 
     * Utilises the properties array
     * 
     * @param string $name  The name of the property to set
     * @param string $value The value that the property is assigned
     * @return void    
     * @access public  
     */
    public function __set($name, $value) 
    { 
      $this->_properties[$name] = $value;
    } 

    /**
     * Global getter
     * 
     * Takes a param from the properties array if it exists
     * 
     * @param string $name The name of the property to get
     * @return mixed Either the property from the internal
     * properties array or false if isn't set
     * @access public  
     */
    public function __get($name)
    {
      if (array_key_exists($name, $this->_properties)) {
        return $this->_properties[$name];
      }
      return false;
    }

    /**
     * Set the show first and last configuration
     * 
     * This will enable the "<< first" and "last >>" style
     * links
     * 
     * @param boolean $showFirstAndLast True to show, false to hide.
     * @return void    
     * @access public  
     */
    public function setShowFirstAndLast($showFirstAndLast)
    {
        $this->_showFirstAndLast = $showFirstAndLast;
    }

    /**
     * Set the main seperator character
     * 
     * By default this will implode an empty string
     * 
     * @param string $mainSeperator The seperator between the page numbers
     * @return void    
     * @access public  
     */
    public function setMainSeperator($mainSeperator)
    {
      $this->mainSeperator = $mainSeperator;
    }

    /**
     * Get the result portion from the provided array 
     * 
     * @return array Reduced array with correct calculated offset 
     * @access public 
     */
    public function getResults()
    {
      // Assign the page variable
      if (empty($this->curPage) !== false) {
        $this->pg = $this->curPage; // using the get method
      } else {
        $this->pg = 1; // if we don't have a page number then assume we are on the first page
      }
      
      // Take the length of the array
      $this->length = count($this->array);
      
      // Get the number of pages
      $this->pages = ceil($this->length / $this->perPage);
      
      // Calculate the starting point 
      $this->start = ceil(($this->pg - 1) * $this->perPage);
      
      // return the portion of results
      return array_slice($this->array, $this->start, $this->perPage);
    }
    
    /**
     * Get the html links for the generated page offset
     * 
     * @param array $params A list of parameters (probably get/post) to
     * pass around with each request
     * @return mixed  Return description (if any) ...
     * @access public 
     */
    public function getLinks($params = array())
    {
		global $_config;
      // Initiate the links array
      $plinks = array();
      $links = array();
      $slinks = array();
      
      // Concatenate the get variables to add to the page numbering string
      $queryUrl = '';
      if (!empty($params) === true) {
        unset($params['pg']);
		//keep session id out of query string
		unset($params['PHPSESSID']);
        $queryUrl = '&amp;'.http_build_query($params);
      }
      
      // If we have more then one pages
      if (($this->pages) > 1) {
        // Assign the 'previous page' link into the array if we are not on the first page
        if ($this->pg != 1) {
          if ($this->_showFirstAndLast) {
            $plinks[] = ' <a class="firstPage" href="'.$this->uriString.'?pg=1'.$queryUrl.'#content"> FIRST </a> ';
          }
          $plinks[] = ' <a class="prevPage" title="Previous Page" href="'.$this->uriString.'?pg='.($this->pg - 1).$queryUrl.'#content" style="transform:rotate(270deg);-webkit-transform:rotate(270deg);"><img src="'.$_config['admin_url'].'modules/shopping_cart/images/chevron-dark.png"/></a> ';
        }
        //make the select element
		$links[] = '<select class="pagSelect" onchange="Go(this.options[this.selectedIndex].value)">';
        // Assign all the page numbers & links to the array
        for ($j = 1; $j < ($this->pages + 1); $j++) {
		  //if($j % 7 == 1) $links[] = '<div>';
          if ($this->pg == $j) {
            $links[] = ' <option class="selected" selected>'.$j.'</option> '; // If we are on the same page as the current item
          } else {
            $links[] = ' <option value="'.$this->uriString.'?pg='.$j.$queryUrl.'#content">'.$j.'</option>'; // add the link to the array
          }
		  //if($j % 7 == 0) $links[] = '...';
		  //if($j % 7 == 0 || $j == $this->pages) $links[] = '</div>';
        }
		// end the select element
		$links[] = '</select>';
        // Assign the 'next page' if we are not on the last page
        if ($this->pg < $this->pages) {
          $slinks[] = ' <a class="nextPage" href="'.$this->uriString.'?pg='.($this->pg + 1).$queryUrl.'#content"><img src="'.$_config['admin_url'].'modules/shopping_cart/images/chevron-dark.png"/></a> ';
          if ($this->_showFirstAndLast) {
            $slinks[] = ' <a class="lastPage" href="'.$this->uriString.'?pg='.($this->pages).$queryUrl.'#content"> LAST </a> ';
          }
        }
        
        // Push the array into a string using any some glue
        return "<div class='parentBox'><div class='pagesBox'>".implode(' ', $plinks)."PAGE: <div id='pgSelectContainer'>".implode($this->mainSeperator, $links)."</div>".implode(' ', $slinks)."</div></div>";
      }
      return;
    }
  }
?>
<?php
  /************************************************************\
  *
  *   PHP Array Pagination Copyright 2007 - Derek Harvey
  *   www.lotsofcode.com
  *
  *   This file is part of PHP Array Pagination .
  *
  *   PHP Array Pagination is free software; you can redistribute it and/or modify
  *   it under the terms of the GNU General Public License as published by
  *   the Free Software Foundation; either version 2 of the License, or
  *   (at your option) any later version.
  *
  *   PHP Array Pagination is distributed in the hope that it will be useful,
  *   but WITHOUT ANY WARRANTY; without even the implied warranty of
  *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *   GNU General Public License for more details.
  *
  *   You should have received a copy of the GNU General Public License
  *   along with PHP Array Pagination ; if not, write to the Free Software
  *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  *
  \************************************************************/
?>