<?php
    require_once 'class/gallery.class.php';    
    $core = new phpGallery();
?>
<!doctype html>
<html lang="<?=$core->getLang();?>">
  <head>
    <meta name="author" content="Simone Albano" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="target-densitydpi=device-dpi, width=device-width, initial-scale=1.0, maximum-scale=1">
    <meta charset="utf-8"> 
    <title><?=$core->getSiteName(); ?></title>    
    <script type="application/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<?php
    if(!isset($_REQUEST['view']) || $_REQUEST['view'] == "")
      $page = "home";
    else
      $page = htmlentities($_REQUEST['view']);
    $core->getHeaders($page);
?>
  </head>
<body class="<?=$core->getTPL()->getTemplateVals("body_cls");?>">
<?php
    require_once 'pages/common/header.php';
    require_once 'pages/'.$page.'.php';
    require_once 'pages/common/footer.php';
?>
</body>
</html>
