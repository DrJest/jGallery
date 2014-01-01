<?php
  define("OK", "ok");
  define("ERR","error");
  define("WAT","PLS WAT I GOTTA DO");
  
  if(!function_exists("sendResponse"))
  {
    require_once("../class/gallery.class.php");
    function sendResponse($sta,$mes,$new=false)
    {
      if($new)
        $a = ["status"=>$sta,"message"=>$mes,"newvalue"=>$new];
      else
        $a = ["status"=>$sta,"message"=>$mes];
      $j = json_encode($a);
      die($j);
    }
  $core = new phpGallery();
  }
  $db = $core->getDB();
  
  if(!$core->isLogged()) sendResponse(ERR,"You are not logged");
	if(!isset($_REQUEST['action'])) sendResponse(ERR,WAT);
  
    
  if($_REQUEST['action']=="create")
  {
    if(!isset($_REQUEST['title']) || trim($_REQUEST['title'])=="") sendResponse(ERR, "Missing Title");
    $desc = (isset($_REQUEST['desc'])) ? htmlentities(trim($_REQUEST['desc'])) : "";
    $r = $db->createAlbum(htmlentities(trim($_REQUEST['title'])),$desc);
    ($r===true) ? sendResponse(OK,"Album Created") : sendResponse(ERR,$r);
  }
  
  if(!isset( $_REQUEST['id']) ||  !empty(trim($_REQUEST['id'], '0123456789')) ) sendResponse(ERR,"Missing or invalid id");
    $id   = $_REQUEST['id'];
  
  if($_REQUEST['action']=="upload")
  {
    if(!isset($_FILES["file"])) sendResponse(ERR,"No file specified");
    $type = explode("/",$_FILES['file']['type'])[1];
    if(!in_array($type,$core->getOption("allowedextensions")) && $type!="zip") sendResponse(ERR, "Filetype not allowed");
    $caption = isset($_REQUEST['caption']) ? $_REQUEST['caption'] : "";
    if($type!="zip")
      $r = $db->addPic($_REQUEST['id'], $caption);
    ($r) ? sendResponse("ok","Image(s) uploaded") : sendResponse(ERR,"Error uploading file");
  }
  
  if ($_REQUEST['action']=="edit"){
    if(!isset($_REQUEST['role'])) sendResponse(ERR,"Missing Role");
    if(!isset($_REQUEST['val'])) sendResponse(ERR,"Missing Value");
    
    $role = htmlentities(trim($_REQUEST['role']));
    $val  = htmlentities(trim($_REQUEST['val']));
    $r;
    switch($role)
    {
      case "album-title":
        $r = $db->updateAlbum($id,$val,"title");
        break;
      case "album-desc":
        $r = $db->updateAlbum($id,$val,"description");      
        break;
      case "pic-caption":
        if(!isset($_REQUEST['gid']) || !empty(trim($_REQUEST['gid'], '0123456789')) ) sendResponse(ERR,"Missing Album Id");
        $gid = $_REQUEST['gid'];
        $r = $db->updatePic($gid,$id,$val);
        break;
      default:
        sendResponse(ERR,"Invalid Role");
    }
    ($r===true) ? sendResponse(OK,"OK",$val) : sendResponse(ERR,$r);
  }
  
  if($_REQUEST['action']=="del")
  {
    if(!isset($_REQUEST['gid']))
      $r = $db->deleteAlbum($id);
    else {
      if( !empty (trim ($_REQUEST['gid'], '0123456789'))) sendResponse(ERR,"Invalid Album id");
      $r = $db->deletePic($_REQUEST['gid'], $id);
    }
    ($r===true) ? sendResponse(OK,"OK") : sendResponse(ERR,$r);
  }
  
  sendResponse(ERR,WAT);

?>
