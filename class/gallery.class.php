<?php
/***
 * 
 * core.class.php
 * 
 */

require_once 'template.class.php';
require_once 'db.class.php';
require_once 'filedb.class.php';
session_start();

class phpGallery
{
  private $tpl;
  private $lang;
  private $albums;
  private $options;
  private $tplvals;
  private $db;
  public  $galfile;
  
  public function __construct()
  {
    $this->tpl = new phpTemplate();
    $this->options = include 'config.inc.php';
    $this->lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : $this->options['lang'];
    $tno = (isset($_SESSION["tpl"])?$_SESSION["tpl"]:"0");
    $this->tpl->configure('tpl_dir',$_SERVER['DOCUMENT_ROOT'].$this->options['pathtoscript'].'tpl/'.$tno."/");
    $this->tpl->setTemplateVals($this->lang);
    $this->galfile = $_SERVER['DOCUMENT_ROOT'].$this->options['pathtoscript']."galleries.dat";
    $this->getTPL()->assign("gallery",[
      "title" => $this->getOption("sitename"),
      "url"   => $this->getOption("pathtoscript")
    ]);
    if ($this->options['usesql'])
      $this->db = new phpDB($this); 
    else
      $this->db = new phpFile($this);
  }
  
  public function getTPL()
  {
    return $this->tpl;
  }
  
  public function getDB()
  {
    return $this->db;
  }
  
  public function getLang()
  {
    return $this->lang;
  }
  
  public function getOption( $opt )
  {
    if(!$opt) return "";
    return $this->options[$opt];
  }
  
  public function getHeaders($page = "default")
  {
    $tpl = $this->getTPL();
    $tv = $tpl->getTemplateFiles();
    $css = $tv["css"];
    $js = $tv["js"];
    $styles = [];
    $script = [];
    
    //CSS
    if(is_array($css["default"]))
      foreach( $css["default"] as $value )
        array_push($styles,$value);
    else 
      array_push($styles,$css["default"]);

    if($page!="default" && array_key_exists($page, $css) ) 
    {
      if(is_array($css[$page]))
        foreach( $css[$page] as $value )
          array_push($styles,$value);
      else 
        array_push($styles,$css[$page]);
    }
    //JS
    if(is_array($js["default"]))
      foreach( $js["default"] as $value )
        array_push($script,$value);
    else 
      array_push($script,$js["default"]);

    if($page!="default" && array_key_exists($page, $js) ) 
    {
      if(is_array($js[$page]))
        foreach( $js[$page] as $value )
          array_push($script,$value);
      else 
        array_push($script,$js[$page]);
    }

    foreach ($script as $var)
      if(filter_var($var,FILTER_VALIDATE_URL,FILTER_FLAG_PATH_REQUIRED))
        echo '    <script type="text/javascript" src="',$var,'"></script>'."\n";
      else
        echo '    <script type="text/javascript" src="',$tpl->getTemplateDir(),$var,'"></script>'."\n";
    
    foreach ($styles as $var)
      if(filter_var($var,FILTER_VALIDATE_URL,FILTER_FLAG_PATH_REQUIRED))
        echo '    <link rel="stylesheet" type="text/css" href="',$var,'" />'."\n";
      else
        echo '    <link rel="stylesheet" type="text/css" href="',$tpl->getTemplateDir(),$var,'" />'."\n";
  }
  
  public function getSiteName()
  {
    return $this->options['sitename'];
  }
  
  public function isLogged() {
    return (isset($_SESSION['user']) && isset($_SESSION['pass']) && $_SESSION['user']==md5($this->getOption("adminlogin")) && $_SESSION['pass']==md5($this->getOption("adminpwd")));
  }
  
}

?>
