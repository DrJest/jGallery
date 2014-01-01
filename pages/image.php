<?php

  $gal = $_REQUEST['album'];
  $pic = $_REQUEST['id'];
  
  $album = $core->getDB()->getAlbums($gal);
  $image = $core->getDB()->getPics($gal,$pic);
  
  $core->getTPL()->assign("album",$album);
  $core->getTPL()->assign("image",$image);
  $core->getTPL()->draw("pages/image");

?>
