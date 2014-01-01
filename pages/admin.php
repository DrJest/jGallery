<?php

  if (isset($_REQUEST['json']))
  {
    if(isset($_REQUEST['edit']) && isset($_REQUEST['val']))
    { ?>
      
      <form action="?view=admin&json&action=edit" method="POST">
        <input type="text"   name="val"  value="<?=$_REQUEST['val'];?>">
        <input type="hidden" name="id"  value="<?=$_REQUEST['edit'];?>">
        <input type="hidden" name="role" value="album-title">
        <input type="submit">
      </form>
      
      <?php
      die();
    }
    
    function sendResponse($sta,$mes,$new="")
    {
      echo $sta,": ",$mes;
      if(isset($_REQUEST['gid']))
        header("Refresh: 3; url=?view=admin&action=album&id=".$_REQUEST['gid']);
      else
        header("Refresh: 3; url=?view=admin");
      die();
    }
    
    require("admin.json.php");
  }
  
	if (isset($_REQUEST['action']) && $_REQUEST['action']=="logout"){
		unset($_SESSION['user']);
		unset($_SESSION['pass']);
    header("location: ?view=admin");  
	}
  
	// if login
	if (isset($_REQUEST['action']) && $_REQUEST['action']=="login"){
		if ($_REQUEST['mglogin']==$core->getOption("adminlogin") && $_REQUEST['mgpwd']==$core->getOption("adminpwd") ){
			$_SESSION['user']=md5($core->getOption("adminlogin"));
			$_SESSION['pass']=md5($core->getOption("adminpwd"));
		} 
    header("location: ?view=admin");
	}
  
  	// if not logged in -> admin login form	
	if (!$core->isLogged()){ ?>
		<form style='width: 300px; margin: auto' name='loginform' method='post' action=''>
		<strong>Admin Control Panel </strong><br>
		Username  <input name='mglogin' type='text' size='20'><br>
		Password  <input name='mgpwd' type='password' size='20'>
		<input type='submit' name='Submit' value='Login'>
		<input name='action' type='hidden' id='action' value='login'><br>
		<a href='?'><-Index</a>
		</form>
<?php
		die();
	}

  if($core->getOption("useajax")) echo '<script src="pages/admin.js"></script>';
  
  if( isset($_REQUEST['action']) && $_REQUEST['action']=="album" && isset($_REQUEST['id']) )
  {
    $gal = $core->getDB()->getAlbums($_REQUEST['id']);
    $pics = $core->getDB()->getPics($_REQUEST['id']);
    
    if(!$gal) header("location: ?view=admin");
    
    echo "<h2>" . $gal['title'] . "</h2><span id='message'></span>";
    echo "<table id='main' data-gid='".$gal["id"]."'>";
    echo "<thead><th>Id</th><th>Title</th><th>Url</th><th>Preview</th><th>Del</th></thead>";
    foreach( $pics as $pic )
    {?>
      <tr data-id="<?=$pic["id"];?>">
      <td><?=$pic["id"];?></td>
      <td>
        <form data-role="pic-caption" method="post" action="?view=admin&json&action=edit&role=pic-caption&id=<?=$pic["id"];?>&gid=<?=$gal["id"];?>">
          <input style="width:100%" name="val" value="<?=$pic["caption"];?>">
        </form>
      </td>
      <td> <a target="_blank" href="<?=$pic["url"];?>"> <?=$pic["url"];?> </a> </td>
      <td> <a target="_blank" href="<?=$pic["urlthumb"];?>"> <img style="max-height: 30px" src="<?=$pic["urlthumb"];?>"> </a> </td>
      <td> <a class="delete" href="?view=admin&json&action=del&gid=<?=$gal["id"];?>&id=<?=$pic['id']?>">X</a> </td>
      </tr>
    <?php
    }  
    echo <<<DH
    </table>
        
    <center style="height:1.2em"><span id="message"></span></center>
    <hr>
DH;
    if($core->getOption("useajax")) echo <<<DH
    <script src="http://malsup.github.com/jquery.form.js"></script>
    <style>
      form#upload { display: block; margin: 20px auto; background: #eee; border-radius: 10px; padding: 15px }
      #progress { position:relative; width:400px; border: 1px solid #ddd; padding: 1px; border-radius: 3px; }
      #bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
      #percent { position:absolute; display:inline-block; top:3px; left:48%; }
    </style>
DH;
    $id = $gal['id'];
    echo <<<DH
    <form id="upload" action="pages/admin.json.php" method="post" enctype="multipart/form-data">
    <table>
      <tr>
        <td> <input type="file" size="60" name="file"> </td>
        <td> <input style="width:100%" type="text" name="caption" placeholder="Caption"> </td>
        <td id="progress">
          <div id="bar"></div>
          <div id="percent">0%</div >
        </td>
        <td> <input type="submit" name="action" value="upload"> </td>
      </tr>
    </table>
    <center><a href="?view=admin">Back</a> | <a href="?view=admin&action=logout">Logout</a></center>
    <input type="hidden" value="$id" name="id">
    </form>
DH;
    die();  
  }
  
  $gals = $core->getDB()->getAlbums();
  
  echo "<table id='main'>";
  echo "<thead><th>Id</th><th>Title</th><th>Description</th><th>Foto</th><th>Del</th></thead>";
    
  foreach( $gals as $gal )
  { ?>
    <tr data-id="<?=$gal["id"];?>">
    <td> <?=$gal["id"];?> </td>
    <td>
      <div>
        <a href="?view=admin&action=album&id=<?=$gal["id"];?>"> <?=$gal["title"];?> </a>
        <a href="?view=admin&json&edit=<?=$gal["id"];?>&val=<?=$gal["title"];?>" class="small" style="font-size: 60%;float:right; cursor: pointer" 
          data-id="<?=$gal["id"];?>">[edit]</a>
      </div>
    </td>
    <td> 
      <form data-role="album-desc" method="POST" action="?view=admin&json&action=edit&role=album-desc&id=<?=$gal["id"];?>">
        <input style="width:100%" name="val" value="<?=$gal["description"];?>">
      </form>
    </td>
    <td> <?=$gal["picsno"];?></td>
    <td> <a class="delete" href="?view=admin&json&action=del&id=<?=$gal["id"];?>">X</a></td>
    </tr>
  <?php
  }
  
  if (!$gals)
    echo '<tr><td colspan="5"><center>NO ALBUMS FOUND</center></td></tr>';
    
  echo <<<HD
  </table>
  <center style="height:1.2em"><span id="message"></span></center>
  <hr>
  <form id="create" method="post" action="?view=admin&json&action=create">
    <table>
      <tr>
        <th colspan="3">Create new album</th>
      </tr>
      <tr>
        <td> <input type="text" style="width:100%" placeholder="Title" name="title"> </td>
        <td> <input type="text" style="width:100%" placeholder="Description" name="desc"> </td>
        <td style="width:100px"> <input type="submit" value="Create"> </td>
      </tr>
    </table>
  </form>
  <center><a href="?">Index</a> | <a href="?view=admin&action=logout">Logout</a></center>
HD;
?>
