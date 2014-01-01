<?php

require_once("filehandler.class.php");

class phpDB
{
  private $core;
  private $pdo;
  private $file;
  
  public function __construct($core)
  {
    $this->core = $core;
    $dns = $core->getOption("dbtype").':host='.
           $core->getOption("dbhost").' port='.
           $core->getOption("dbport").' dbname='.
           $core->getOption("dbname").' user='.
           $core->getOption("dbuser").' password='.
           $core->getOption("dbpass");
    $this->pdo = $pdo = new PDO($dns);
    $stmt = "CREATE TABLE IF NOT EXISTS gallery_albums (
              id SERIAL NOT NULL,
              date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
              title VARCHAR(20) NOT NULL ,
              description TEXT NOT NULL ,
              PRIMARY KEY (id) )";
    $pdo->query($stmt);
    $this->file = new fileHandler($core);
  }
  
  public function getThumb($gal)
  {
    $urlthumb = "albums/".$gal."/".($this->core->getOption("thumbnails")?"thumbs/":"");
    $count = $this->pdo->query('SELECT count(*) FROM gallery_album_'.$gal.' WHERE index>0')->fetchColumn();
    
    $q = $this->pdo->prepare('SELECT filename FROM gallery_album_'.$gal.' WHERE index=:id LIMIT 1');
    $q->bindParam("id",rand( 1, $count-1 ));
    $q->execute();
    $r = $q->fetchColumn();
    $fn = $urlthumb.$r;

    if(!$r)
      $fn = "http://lh3.ggpht.com/_9nphYWWWTBQ/TLmC-EdNZxI/AAAAAAAAEFY/HpLyVN91AVY/no_image.jpeg";
    if(!$count) 
      $count = 0;
    return [$fn , $count] ;
  }
  
  public function getAlbums($alb = null)
  {
    $pdo = $this->pdo;
    if(!$alb)
      $q = $pdo->prepare("SELECT * FROM gallery_albums ORDER BY date");
    else
    {
      $q = $pdo->prepare("SELECT * FROM gallery_albums WHERE id=:gal LIMIT 1");
      $q->bindParam(':gal', $alb, PDO::PARAM_INT);
    }
    $q->execute();
    $galleries = [];
    foreach($q as $r)
    {
      $tmp =  $r;
      $im = $this->getThumb($r["id"]);
      $tmp["urlthumb"] = $im[0];
      $tmp["picsno"] = $im[1];
      array_push($galleries, $tmp);
    }
    return $alb?($galleries[0]?$galleries[0]:null):$galleries;
  }
  
  public function getPics($gal, $pic = null)
  {
    if (!$pic)
    {
      $q = $this->pdo->prepare('SELECT * FROM gallery_album_'.$gal.' WHERE index>0 ORDER BY date');
    } else {
      $q = $this->pdo->prepare('SELECT * FROM gallery_album_'.$gal.' WHERE index>0 AND id=:pic LIMIT 1');
      $q->bindParam(':pic', $pic, PDO::PARAM_INT);
    }
    $q->execute();
    $pics = [];
    
    foreach($q as $r)
    {
      $tmp = $r;
      $fn = $tmp["filename"];
      $bas = "albums/".$gal."/";
      $tmp["urlthumb"] = $bas.($this->core->getOption("thumbnails")?"thumbs/":"").$fn;
      $tmp["url"] = $bas.$fn;
      array_push($pics, $tmp);
    }
    
    return $pic?(isset($pics[0])?$pics[0]:null):$pics;
  }
  
  public function updatePic($gal, $pic, $caption)
  {
    $q = $this->pdo->prepare('SELECT caption FROM gallery_album_'.$gal.' WHERE id=:pic AND index>0 LIMIT 1');
    $q->bindParam(':pic', $pic, PDO::PARAM_INT);
    $q->execute();
    $r = $q->fetchColumn();
    if($r===false) return "Pic not found";
    if($r==$caption) return true;
    $q = $this->pdo->prepare('UPDATE gallery_album_'.$gal.' SET caption=:caption WHERE id=:pic AND index>0');
    $q->bindParam(':caption', $caption, PDO::PARAM_STR);
    $q->bindParam(':pic', $pic, PDO::PARAM_INT);
    try {
      $q->execute();
    } catch(Exception $e) {
      return $e;
    }
    return true;
  }
  
  public function updateAlbum($gal, $val, $wut)
  {
    $q = $this->pdo->prepare('SELECT '.$wut.' FROM gallery_albums WHERE id=:gal LIMIT 1');
    $q->bindParam(':gal', $gal, PDO::PARAM_INT);
    $q->execute();
    $r = $q->fetchColumn();
    if(!$r) return "Album not found";
    if($r==$val) return true;    
    $q = $this->pdo->prepare('UPDATE gallery_albums SET '.$wut.'=:val WHERE id=:gal');
    $q->bindParam(':val', $val, PDO::PARAM_STR);
    $q->bindParam(':gal', $gal, PDO::PARAM_INT);
    try {
      $q->execute();
    } catch(Exception $e) {
      return $e;
    }
    return true;
  }
  
  public function deleteAlbum($gal)
  {
    $pdo = $this->pdo;
    $c = $pdo->query("SELECT COUNT(*) FROM gallery_albums WHERE id=".$gal)->fetchColumn();
    if(!$c) return "Album not found";
    $pdo->beginTransaction();
    $a = $pdo->exec("DELETE FROM gallery_albums WHERE id=".$gal);
    $i = $pdo->exec("DROP TABLE gallery_album_".$gal);
    if($a===false || $i===false) { $pdo->rollback(); return "Something went wrong"; }
    if(!$this->file->deleteAlbum($gal)) { $pdo->rollback(); return "Error Deleting File"; }
    $pdo->commit();
    return true;
  }
  
  public function deletePic($gal, $pic)
  {
    $pdo = $this->pdo;
    $c = $pdo->query("SELECT filename FROM gallery_album_$gal WHERE id=".$pic);
    if(!$c) return "Album not found";
    $c = $c->fetchColumn();
    if(!$c) return "Pic not found";
    $pdo->beginTransaction(); 
    $maxid = $pdo->query("SELECT id FROM gallery_album_".$gal." ORDER BY id DESC LIMIT 1")->fetchColumn();
    $pdo->query("DELETE FROM gallery_album_".$gal." WHERE id=".$pic);
    if( $maxid != $pic ) 
      $pdo->query("UPDATE gallery_album_".$gal." SET id = ".$pic." WHERE id=".$maxid);
    if(!$this->file->deletePic($gal,$c)) { $pdo->rollback(); return "Error deleting file"; }
    $pdo->commit();
    return true;
  }
  
  public function createAlbum($title,$desc)
  {
    $pdo = $this->pdo;
    $pdo->beginTransaction(); 

    $a = $pdo->prepare("INSERT INTO gallery_albums (title, description) VALUES(?,?)"); 

    $a->execute(array($title, $desc)); 
    
    $id = $pdo->query("SELECT id FROM gallery_albums ORDER BY date DESC LIMIT 1")->fetchColumn();
    
    $q = "CREATE TABLE gallery_album_$id (
          id SERIAL NOT NULL,
          index INT NOT NULL,
          date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
          caption VARCHAR(20) NOT NULL ,
          filename VARCHAR(32) NOT NULL,
          PRIMARY KEY (id) )";
    
    $r = $pdo->query($q);
    $i = $pdo->query("INSERT INTO gallery_album_$id (index,caption,filename) VALUES (0,'Index','none.png')");
    if($r===false || $a === false || $i === false) { $pdo->rollback(); return "Database Error"; }
    if(!$this->file->createAlbum($id)) { $pdo->rollback(); return "Error on creating file"; }
    $pdo->commit();
    return true;
  }
  
  public function addPic($gal, $caption)
  {
    $pdo = $this->pdo;
    
    $rindex = $pdo->query("SELECT MIN(index)+1 FROM gallery_album_$gal WHERE index+1 not in (SELECT index from gallery_album_$gal)")->fetchColumn();
    $pdo->beginTransaction();
    $fn = $this->file->addPic($gal);
    if(!$fn) {$pdo->rollback(); return false;}
    $a = $pdo->query("INSERT INTO gallery_album_$gal (index, caption, filename) VALUES ('$rindex','$caption','$fn')"); 
    if(!$a) return false;
    $pdo->commit();
    return true;
  }
  
}

?>
