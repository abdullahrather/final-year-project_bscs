
<?php


/**
   +------------------------------------------------------------------------+
   | Paging                                                                 |
   +------------------------------------------------------------------------+
   | @Copyright (c) 2007, Srivalli.Ch               		                |
   | @author       Srivalli.Ch <ch.srivalli@gmail.com>                      |
   | @version      1.0                                                      |
   | @license      The PHP License                                          |
   | @package      Paging                                                   |
   +------------------------------------------------------------------------+
   |This file demonstrates simple paging concept	                        |
   +------------------------------------------------------------------------+

 *  A class to create a paging Mangement application.
 *
 * It has Methods to 
 * @author Srivalli.Ch <ch.srivalli@gmail.com> 
 * @version 1.0
 * @package cart
 * @copyright The PHP License
 */

class Paging 
{
	var $page;
	var $limit;
	var $offset;
	var $sql;
	var $rs;
	var $numrows;
	var $pgMinLimit;
	var $pgMaxLimit;
	var $seriesLimit;
	
	//Takes the query and calculate totla number of rows and sets the initial values of offset,page
	function Paging($query)
	{
		$this->offset=0;
		$this->page=1;
		$this->sql=$query;
		$this->rs=mysql_query($this->sql);
		$this->numrows=mysql_num_rows($this->rs);
	}
	
	//get the total number of rows
	function getNumRows()
	{
    	return $this->numrows;
    }
	
	//Assigning limit value
 	function setLimit($no)
	{
		$this->limit=$no;
	}
	
	//get the limit value
	function getLimit() 
	{
		return $this->limit;
	}
	
	//calculating totla number of pages
	function getNoOfPages()
	{
        return ceil($this->noofpage=($this->getNumRows()/$this->getLimit()));
    }
	
	//calculating offset for each page
	function getOffset($page)
	{
		if($page>$this->getNoOfPages()) {
			$page=$this->getNoOfPages();
		}
		if($page=="") {
			$this->page=1;
			$page=1;
		}
		else {
			$this->page=$page;
		}
		if($page=="1") {
			$this->offset=0;
			return $this->offset;
		}
		else {
			for($i=2;$i<=$page;$i++) {
				$this->offset=$this->offset+$this->getLimit();
			}
			return $this->offset;
		}
	}
	
	//Get the current page.
	function getPage() 
	{
		return $this->page;
	}
	
	function setTableParams($tabwidth,$align)
	{
		$this->tableWidth = $tabwidth;
		$this->tableAlign = $align;
	}
	
	function setNumLimitSeries($numlimit)
	{
		$this->seriesLimit = $numlimit;
	}
	
	//Template for paging with and without images like previous, selectbox, next
	function getPageNo($image1=NULL,$image2=NULL,$agentid=NULL) 
	{
	

            $str="";
            $str=$str."<table width='".$this->tableWidth."' border='0'><tr>";
            $str=$str."<td align='".$this->tableAlign."' valign='top' >";
            if($this->getPage()>1) {
			    if($image1!=NULL)
				{
					$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()-1).$this->getParameter()."&id=".$agentid."' class='".$this->getStyle()."'></a>";
				}
				if($image1==NULL)
				{
                $str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()-1).$this->getParameter()."&id=".$agentid."' class='".$this->getStyle()."'>Previous</a>&nbsp;";
				}
            }
	
           /* "<form name=\"pgfrm\" method=\"post\" action='".$_SERVER['PHP_SELF']."'>
	                   <select name=\"page\" onChange=\"pagechange()\" >";
					   
			$param=split("[& =]",$this->getParameter());
            for($i=2;$i<=count($param);$i=$i+2) {
                $str=$str."<input type='hidden' name='".$param[$i-1]."' value='".$param[$i]."'>";
            }
			
		    for($i=1;$i<=$this->getNoOfPages();$i++) {
				if($i==$this->getPage())
		         $selected = "selected";
				else
				 $selected = "";
                 $str=$str."<option value= ".$i." ".$selected." >".$i."</option>";
            }			
	        $str=$str."</select></form>";*/
			
			
			 for($i=1;$i<=$this->getNoOfPages();$i++) 
			 {
			      
				$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".$i.$this->getParameter()."&id=".$agentid."' class='".$this->getStyle()."'>";
				if($this->getPage()==$i)
					$str = $str."<font color='#FFFFFF' style='background-color:#a2a2a2'> $i </font></a>";
				else
					$str = $str."$i </a>";
             }
			
			if($this->getPage()>1&&$this->getPage()<$this->getNoOfPages())
			$str=$str."<span class='line'> | </span>";
            if($this->getPage()<$this->getNoOfPages()) {
			    if($image2!=NULL)
				{
					$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()+1).$this->getParameter()."&id=".$agentid."' class='".$this->getStyle()."'></a>";
				}
				if($image2==NULL)
				{
                $str=$str."  <a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()+1).$this->getParameter()."&id=".$agentid."' class='".$this->getStyle()."'>Next</a>";
				}
            }
            $str=$str."</td>";
            $str=$str."</tr></table>";
            print $str;
        }
		
		//Template for paging with and without images like previous, limiting numbers, next
	function getPageNoLimit($image1=NULL,$image2=NULL) 
	{
            
			$str="";
            $str=$str."<table width='".$this->tableWidth."' border='0'><tr>";
            $str=$str."<td align='".$this->tableAlign."' valign='top' width='30%'>";
            if($this->getPage()>1) {
			    if($image1!=NULL)
				{
					$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()-1).$this->getParameter()."' class='".$this->getStyle()."'><input type='image' src='".$image1."' /></a>";
				}
				if($image1==NULL)
				{
                $str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()-1).$this->getParameter()."' class='".$this->getStyle()."'>Previous</a>&nbsp;";
				}
            }
			
			if($this->getPage()<=$this->seriesLimit)
				$this->pgMinLimit=1;
			else
				$this->pgMinLimit=$this->getPage()-$this->seriesLimit;
			if($this->getPage()<($this->getNoOfPages()-$this->seriesLimit))
				$this->pgMaxLimit=$this->getPage()+$this->seriesLimit;
			else
				$this->pgMaxLimit=$this->getNoOfPages();
           
		    for($k=$this->pgMinLimit;$k<=$this->pgMaxLimit;$k++) {
				$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".$k.$this->getParameter()."' class='".$this->getStyle()."'>".$k."</a>";
				}
		  
		  
            if($this->getPage()<$this->getNoOfPages()) {
			    if($image2!=NULL)
				{
					$str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()+1).$this->getParameter()."' class='".$this->getStyle()."'><input type='image' src='".$image2."' /></a>";
				}
				if($image2==NULL)
				{
                $str=$str."<a href='".$_SERVER['PHP_SELF']."?page=".($this->getPage()+1).$this->getParameter()."' class='".$this->getStyle()."'>Next</a>";
				}
            }
            $str=$str."</td>";
            $str=$str."</tr></table>";
            print $str;
        }
		function setStyle($style) 
		{
            $this->style=$style;
        }
        function getStyle()
		{
            return "pagingclass";
        }
        function setActiveStyle($style) 
		{
            $this->activestyle=$style;
        }
        function getActiveStyle()
		{
            return $this->activestyle;
        }
		function setParameter($parameter) 
		{
            $this->parameter=$parameter;
        }
        function getParameter()
		{
            return $this->parameter;
        }
}
?>