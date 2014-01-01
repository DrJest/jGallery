<?php

function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            if(!unlink($file)) return false;
    }
    return rmdir($dir);
}

class fileHandler
{
  private $core;
  private $base;
  
  public function __construct($core)
  {
    $this->base = $_SERVER['DOCUMENT_ROOT'].$core->getOption("pathtoscript")."albums/";
    $this->core = $core;
  }
  
  public function createAlbum($id)
  {
    $dir = $this->base.$id;
    if(is_dir($dir)) {
      
      return true;
    }
    $a = mkdir($dir);
    $t = ( $this->core->getOption("thumbnails") ) ? mkdir($dir."/thumbs") : true;
    return ($a&&$t);
  }
  
  public function deleteAlbum($id)
  {
    $dir = $this->base.$id;
    return rrmdir($dir);
  }
  
  public function deletePic($gal,$pic)
  {
    return unlink($this->base.$gal."/".$pic) && unlink($this->base.$gal."/thumbs/".$pic);
  }
  
  public function addPic($gal)
  {
      $type = explode("/",$_FILES['file']['type'])[1];
      $output_dir = "../albums/".$gal."/";
      if ($_FILES["file"]["error"] > 0)
        return false;
      else
      {
        $filename = md5(microtime().$_FILES['file']["tmp_name"]).".".$type;
        if(!move_uploaded_file($_FILES['file']["tmp_name"],$output_dir. $filename)) return false;

        if($this->core->getOption("thumbnails")) 
          $this->mkthumbnail($output_dir, $filename);
      }
      return $filename;
  }
  
  private function mkthumbnail($dir, $src)
  {
    $core = $this->core;
    $dst = $dir."thumbs/".$src;
    $quality = $core->getOption("jpgquality");
    $size = $core->getOption("thumbsize");
    $hv = $core->getOption("thumbhv");
    $ext = explode(".", $src)[1];
    $info=getimagesize($dir.$src);
    if ($hv=="w"){
      $nw=$size;
      $nh=round(($info[1]*$size)/$info[0], 0);
    }else{
      $nh=$size;
      $nw=round(($info[0]*$size)/$info[1], 0);
    }
    $dst_p=imagecreatetruecolor($nw, $nh);
    switch ($ext) {
      case "png":
        $src_p=imagecreatefrompng($dir.$src);
        imagecopyresampled($dst_p, $src_p, 0,0,0,0,$nw, $nh, $info[0], $info[1]);
        imagepng($dst_p, $dst, intval((float)$quality/11. ));
        break;
      case "gif":
        $src_p=imagecreatefromgif($src);
        imagecopyresampled($dst_p, $src_p, 0,0,0,0,$nw, $nh, $info[0], $info[1]);
        imagegif($dst_p, $dst, $quality);
        break;
      default:
        $src_p=imagecreatefromjpeg($src);
        imagecopyresampled($dst_p, $src_p, 0,0,0,0,$nw, $nh, $info[0], $info[1]);
        imagejpeg($dst_p, $dst, $quality);
    }
    imagedestroy($src_p);		
    imagedestroy($dst_p);	
  }
}

?>
