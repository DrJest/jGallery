<?php

  $vals = [];

  $albums = $core->getDB()->getAlbums();
  
  $vals['gpp'] = $gpp = $core->getOption("gpp") ? $core->getOption("gpp") : count($albums);
  
  $vals['curpage'] = $curpage = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1 ;
    
  $vals['numalbums'] = $num = count($albums);
  
  $vals['numpages'] = floor( $num / $gpp ) + 1;
   
  $vals['albums'] = array_slice($albums, ($curpage-1)*$gpp, $gpp);
  
  $core->getTPL()->assign($vals);
  $core->getTPL()->draw('pages/home');

?>
