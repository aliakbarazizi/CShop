<?php 
class Html
{
	public static function textField($name,$value,$htmloptions=array())
	{
		$htmloptions['type'] = 'text';
		return self::input($name,$value,$htmloptions);
	}
	
	public static function passwordField($name,$value,$htmloptions=array())
	{
		$htmloptions['type'] = 'password';
		return self::input($name,$value,$htmloptions);
	}
	
	public static function checkBox($name,$value,$checked=false,$htmloptions=array())
	{
		if ($checked === true)
		{
			$htmloptions['checked'] = 'checked';
		}
		$htmloptions['type'] = 'checkbox';
		return self::input($name,$value,$htmloptions);
	}
	
	public static function radioButton($name,$value,$selected=false,$htmloptions=array())
	{
		if ($selected === true)
		{
			$htmloptions['selected'] = 'selected';
		}
		$htmloptions['type'] = 'radio';
		return self::input($name,$value,$htmloptions);
	}
	
	public static function selectList($name,$content,$htmloptions=array())
	{
		$htmloptions['name'] = $name;
		return self::tag('select',$content,$htmloptions);
	}
	
	public static function optionsList($data,$selected=false,$htmloptions=array())
	{
		$r = '';
		foreach ($data as $value=>$content)
		{
			if(is_array($content))
			{
				$r .= '<optgroup label="'.$value.'">';
				foreach ($content as $key=>$option)
				{
					
					$r .= self::optionList($option, $key,$key==$selected,$htmloptions);
					
				}
				$r .= '</optgroup>';
			}
			else
				$r .= self::optionList($content, $value,$value==$selected,$htmloptions);
		}
		return $r;
	}
	
	public static function optionList($content,$value,$selected=false,$htmloptions=array())
	{
		if ($selected === true)
		{
			$htmloptions['selected'] = 'selected';
		}
		$htmloptions['value'] = $value;
		return self::tag('option',$content,$htmloptions);
	}
	
	public static function input($name,$value,$htmloptions=array())
	{
		$htmloptions['name'] = $name;
		$htmloptions['value'] = $value;
		return self::tag('input',false,$htmloptions);
	}
	
	public static function textArea($name,$content,$htmloptions=array())
	{
		$htmloptions['name'] = $name;
		return self::tag('textarea',$content,$htmloptions);
	}
	
	public static function tag($tag,$content=false,$htmloptions=array())
	{
		$htmloption = '';
		foreach ($htmloptions as $key=>$value)
		{
			$htmloption .= "$key='$value' ";
		}
		if ($content === false)
		{
			return "<$tag $htmloption />";
		}
		else 
		{
			return "<$tag $htmloption>$content</$tag>";
		}
	}
}