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
        <h2>Liste des MACs</h2> 
        <ul class="nav navbar-right panel_toolbox">
          <li>
            <a href="#" data-toggle="modal" data-target=".add-modal"><i style="color:blue!important;" class="fa fa-plus"></i></a>
          </li>
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table id="usersDatatable" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>MAC</th>
              <th>Admin</th>
              <th>Appartement</th>
              <th>Actions</th>
            </tr>
          </thead>


          <tbody>
              <?php
                $i = 1;
                foreach ($macs as $m):
              ?>
              <tr>
                <td><?=$i;?></td>
                <td><?=$m->mac;?></td>
                <td><?=$m->name;?></td>
                <td>
                  <?php 
                    if($m->deletedAt != NULL){
                      echo "<span class='text-danger'>Désactivé</span>";
                    }
                    else{
                      $apt = $adminModel->getApartByMac($m->mac);

                      if($apt == NULL){
                        echo "<span class='text-success'>Inactif</span>";
                      }
                      else{
                        echo "<a href='".base_url("apartments/".$apt->id)."'><span class='text-primary'>".$apt->aptName."</span></a>";
                      }
                    }
                    
                  ?>    
                </td>
                <td>
                  <?php if($m->deletedAt != NULL):?>
                  <a class="btn btn-sm btn-primary" onclick="return confirm('Êtes vous sûr de vouloir restaurer cette adresse MAC ?');" href="<?=base_url('undelete_mac?mac='.$m->mac);?>"><i class="fa fa-refresh"></i></a>
                <?php else:?>
                  <a class="btn btn-sm btn-info" data-toggle="modal" data-target=".edit-modal<?=$m->mac;?>" href="#"><i class="fa fa-pencil"></i></a>
                  <a class="btn btn-sm btn-danger" onclick="return confirm('Êtes vous sûr de vouloir supprimer cette adresse MAC ? Attention l\'appartement lié à cette MAC sera aussi supprimé. Pour restaurer l\'appartement vous devrez restaurer la MAC.');" href="<?=base_url('delete_mac?mac='.$m->mac);?>"><i class="fas fa-exclamation-triangle"></i></a>
                <?php endif;?>
                </td>
              </tr>

              <div class="modal fade edit-modal<?=$m->mac;?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">

                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-close"></i></span>
                      </button>
                      <h4 class="modal-title" id="myModalLabel2">Modifier <strong><?=$m->mac;?></strong></h4>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-12">
                          <form action="<?=base_url('edit_mac?mac='.$m->mac);?>" method="POST">
                            
                            <div class="form-group">
                              <div class="col-md-12">
                                <input type="text" name="newMac" value="<?=$m->mac;?>" required class="form-control" placeholder="Adresse MAC">
                              </div>
                            </div>

                            <div class="form-group">
                              <div class="col-md-12">
                                <select style="margin-top: 1.5em;" class="form-control" name="admin">
                                  <?php 
                                    foreach($users as $u):
                                      if($u->userRole == "Admin"):
                                  ?>
                                  <option <?=$u->id == $m->adminId ? "selected": "";?> value="<?=$u->id;?>"><?=$u->name;?></option>
                                  <?php
                                      endif;
                                    endforeach;
                                  ?>
                                </select>
                              </div>
                            </div>

                            <div class="form-group">
                              <div class="col-md-12">
                                <button style="margin-top: 1.5em;" type="submit" class="btn btn-success">Modifier <i class="fa fa-edit"></i></button>
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
                  $i++;
                endforeach;
              ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade add-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-close"></i></span>
        </button>
        <h4 class="modal-title" id="myModalLabel2">Ajouter une MAC</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form action="<?=base_url('add_mac');?>" method="POST">
              
              <div class="form-group">
                <div class="col-md-12">
                  <input type="text" name="mac" required class="form-control" placeholder="Adresse MAC">
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <select style="margin-top: 1.5em;" class="form-control" name="admin">
                    <?php 
                      foreach($users as $u):
                        if($u->userRole == "Admin"):
                    ?>
                    <option value="<?=$u->id;?>"><?=$u->name;?></option>
                    <?php
                        endif;
                      endforeach;
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-12">
                  <button style="margin-top: 1.5em;" type="submit" class="btn btn-success">Ajouter <i class="fa fa-plus"></i></button>
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