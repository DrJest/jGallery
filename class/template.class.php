<?php
require_once "raintpl.class.php";

class phpTemplate extends RainTPL 
{  
  public function setTemplateVals($lang = null) {
    $tv = json_decode( file_get_contents(self::$tpl_dir."template.values") , true);
    if(!$tv) die("Error: ".json_last_error_msg()." in template.values");
    $this->template_files = $tv["files"];
    $this->template_vals = $tv["values"];
    
    if ($lang && in_array($lang, $tv["values"]["langs"]))
      $this->template_lang = $lang;
    else 
      $this->template_lang = $tv["values"]["langs"][0];
      
    $url = self::$tpl_dir."langs/".$this->template_lang.".json";
    $lng = json_decode( file_get_contents ( $url ),  true );
    $this->assign( "lang", $lng );
  }
  
  public function getTemplateVals($val=null) 
  {
    if(!$val)
      return $this->template_vals;
    else
      if(array_key_exists($val,$this->template_vals))
        return $this->template_vals[$val];
      else
        return "";
  }
  
  public function getTemplateFiles($type=null)
  {
    if($type=="js" || $type=="css")
      return $this->template_files[$type];
    return $this->template_files;
  }
  
  public static function getTemplateDir()
  {
    return str_replace($_SERVER['DOCUMENT_ROOT'],"",self::$tpl_dir);
  }
  
  private $template_vals;
  private $template_files;
  private $template_lang;
  private $template_number;
  private $template_name;
  private $template_author;
  private $template_version;
}

?>
