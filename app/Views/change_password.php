<?php
  use App\Libraries\Tools;
  $tools = new Tools();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bienvenue sur DOMPilote Smart Home</title>

    <link rel="icon" href="<?=base_url();?>/favicon.png" type="image/png" />

    <!-- Bootstrap -->
    <link href="<?=base_url();?>/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?=base_url();?>/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?=base_url();?>/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="<?=base_url();?>/vendors/animate.css/animate.min.css" rel="stylesheet">

    <link href="<?=base_url();?>/vendors/pnotify/dist/pnotify.css" rel="stylesheet">
    <link href="<?=base_url();?>/vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet">
    <link href="<?=base_url();?>/vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?=base_url();?>/build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      
      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form action="<?=base_url('reset_password');?>" method="POST">
              <h2>Changer votre mot de passe</h2>
              <div>
                <input name="email" type="hidden" value="<?=$email;?>" required />
                <input placeholder="Entrez le nouveau mot de passe" name="pwd" type="password" class="form-control" required />
                <input placeholder="Confirmez le nouveau mot de passe" name="confirm" type="password" class="form-control" required />
              </div>
              <div>
                <button type="submit" id="loginBtn" class="btn btn-default submit"><span>Confirmer</span></button>
              </div>

              <div class="clearfix"></div>

              <div class="separator">

                <div class="clearfix"></div>
                <br />
                <p class="text-center">
                  <img alt="logo" src="<?=base_url();?>/logo.png" width="250" height="70">
                </p>
                <div>
                  <p>&copy; 2022 All Rights Reserved. DOMPilote Smart Home</p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>

      </div>
    </div>

    <script src="<?=base_url('vendors/jquery/dist/jquery.min.js');?>"></script>
    <script src="<?=base_url('vendors/pnotify/dist/pnotify.js');?>"></script>
    <script src="<?=base_url('vendors/pnotify/dist/pnotify.buttons.js');?>"></script>
    <script src="<?=base_url('vendors/pnotify/dist/pnotify.nonblock.js');?>"></script>
    <script src="<?=base_url('login.js');?>"></script>
    <script>
      $(document).ready(function(){
          $(".disclaimer").hide();
        })
    </script>
    <?php
        if(isset($_SESSION['alert'])){
          echo $tools->setNotif($_SESSION['alert']['title'],$_SESSION['alert']['text'],$_SESSION['alert']['type']);
        }
    ?>
  </body>
</html>
