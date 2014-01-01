<?php
  $gal = $_REQUEST['id'];
  
  $pics = $core->getDB()->getPics($gal);
  $album= $core->getDB()->getAlbums($gal);
  
  $core->getTPL()->assign("pics",$pics);
  $core->getTPL()->assign("album",$album);
  $core->getTPL()->draw('pages/album');
?>
