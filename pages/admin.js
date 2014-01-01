$(document).ready(function(){
  
  $("#main").on("click",".small",function(e){
    e.preventDefault();
    form = $("<form>").data("id",$(this).data("id")).data("role","album-title")
                      .html('<input style="width:100%" value="'+$(this).prev().text()+'">')
    $(this).parent().hide().parent().append(form);
  }).on("submit","form",function(e){
    e.preventDefault();
    $this = $(this);
    $role = $this.data("role");
    $tr=$this.parent().parent();
    $id = $tr.data("id");
    $gid = $("#main").data("gid");
    $val = $this.children().first().val();
    $.ajax({
      type: "POST",
      url: "pages/admin.json.php?action=edit",
      data: { role:$role , id:$id, val:$val, gid:$gid },
      success: function(data){
        if(data.status=="ok")
        {
          if($this.data("role")=="album-title") 
          {
            $this.parent().children().first().show().children().first().text(data.newvalue);
            $this.remove();
          }
          $this.find("input:text").blur()
          $("#message").text(data.message).show();
        }
        else $("#message").text("Error:"+data.message).show();
        window.setTimeout(function(){$("#message").fadeOut()},800);
      },
      dataType: "json"
    });
  }).on("click",".delete", function(e){
    e.preventDefault();
    $this=$(this);
    $tr=$this.parent().parent();
    $id=$tr.data("id");
    $gid=$("#main").data("gid");
    $.ajax({
      type: "POST",
      url: "pages/admin.json.php?action=del",
      data: { id:$id, gid:$gid },
      success: function(data){
        if(data.status=="ok")
        {
          $("#message").text(data.message).show();
          $tr.remove();
        }
        else $("#message").text("Error:"+data.message).show();
        window.setTimeout(function(){$("#message").fadeOut()},800);
      },
      dataType: "json"
    });
  })
  
  $("#create").on("submit",function(e){
    e.preventDefault();
    $this=$(this);
    $.ajax({
      type: "POST",
      url: "pages/admin.json.php?action=create",
      data: $(this).serialize(),
      success: function(data){
        if(data.status=="ok")
        {
          $this.find("input:text").val("").blur()
          $("#message").text(data.message).show();
          $( "#main" ).load( "index.php?view=admin #main" );
        }
        else $("#message").text("Error:"+data.message).show();
        window.setTimeout(function(){$("#message").fadeOut()},800);
      },
      dataType: "json"
    });
  });
  
  var options = { 
    beforeSend: function() 
    {
    	$("#progress").show();
    	$("#bar").width('0%');
    	$("#message").html("");
		$("#percent").html("0%");
    },
    uploadProgress: function(event, position, total, percentComplete) 
    {
    	$("#bar").width(percentComplete+'%');
    	$("#percent").html(percentComplete+'%');
    },
    success: function() 
    {
      $("#bar").width('100%');
    	$("#percent").html('100%');
    },
    complete: function(response) 
    {
      json = $.parseJSON(response.responseText);
      $("#message").html("<font color='green'>"+json.message+"</font>");
      $( "#main" ).load( "index.php?view=admin&action=album&id="+$("#main").data("gid")+" #main" );
    },
    error: function()
    {
      $("#message").html("<font color='red'> ERROR: unable to upload file(s)</font>");
    }
  }; 
  
  if($("#upload").length)
    $("#upload").ajaxForm(options);
  
});
