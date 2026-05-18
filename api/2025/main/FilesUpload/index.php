<?php
header("Access-Control-Allow-Origin: *");
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style type="text/css">
<!--

@font-face

/*perintah untuk memanggil font eksternal*/
    {
    font-family: 'Poppins-SemiBold';
    /*memberikan nama bebas untuk font*/
    src: url('webfonts/Poppins-SemiBold.otf');
    /*memanggil file font eksternalnya di folder nexa*/
}

body {
    font-family: 'Poppins-SemiBold', verdana;
    font-weight: 400;
	font-size: 12px;
}
h1, h2, h3, h4, h5, h6 {
    font-family: 'Poppins-SemiBold', verdana;
}
#btn-back-to-top {
  position: fixed;
  bottom: 10px;
  right: 10px;
  display: none;
  background-color:#000000;
  border:#000000 solid 1px;
} 
</style>  
<style>
/*Eliminates padding, centers the thumbnail */


/* Styles the thumbnail */

a.lightbox img {
height: 100px;
border: 3px solid white;
box-shadow: 0px 0px 8px rgba(0,0,0,.3);


}
/* Styles the lightbox, removes it from sight and adds the fade-in transition */

.lightbox-target {
position: absolute;
top: 0%;
width: 100%;
left:0;
background: rgba(0,0,0,.9);
z-index:1000;
width: 100%;
opacity: 0;
-webkit-transition: opacity .5s ease-in-out;
-moz-transition: opacity .5s ease-in-out;
-o-transition: opacity .5s ease-in-out;
transition: opacity .5s ease-in-out;
overflow: hidden;
}

/* Styles the lightbox image, centers it vertically and horizontally, adds the zoom-in transition and makes it responsive using a combination of margin and absolute positioning */

.lightbox-target img {
margin: auto;
position: absolute;
top: 0;
left:0;
right:0;
bottom: 0;
max-height: 0%;
max-width: 0%;
border: 3px solid white;
box-shadow: 0px 0px 8px rgba(0,0,0,.3);
box-sizing: border-box;
-webkit-transition: .5s ease-in-out;
-moz-transition: .5s ease-in-out;
-o-transition: .5s ease-in-out;
transition: .5s ease-in-out;
}

/* Styles the close link, adds the slide down transition */

a.lightbox-close {
display: block;
width:50px;
height:50px;
box-sizing: border-box;
background: white;
color: black;
text-decoration: none;
position: absolute;
top: 0px;
right: 0;
-webkit-transition: .5s ease-in-out;
-moz-transition: .5s ease-in-out;
-o-transition: .5s ease-in-out;
transition: .5s ease-in-out;
}

/* Provides part of the "X" to eliminate an image from the close link */

a.lightbox-close:before {
content: "";
display: block;
height: 30px;
width: 1px;
background: black;
position: absolute;
left: 26px;
top:10px;
-webkit-transform:rotate(45deg);
-moz-transform:rotate(45deg);
-o-transform:rotate(45deg);
transform:rotate(45deg);
}

/* Provides part of the "X" to eliminate an image from the close link */

a.lightbox-close:after {
content: "";
display: block;
height: 30px;
width: 1px;
background: black;
position: absolute;
left: 26px;
top:10px;
-webkit-transform:rotate(-45deg);
-moz-transform:rotate(-45deg);
-o-transform:rotate(-45deg);
transform:rotate(-45deg);
}

/* Uses the :target pseudo-class to perform the animations upon clicking the .lightbox-target anchor */

.lightbox-target:target {
opacity: 1;
top: 0;
bottom: 0;
}

.lightbox-target:target img {
max-height: 100%;
max-width: 100%;
}

.lightbox-target:target a.lightbox-close {
top: 100px;
}


</style>

    </head>
    <body>

        <div class="container">
            <div class="card">
            <input type="text" class="form-control form-control-sm" id="filenya_slider" name="filenya_slider" placeholder="File Slider">
                <div class="card-header" style="font-family: 'Poppins-SemiBold', verdana;">Drag & Drop File Here</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">&nbsp;</div>
                        <div class="col-md-6">
                            <div id="drag_drop" style="font-family: 'Poppins-SemiBold', verdana;">Drag & Drop File Here</div>
                        </div>
                        <div class="col-md-3">&nbsp;</div>
                    </div>
                </div>
            </div>
            <br />
            <div class="progress" id="progress_bar" style="display:none; height:50px;">
                <div class="progress-bar bg-success" id="progress_bar_process" role="progressbar" style="width:0%; height:50px;">0%

                </div>
            </div>
            <div id="uploaded_image" class="row mt-5"></div>
        </div>
    </body>
</html>

<style>

#drag_drop{
    background-color : #f9f9f9;
    border : #ccc 4px dashed;
    line-height : 250px;
    padding : 12px;
    font-size : 24px;
    text-align : center;
}

</style>

<script>

function _(element)
{
    return document.getElementById(element);
}

_('drag_drop').ondragover = function(event)
{
    this.style.borderColor = '#333';
    return false;
}

_('drag_drop').ondragleave = function(event)
{
    this.style.borderColor = '#ccc';
    return false;
}


_('drag_drop').ondrop = function(event)
{
    event.preventDefault();

    var form_data  = new FormData();

    var image_number = 1;

    var error = '';

    var drop_files = event.dataTransfer.files;
    
    for(var count = 0; count < drop_files.length; count++)
    {

            form_data.append("images[]", drop_files[count]);


        image_number++;
    }

    if(error != '')
    {
        _('uploaded_image').innerHTML = error;
        _('drag_drop').style.borderColor = '#ccc';
    }
    else
    {
        _('progress_bar').style.display = 'block';

        var ajax_request = new XMLHttpRequest();

        ajax_request.open("post", "upload.php");

        ajax_request.upload.addEventListener('progress', function(event){

            var percent_completed = Math.round((event.loaded / event.total) * 100);

            _('progress_bar_process').style.width = percent_completed + '%';

            _('progress_bar_process').innerHTML = percent_completed + '% completed';

        });

        ajax_request.addEventListener('load', function(event){

            _('uploaded_image').innerHTML = '<div class="alert alert-success">Files Uploaded Successfully</div>';

            _('drag_drop').style.borderColor = '#ccc';
			 
             //alert(drop_files[0].name);
			 //window.parent.history.replaceState('', '', 'simfastjateng-bdi?ket=success');
			 //window.parent.location.reload();

        });

        ajax_request.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                //alert(this.responseText.replace("success", ""));
                document.getElementById('filenya_slider').value=this.responseText.replace("success", "");
            }
        }

        ajax_request.send(form_data);
    }
}

</script> 
<script>
    $(document).ready(function(){
        alert(window.location.href);
    });
</script>