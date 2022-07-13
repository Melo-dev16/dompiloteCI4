<?php
use App\Libraries\Tools;
    $this->extend('templates/template');
    $tools = new Tools();
    $adminModel = model("AdminModel");

    $this->section('content');
    $unknowns = $adminModel->getUnknownApts();
    $userApts = $adminModel->getUserApts($_SESSION['__sess_dompilote_id']);
    $adminMacs = $adminModel->getAdminMacs($_SESSION['__sess_dompilote_id']);
?>
  
<div class="row">
  <?php
    if($unknowns != NULL):
  ?>  
  <div class="col-md-6 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>MACs non-identifiées <span class="label label-danger" style="color: white;"><?=count($unknowns);?></span></h2>
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
            <?php $i = 1; foreach ($unknowns as $uk): $ukTemps = $adminModel->getApartTemps($uk->apartementId);?>
            <div class="panel">
              <a class="panel-heading collapsed" role="tab" id="unknowHead<?=$uk->host;?>" data-toggle="collapse" data-parent="#accordion" href="#unknowBody<?=$i;?>" aria-expanded="false">
                <h4 class="panel-title"><?=$uk->aptName;?></h4>
              </a>
              <div id="unknowBody<?=$i;?>" class="panel-collapse collapse" role="tabpanel">
                <div class="panel-body">
                  <p>
                    <strong>Adresse MAC:</strong> <?=$uk->host;?>
                  </p>
                  <p>
                    <strong>Données:</strong> <?=count($ukTemps);?>
                  </p>
                  <p>
                    <strong>Dernier relevé:</strong> <?php foreach($ukTemps as $ukt){echo $tools->dateInFrenchFormat($ukt->datetime,true);break; } ?>
                  </p>
                  <p>
                    <a href="#" data-toggle="modal" data-target=".validate-modal<?=$uk->apartementId;?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                    <a href="#" data-toggle="modal" data-target=".merge-modal<?=$uk->apartementId;?>" class="btn btn-primary btn-sm"><i class="fas fa-atom"></i></a>
                    <a onclick="return confirm('Êtes vous sûr de vouloir supprimer cet appartement ?');" href="<?=base_url('delete_appt/'.$uk->apartementId);?>" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
                  </p>
                </div>
              </div>
            </div>

            <div class="modal fade validate-modal<?=$uk->apartementId;?>" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">

                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                    <h4 class="modal-title">Validation de <strong><?=$uk->aptName;?></strong></h4>
                  </div>
                  <div class="modal-body">
                    <form method="POST" action="<?=base_url('edit_apartment/'.$uk->apartementId);?>">
                      <input type="hidden" name="unknown" value="0">
                    <div class="row">
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->aptName;?>" type="text" name="aptName" class="form-control" placeholder="Nom de l'appartement">
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->adress;?>" type="text" name="aptAddr" class="form-control" placeholder="Adresse">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->company;?>" type="text" name="aptCmp" class="form-control" placeholder="Compagnie">
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->state;?>" type="text" name="aptState" class="form-control" placeholder="Etat">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->long;?>" type="text" name="aptLong" class="form-control" placeholder="Coordonnée Longitude">
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->lat;?>" type="text" name="aptLat" class="form-control" placeholder="Coordonnée Latitude">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <select name="aptType" class="form-control">
                            <option value="">Type de l'appartement</option>
                            <option <?=$uk->type == 'F1' ? "selected" : "";?> value="F1">F1</option>
                            <option <?=$uk->type == 'F2' ? "selected" : "";?> value="F2">F2</option>
                            <option <?=$uk->type == 'F3' ? "selected" : "";?> value="F3">F3</option>
                            <option <?=$uk->type == 'F4' ? "selected" : "";?> value="F4">F4</option>
                            <option <?=$uk->type == 'F5' ? "selected" : "";?> value="F5">F5</option>
                            <option <?=$uk->type == 'F6' ? "selected" : "";?> value="F6">F6</option>
                            <option <?=$uk->type == 'F7' ? "selected" : "";?> value="F7">F7</option>
                            <option <?=$uk->type == 'F8' ? "selected" : "";?> value="F8">F8</option>
                            <option <?=$uk->type == 'F9' ? "selected" : "";?> value="F9">F9</option>
                            <option <?=$uk->type == 'F10' ? "selected" : "";?> value="F10">F10</option>
                          </select>
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input readonly value="<?=$uk->host;?>" type="text" name="aptMac" class="form-control" placeholder="Adresse MAC">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->tel1;?>" type="text" name="aptTel1" class="form-control" placeholder="Téléphone 1">
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->tel2;?>" type="text" name="aptTel2" class="form-control" placeholder="Téléphone 2">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->nBat;?>" type="text" name="aptBat" class="form-control" placeholder="N° Batiment (Optionnel)">
                        </p>
                      </div>
                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->nStair;?>" type="text" name="aptStair" class="form-control" placeholder="N° Escalier (Optionnel)">
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          <input value="<?=$uk->nFloor;?>" type="text" name="aptFloor" class="form-control" placeholder="N° Etage (Optionnel)">
                        </p>
                      </div>
                      
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <p>
                          Propriétaire
                        </p>
                        <p>
                          <select name="owner" class="form-control">
                            <?php 
                              foreach($users as $u):
                                if($u->userRole == 'Utilisateur'):
                            ?>
                              <option <?=$adminModel->verifyAdminManage($u->id,$uk->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?> (<?=$u->email;?>)</option>
                            <?php endif;endforeach;?>
                          </select>
                        </p>
                      </div>

                      <div class="col-md-6">
                        <p>
                          Technicien
                        </p>
                        <p>
                          <select name="techs[]" multiple class="form-control chosen-select">
                            <?php 
                              foreach($users as $u):
                                if($u->userRole == 'Technicien'):
                            ?>
                              <option <?=$adminModel->verifyAdminManage($u->id,$uk->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?></option>
                            <?php endif;endforeach;?>
                          </select>
                        </p>
                      </div>
                      <?php if($_SESSION['__sess_dompilote_role'] == "Super Admin"){?>
                      <div class="col-md-6">
                        <p>
                          Gestionnaire
                        </p>
                        <p>
                          <select name="admins[]" multiple class="form-control chosen-select">
                            <?php 
                              foreach($users as $u):
                                if($u->userRole == 'Admin'):
                            ?>
                              <option <?=$adminModel->verifyAdminManage($u->id,$uk->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?></option>
                            <?php endif;endforeach;?>
                          </select>
                        </p>
                      </div>
                      <?php
                      }
                      else{
                        foreach($users as $u):
                          if($u->userRole == 'Admin'):
                            if($adminModel->verifyAdminManage($u->id,$uk->apartementId)){
                      ?>
                      <input type='hidden' name='admins[]' value='<?=$u->id;?>'>
                      <?php 
                          }
                        endif;
                      endforeach;
                       }
                      ?>
                    </div>

                    <p class="text-center">
                      <button type="submit" class="btn btn-success">Valider <i class="fa fa-check"></i></button>
                    </p>

                    </form>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade merge-modal<?=$uk->apartementId;?>" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">

                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                    <h4 class="modal-title">Fusionner <strong><?=$uk->aptName;?></strong></h4>
                  </div>
                  <div class="modal-body">
                    <form method="POST" action="<?=base_url('merge_apartment/'.$uk->apartementId);?>">

                    <div class="row">
                      <div class="col-md-4">
                        <p>
                          Fusionner à
                        </p>
                        <p>
                          <select name="to" class="form-control">
                            <?php foreach($userApts as $ua): if($uk->apartementId != $ua->apartementId && $ua->unknown == 0):?>
                              <option value="<?=$ua->apartementId;?>"><?=$ua->aptName;?> (<?=$ua->host;?>)</option>
                            <?php endif; endforeach;?>
                          </select>
                        </p>
                      </div>

                      <div class="col-md-8">
                        <p>
                          Type de fusion
                        </p>
                        <p>
                          <select name="mergeType" class="form-control">
                            <option value="A">Remplacer la MAC et migrer les données de la nouvelle MAC vers l'appartement cible</option>
                            <option value="B">Remplacer uniquement la MAC</option>
                            <option value="C">Migrer les données de la nouvelle MAC vers la cible</option>
                          </select>
                        </p>
                      </div>

                    </div>

                    <p class="text-center">
                      <button type="submit" class="btn btn-success">Fusionner <i class="fa fa-atom"></i></button>
                    </p>

                    </form>
                  </div>
                </div>
              </div>
            </div>
            <?php $i++; endforeach;?>

          </div>
      </div>
    </div>
  </div>
  <?php
    endif;
  ?>
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Ajouter un appartement</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">


        <!-- Smart Wizard -->
        <div id="wizard" class="form_wizard wizard_horizontal">
          <ul class="wizard_steps">
            <li>
              <a href="#step-1">
                <span class="step_no">1</span>
                <span class="step_descr">
                  <small>Informations de base</small>
                </span>
              </a>
            </li>
            <li>
              <a href="#step-2">
                <span class="step_no">2</span>
                <span class="step_descr">
                    <small>Utilisateurs</small>
                </span>
              </a>
            </li>
            <li>
              <a href="#step-3">
                <span class="step_no">3</span>
                <span class="step_descr">
                    <small>Résumé</small>
                </span>
              </a>
            </li>
          </ul>
          <form method="POST" action="<?=base_url('add_apartment');?>">
          <div id="step-1">
            <div class="form-horizontal form-label-left">

              <div class="row">
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptName" name="aptName" class="form-control" placeholder="Nom de l'appartement">
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptAddr" name="aptAddr" class="form-control" placeholder="Adresse">
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptCmp" name="aptCmp" class="form-control" placeholder="Compagnie">
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptState" name="aptState" class="form-control" placeholder="Etat">
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptLong" name="aptLong" class="form-control" placeholder="Coordonnée Longitude">
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptLat" name="aptLat" class="form-control" placeholder="Coordonnée Latitude">
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <select name="aptType" id="aptType" class="form-control">
                      <option value="">Type de l'appartement</option>
                      <option value="F1">F1</option>
                      <option value="F2">F2</option>
                      <option value="F3">F3</option>
                      <option value="F4">F4</option>
                      <option value="F5">F5</option>
                      <option value="F6">F6</option>
                      <option value="F7">F7</option>
                      <option value="F8">F8</option>
                      <option value="F9">F9</option>
                      <option value="F10">F10</option>
                    </select>
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <select id="aptMac" name="aptMac" class="form-control">
                      <option value="">Adresse MAC</option>
                      <?php 
                        foreach($adminMacs as $macs):
                          $apt = $adminModel->getApartByMac($macs->mac);

                          if(is_null($apt)):
                      ?>
                        <option value="<?=$macs->mac;?>"><?=$macs->mac;?></option>
                      <?php endif;endforeach;?>
                    </select>
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptTel1" name="aptTel1" class="form-control" placeholder="Téléphone 1">
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptTel2" name="aptTel2" class="form-control" placeholder="Téléphone 2">
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptBat" name="aptBat" class="form-control" placeholder="N° Batiment (Optionnel)">
                  </p>
                </div>
                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptStair" name="aptStair" class="form-control" placeholder="N° Escalier (Optionnel)">
                  </p>
                </div>

                <div class="col-md-6">
                  <p>
                    <input type="text" id="aptFloor" name="aptFloor" class="form-control" placeholder="N° Etage (Optionnel)">
                  </p>
                </div>
                
              </div>

              <div class="row">
                <div class="col-md-12 text-center">
                  <p>
                    <button type="button" onclick="checkAptInfos()" class="btn btn-primary">Suivant <i class="fa fa-arrow-right"></i></button>
                  </p>
                </div>
              </div>

            </div>

          </div>
          <div id="step-2">
            <div class="row">
              <div class="col-md-6">
                <p>
                  Propriétaire
                </p>
                <p>
                  <select id="owner" name="owner" class="form-control">
                    <?php 
                      foreach($users as $u):
                        if($u->userRole == 'Utilisateur'):
                    ?>
                      <option value="<?=$u->id;?>"><?=$u->name;?> (<?=$u->email;?>)</option>
                    <?php endif;endforeach;?>
                  </select>
                </p>
              </div>

              <div class="col-md-6">
                <p>
                  Technicien
                </p>
                <p>
                  <select id="techs" name="techs[]" multiple class="form-control chosen-select">
                    <?php 
                      foreach($users as $u):
                        if($u->userRole == 'Technicien'):
                    ?>
                      <option value="<?=$u->id;?>"><?=$u->name;?></option>
                    <?php endif;endforeach;?>
                  </select>
                </p>
              </div>
              <?php if($_SESSION['__sess_dompilote_role'] == "Super Admin"){?>
              <div class="col-md-6">
                <p>
                  Gestionnaire
                </p>
                <p>
                  <select id="admins" name="admins[]" multiple class="form-control chosen-select">
                    <?php 
                      foreach($users as $u):
                        if($u->userRole == 'Admin'):
                    ?>
                      <option value="<?=$u->id;?>"><?=$u->name;?></option>
                    <?php endif;endforeach;?>
                  </select>
                </p>
              </div>
              <?php
              }
              else{
                echo "<input type='hidden' name='admins[]' id='admins' multiple value='".$_SESSION['__sess_dompilote_id']."'>";
              }
              ?>
            </div>

            <div class="row">
              <div class="col-md-12 text-center">
                <p>
                  <button type="button" onclick="$('#wizard').smartWizard('goToStep', 1);" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Précédent</button>
                  <button type="button" onclick="checkManage()" class="btn btn-primary">Suivant <i class="fa fa-arrow-right"></i></button>
                </p>
              </div>
            </div>
          </div>
          <div id="step-3">
              <h2 class="StepTitle">Informations sur l'appartement</h2>
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Nom:</strong></p>
                  <p id="aptName-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Adresse:</strong></p>
                  <p id="aptAddr-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>Compagnie:</strong></p>
                  <p id="aptCmp-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Etat:</strong></p>
                  <p id="aptState-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>Longitude:</strong></p>
                  <p id="aptLong-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Latitude:</strong></p>
                  <p id="aptLat-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>Type:</strong></p>
                  <p id="aptType-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Adresse MAC:</strong></p>
                  <p id="aptMac-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>Téléphone 1:</strong></p>
                  <p id="aptTel1-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Téléphone 2:</strong></p>
                  <p id="aptTel2-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>N° Batiment:</strong></p>
                  <p id="aptBat-txt"></p>
                </div>
                <div class="col-md-6">
                  <p><strong>N° Escalier:</strong></p>
                  <p id="aptStair-txt"></p>
                </div>

                <div class="col-md-6">
                  <p><strong>N° Etage:</strong></p>
                  <p id="aptFloor-txt"></p>
                </div>
              </div>

              <h2 class="StepTitle">Informations sur les utilisateurs</h2>
              <div class="row">
                <div class="col-md-12">
                  <p><strong>Propriétaire:</strong></p>
                  <p id="owner-txt"></p>
                </div>
                <div class="col-md-12">
                  <p><strong>Techniciens:</strong></p>
                  <p id="techs-txt"></p>
                </div>
                <div class="col-md-12">
                  <p><strong>Gestionnaires:</strong></p>
                  <p id="admins-txt"></p>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 text-center">
                  <p>
                    <button type="button" onclick="$('#wizard').smartWizard('goToStep', 2);" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Précédent</button>
                    <button type="submit" class="btn btn-success">Terminer</button>
                  </p>
                </div>
              </div>
          </div>
          </form>

        </div>
        <!-- End SmartWizard Content -->

        <table id="usersDatatable" class="dataTable table table-striped table-bordered">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Adresse</th>
              <th>Admins</th>
              <th>Techniciens</th>
              <th>Propriétaire</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
              <?php foreach($userApts as $ua):?>
                <tr>
                  <td><a style="color: blue;" href="<?=base_url("apartments/".$ua->apartementId);?>"><?=$ua->aptName;?></a></td>
                  <td><?=$ua->adress;?></td>
                  <td>
                  <?php
                    $aptUsers = $adminModel->getApartUsers($ua->apartementId);
                    foreach($aptUsers as $au){
                      if($au->roleId == 1 || $au->roleId == 2){
                        echo $au->name.'<br>';
                      }
                    }
                  ?>
                  </td>
                  <td>
                  <?php
                    $aptUsers = $adminModel->getApartUsers($ua->apartementId);
                    foreach($aptUsers as $au){
                      if($au->roleId == 3){
                        echo $au->name.'<br>';
                      }
                    }
                  ?>
                  </td>
                  <td>
                  <?php
                    $aptUsers = $adminModel->getApartUsers($ua->apartementId);
                    foreach($aptUsers as $au){
                      if($au->roleId == 4){
                        echo $au->name.'<br>';
                      }
                    }
                  ?>
                  </td>
                  <td>
                    <?php if($ua->unknown == 0):?>
                    <a class="btn btn-sm btn-info" data-toggle="modal" data-target=".edit-modal<?=$ua->apartementId;?>" href="#"><i class="fa fa-pencil"></i></a>
                    <a class="btn btn-sm btn-danger" onclick="return confirm('Êtes vous sûr de vouloir supprimer cet appartement ?');" href="<?=base_url('delete_appt/'.$ua->apartementId);?>"><i class="fa fa-trash"></i></a>
                    <?php else: echo "-"; endif;?>
                  </td>
                </tr>

                <div class="modal fade edit-modal<?=$ua->apartementId;?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-close"></i></span>
                          </button>
                          <h4 class="modal-title">Modifier <strong><?=$ua->aptName;?></strong></h4>
                        </div>
                        <div class="modal-body">
                          <form method="POST" action="<?=base_url('edit_apartment/'.$ua->apartementId);?>">

                          <div class="row">
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->aptName;?>" type="text" name="aptName" class="form-control" placeholder="Nom de l'appartement">
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->adress;?>" type="text" name="aptAddr" class="form-control" placeholder="Adresse">
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->company;?>" type="text" name="aptCmp" class="form-control" placeholder="Compagnie">
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->state;?>" type="text" name="aptState" class="form-control" placeholder="Etat">
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->long;?>" type="text" name="aptLong" class="form-control" placeholder="Coordonnée Longitude">
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->lat;?>" type="text" name="aptLat" class="form-control" placeholder="Coordonnée Latitude">
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <select name="aptType" class="form-control">
                                  <option value="">Type de l'appartement</option>
                                  <option <?=$ua->type == 'F1' ? "selected" : "";?> value="F1">F1</option>
                                  <option <?=$ua->type == 'F2' ? "selected" : "";?> value="F2">F2</option>
                                  <option <?=$ua->type == 'F3' ? "selected" : "";?> value="F3">F3</option>
                                  <option <?=$ua->type == 'F4' ? "selected" : "";?> value="F4">F4</option>
                                  <option <?=$ua->type == 'F5' ? "selected" : "";?> value="F5">F5</option>
                                  <option <?=$ua->type == 'F6' ? "selected" : "";?> value="F6">F6</option>
                                  <option <?=$ua->type == 'F7' ? "selected" : "";?> value="F7">F7</option>
                                  <option <?=$ua->type == 'F8' ? "selected" : "";?> value="F8">F8</option>
                                  <option <?=$ua->type == 'F9' ? "selected" : "";?> value="F9">F9</option>
                                  <option <?=$ua->type == 'F10' ? "selected" : "";?> value="F10">F10</option>
                                </select>
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <select name="aptMac" required class="form-control">
                                  <option value="">Adresse MAC</option>
                                  <?php 
                                    foreach($adminMacs as $macs):
                                      $apt = $adminModel->getApartByMac($macs->mac);

                                      if(is_null($apt) || $apt->host == $ua->host):
                                  ?>
                                    <option <?=$ua->host == $macs->mac ? "selected":"";?> value="<?=$macs->mac;?>"><?=$macs->mac;?></option>
                                  <?php endif;endforeach;?>
                                </select>
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->tel1;?>" type="text" name="aptTel1" class="form-control" placeholder="Téléphone 1">
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->tel2;?>" type="text" name="aptTel2" class="form-control" placeholder="Téléphone 2">
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->nBat;?>" type="text" name="aptBat" class="form-control" placeholder="N° Batiment (Optionnel)">
                              </p>
                            </div>
                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->nStair;?>" type="text" name="aptStair" class="form-control" placeholder="N° Escalier (Optionnel)">
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                <input value="<?=$ua->nFloor;?>" type="text" name="aptFloor" class="form-control" placeholder="N° Etage (Optionnel)">
                              </p>
                            </div>
                            
                          </div>

                          <div class="row">
                            <div class="col-md-6">
                              <p>
                                Propriétaire
                              </p>
                              <p>
                                <select name="owner" class="form-control">
                                  <?php 
                                    foreach($users as $u):
                                      if($u->userRole == 'Utilisateur'):
                                  ?>
                                    <option <?=$adminModel->verifyAdminManage($u->id,$ua->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?> (<?=$u->email;?>)</option>
                                  <?php endif;endforeach;?>
                                </select>
                              </p>
                            </div>

                            <div class="col-md-6">
                              <p>
                                Technicien
                              </p>
                              <p>
                                <select name="techs[]" multiple class="form-control chosen-select">
                                  <?php 
                                    foreach($users as $u):
                                      if($u->userRole == 'Technicien'):
                                  ?>
                                    <option <?=$adminModel->verifyAdminManage($u->id,$ua->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?></option>
                                  <?php endif;endforeach;?>
                                </select>
                              </p>
                            </div>
                            <?php if($_SESSION['__sess_dompilote_role'] == "Super Admin"){?>
                            <div class="col-md-6">
                              <p>
                                Gestionnaire
                              </p>
                              <p>
                                <select name="admins[]" multiple class="form-control chosen-select">
                                  <?php 
                                    foreach($users as $u):
                                      if($u->userRole == 'Admin'):
                                  ?>
                                    <option <?=$adminModel->verifyAdminManage($u->id,$ua->apartementId) ? "selected" : "";?> value="<?=$u->id;?>"><?=$u->name;?></option>
                                  <?php endif;endforeach;?>
                                </select>
                              </p>
                            </div>
                            <?php
                            }
                            else{
                              foreach($users as $u):
                                if($u->userRole == 'Admin'):
                                  if($adminModel->verifyAdminManage($u->id,$ua->apartementId)){
                            ?>
                            <input type='hidden' name='admins[]' value='<?=$u->id;?>'>
                            <?php 
                                }
                              endif;
                            endforeach;
                             }
                            ?>
                          </div>

                          <p class="text-center">
                            <button type="submit" class="btn btn-success">Modifier <i class="fa fa-edit"></i></button>
                          </p>

                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
              <?php endforeach;?> 
          </tbody>
        </table>
      </div>
    </div>
  </div>    
</div>

<?php
    if(isset($_GET['unknown'])){
      ?>
      <script type="text/javascript">
        let callback = function() {
          document.getElementById("unknowHead<?=$_GET['unknown'];?>").click();
          window.location.href = "#unknowHead<?=$_GET['unknown'];?>";
          console.log("<?=$_GET['unknown'];?>");
        };
        document.addEventListener("DOMContentLoaded", callback);
      </script>
      <?php
    }
    $this->endSection();
?>