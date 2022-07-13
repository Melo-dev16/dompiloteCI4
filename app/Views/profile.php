<?php
use App\Libraries\Tools;
    $this->extend('templates/template');
    $adminModel = model("AdminModel");
    $tools = new Tools();
    $this->section('content');
?>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Profil de <strong><?=$_SESSION["__sess_dompilote_name"];?></strong></h2> 
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="row">
          <div class="col-md-6">
            <h4 style="font-weight: bolder;">Nom</h4>
            <p><?=$_SESSION["__sess_dompilote_name"];?></p>
            <h4 style="font-weight: bolder;">Adresse E-mail</h4>
            <p><?=$_SESSION["__sess_dompilote_email"];?></p>
            <h4 style="font-weight: bolder;">Rôle</h4>
            <p><?=$_SESSION["__sess_dompilote_role"];?></p>
            <?php
              $userApts = $adminModel->getUserApts($_SESSION["__sess_dompilote_id"]);

              if(count($userApts) == 0){
                echo "<h4>Aucun appartement</h4>";
              }
              else{
                ?>
                <h4 style="font-weight: bolder;">Appartements</h4>
                <?php
                foreach ($userApts as $ua) {
                  ?>
                  <a href="<?=base_url('apartments/'.$ua->apartementId);?>"><?=$ua->aptName;?></a>,
                  <?php
                }
              }
            ?>
            <p><br><a href="#" data-toggle="modal" data-target=".edit-modal" class="btn btn-primary">Modifier <i class="fa fa-edit"></i></a></p>
          </div>
          <div class="col-md-6">
            <form action="<?=base_url('user_edit_password');?>" method="POST">
              <div class="form-group">
                <div class="col-md-12">
                  <input type="password" name="old" required class="form-control" placeholder="Ancien Mot de passe">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <input style="margin-top: 1em;" type="password" name="pwd" required class="form-control" placeholder="Nouveau Mot de passe">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <input style="margin-top: 1em;" type="password" name="confirm" required class="form-control" placeholder="Confirmation Nouveau Mot de passe">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <p style="text-align: center;">
                    <button style="margin-top: 1.5em;" type="submit" class="btn btn-success">Modifier <i class="fa fa-edit"></i></button>
                  </p>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-close"></i></span>
        </button>
        <h4 class="modal-title" id="myModalLabel2">Modifier <strong><?=$_SESSION["__sess_dompilote_name"];?></strong></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form action="<?=base_url('user_edit_infos');?>" method="POST">
              
              <div class="form-group">
                <div class="col-md-12">
                  <input type="text" name="name" required class="form-control" placeholder="Nom" value="<?=$_SESSION["__sess_dompilote_name"];?>">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <input value="<?=$_SESSION["__sess_dompilote_email"];?>" type="email" style="margin-top: 1.5em;" name="email" required class="form-control" placeholder="Adresse E-mail">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <button style="margin-top: 1.5em;" type="submit" class="btn btn-success">Modifier <i class="fa fa-pencil"></i></button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php
    $this->endSection();
?>