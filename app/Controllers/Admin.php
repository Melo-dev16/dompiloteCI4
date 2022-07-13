<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function index()
    {
        if (isset($_SESSION["__sess_dompilote_id"])){
            //echo "Il est co";exit(0);
            $apts = $this->admin->getUserApts($_SESSION["__sess_dompilote_id"]);
            
            if (count($apts) > 0) 
            {
                
                foreach ($apts as $a) {
                    return redirect()->to(base_url('apartments/'.$a->apartementId));
                }
            }
            else{
                return view('dashboard');
            }
        }
        else{
            //echo "Il est pas co";exit(0);
            $this->admin->AutoLogin();
            return view('login');
        }
    }

    public function showPasswordForget(){
        return view('password_forget');
    }

    public function PasswordRecovery(){
        $mail = $this->request->getVar("email");

        if($this->admin->getUserInfosByEmail($mail) != NULL){
            if($this->request->getVar("code") != null){
                return view('confirm_pr_mail',["email"=>$mail]);
            }
            else{
                $ok = false;
                $code = rand(10000,99999);
                
                while (!$ok) {
                    if($this->admin->checkPasswordRecovery($mail,$code) != NULL){
                        $code = rand(10000,99999);        
                    }
                    else{
                        $ok = true;
                    }
                }
                $html = view('password_recovery',['code'=>$code,'email'=>$mail]);
                $this->admin->newPasswordRecovery($mail,$code);
                $email = \Config\Services::email();
                $email->setFrom('cedric.datcha@growthcontinue.com', 'DOMPilote');
                $email->setTo("melcedricdatcha@gmail.com");
                $email->setSubject('Récupération de mot de passe');
                $email->setMessage($html);//your message here
              
               if ($email->send()) {
                $this->session->setFlashdata('alert',['title'=>'Email Envoyé !','text'=>"Email de confirmation envoyé à <strong>$mail</strong>",'type'=>'success']);
                    return view('confirm_pr_mail',["email"=>$mail]);
                } else {
                    $data = $email->printDebugger(['headers']);
                    print_r($data);
                }
            }
        }
        else{
            $this->session->setFlashdata('alert',['title'=>'Email Envoyé !','text'=>"Aucun compte trouvé pour <strong>$mail</strong>",'type'=>'error']);
            return redirect()->to(base_url('password_forget'));
        }
        
    }

    public function ConfirmPasswordRecovery(){
        $mail = $this->request->getVar("email");
        $code = $this->request->getVar("code");

        $pr = $this->admin->checkPasswordRecovery($mail,$code);
        if($pr != NULL){
            if(date("Y-m-d H:i:s") <= $pr->disableAt){
                $this->session->setFlashdata('alert',['title'=>'Code Confirmé !','text'=>"Récupération de mot de passe confirmée !",'type'=>'success']);
                    return view('change_password',["email"=>$mail]);
            }
            else{
                $this->session->setFlashdata('alert',['title'=>'Email Envoyé !','text'=>"Code expiré ou invalide !",'type'=>'error']);
                return redirect()->to(base_url('password_recovery?email='.$mail.'&code='.$code));
            }
        }
        else{
            $this->session->setFlashdata('alert',['title'=>'Email Envoyé !','text'=>"Code expiré ou invalide !",'type'=>'error']);
            return redirect()->to(base_url('password_recovery?email='.$mail.'&code='.$code));
        }
    }

    public function ResetPassword(){
        $mail = $this->request->getPost("email");
        $pwd = $this->request->getPost("pwd");
        $confirm = $this->request->getPost("confirm");

        if($pwd != $confirm) {
            $this->session->setFlashdata('alert',['title'=>'Mot de passe invalide','text'=>"Mot de passe non confirmé !",'type'=>'error']);
            return view('change_password',["email"=>$mail]);
        }
        else{
            $this->admin->resetPassword($mail,$pwd);
            $this->session->setFlashdata('alert',['title'=>'Mot de passe mis à jour !','text'=>"Récupération de mot de passe éffectuée !",'type'=>'success']);

            return redirect()->to(base_url());
        }
    }
    
    public function login()
    {
        $email = $this->request->getPost("email");
        $pass = $this->request->getPost("pwd");

        $ticket = strtotime(date("d-m-y H:i:s")).md5(uniqid(rand(), TRUE));

        if($this->admin->login($email,$pass,$ticket)){

            return $this->response->setJSON(['token'=>$ticket]);
        }
        else{
            return $this->response->setJSON(["token"=> -1]);
        }
    }
    
    public function logout()
    {
        $this->tools->verify_admin_logged();
        unset($_SESSION["__sess_dompilote_name"]);
        unset($_SESSION["__sess_dompilote_email"]);
        unset($_SESSION["__sess_dompilote_id"]);
        unset($_SESSION["__sess_dompilote_role"]);
        
        setcookie ("__dompilote_token", "", time() - 3600);
        setcookie ("__dompilote_email", "", time() - 3600);
        
        return redirect()->to(base_url());
    }

    public function UserProfile(){
        return view('profile');
    }

    public function UserEditPassword(){
        $old = $this->request->getPost('old');
        $pwd = $this->request->getPost('pwd');
        $confirm = $this->request->getPost('confirm');

        if(!$this->admin->checkUserPassword($_SESSION["__sess_dompilote_email"],$old)){
            $this->session->setFlashdata('alert',['title'=>'Erreur d\'authentification','text'=>"Ancien mot de passe invalide",'type'=>'error']);
            return redirect()->to(base_url('profile'));
        }    

        if ($pwd != $confirm) {
            $this->session->setFlashdata('alert',['title'=>'Erreur de confirmation','text'=>"Mot de passe non confirmé",'type'=>'error']);
        }
        else{
            $this->admin->updatePassword($_SESSION["__sess_dompilote_id"],password_hash($pwd, PASSWORD_DEFAULT));

            $this->session->setFlashdata('alert',['title'=>'Action éffectuée !','text'=>"Mot de passe modifié avec succès",'type'=>'success']);
        }
        
        return redirect()->to(base_url('profile')); 
    }

    public function UserEditInfos(){
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');

        $this->admin->editUser($_SESSION["__sess_dompilote_id"],$name,$email,'','');
        $this->session->setFlashdata('alert',['title'=>'Action éffectuée !','text'=>"Vos informations ont été modifié avec succès",'type'=>'success']);

        $_SESSION["__sess_dompilote_name"] = $name;
        $_SESSION["__sess_dompilote_email"] = $email;

        return redirect()->to(base_url('profile'));
    }

    public function importUsers(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $users = $_POST['Feuil1'];

        $i = 0;
        foreach ($users as $u) {
            $name = $u['name'];
            $email = $u['email'];
            $pwd = password_hash($u['password'], PASSWORD_DEFAULT);
            $disable = date("Y-m-d H:i:s",strtotime("+3 month", strtotime(date("Y-m-d H:i:s"))));

            if(isset($u['disable']) && !is_numeric($u['disable'])){
                $disable = date("Y-m-d H:i:s", strtotime($u['disable']));
            }

            if($u['role'] == "Super Admin"){
                $role = 1;
            }
            elseif($u['role'] == "Admin"){
                $role = 2;
            }
            elseif($u['role'] == "Technicien"){
                $role = 3;
            }
            elseif($u['role'] == "Utilisateur"){
                $role = 4;
            }

            $this->admin->addUser($name,$email,$role,$pwd,$disable);
            $i++;
        }
        
        $this->session->setFlashdata('alert',['title'=>'Exportation réussie','text'=>"$i utilisateurs ajoutés",'type'=>'success']);
    }

    public function showUsers(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $data['users'] = $this->admin->getAllUsers();

        return view('users',$data);
    }

    public function addUser(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $role = $this->request->getPost('role');
        $pwd = password_hash($this->request->getPost('pwd'), PASSWORD_DEFAULT);
        $disable = date("Y-m-d H:i:s", strtotime($this->request->getPost('disable')));

        $this->admin->addUser($name,$email,$role,$pwd,$disable);

        $this->session->setFlashdata('alert',['title'=>'Utilisateur ajouté !','text'=>"<strong>$name</strong> a bien été ajouté.",'type'=>'success']);
        return redirect()->to(base_url('users'));
    }

    public function deleteUser(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $userID = $this->request->uri->getSegment(2);

        $this->admin->deleteUser($userID);

        $this->session->setFlashdata('alert',['title'=>'Utilisateur supprimé !','text'=>"L'utilisateur a bien été supprimé.",'type'=>'success']);
        return redirect()->to(base_url('users'));   
    }

    public function editUser(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $userID = $this->request->uri->getSegment(2);

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $role = $this->request->getPost('role');
        $disable = date("Y-m-d H:i:s", strtotime($this->request->getPost('disable')));

        $this->admin->editUser($userID,$name,$email,$role,$disable);

        $this->session->setFlashdata('alert',['title'=>'Utilisateur édité !','text'=>"Les informations de l'utilisateur ont été mis à jour",'type'=>'success']);
        return redirect()->to(base_url('users'));   
    }

    public function passwordUpdate(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $userID = $this->request->uri->getSegment(2);

        $pwd = $this->request->getPost('pwd');
        $confirm = $this->request->getPost('confirm');

        if ($pwd != $confirm) {
            $this->session->setFlashdata('alert',['title'=>'Erreur de confirmation','text'=>"Mot de passe non confirmé ! veuillez réessayer.",'type'=>'error']);
        }
        else{
            $this->admin->updatePassword($userID,password_hash($pwd, PASSWORD_DEFAULT));

        $this->session->setFlashdata('alert',['title'=>'Action éffectuée !','text'=>"Mot de passe modifié avec succès",'type'=>'success']);
        }
        
        return redirect()->to(base_url('users')); 
    }

    public function Apartments(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $data['users'] = $this->admin->getAllUsers();
        return view('apartments',$data);
    }

    public function addApartment(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $aptName = $this->request->getPost('aptName');
        $aptAddr = $this->request->getPost('aptAddr');
        $aptCmp = $this->request->getPost('aptCmp');
        $aptState = $this->request->getPost('aptState');
        $aptLong = $this->request->getPost('aptLong');
        $aptLat = $this->request->getPost('aptLat');
        $aptType = $this->request->getPost('aptType');
        $aptMac = $this->request->getPost('aptMac');
        $aptTel1 = $this->request->getPost('aptTel1');
        $aptTel2 = $this->request->getPost('aptTel2');
        $aptBat = $this->request->getPost('aptBat');
        $aptStair = $this->request->getPost('aptStair');
        $aptFloor = $this->request->getPost('aptFloor');
        $owner = $this->request->getPost('owner');
        $techs = $this->request->getPost('techs');
        $admins = $this->request->getPost('admins');

        $this->admin->addApartment($aptName,$aptAddr,$aptCmp,$aptState,$aptLong,$aptLat,$aptType,$aptMac,$aptTel1,$aptTel2,$aptBat,$aptStair,$aptFloor,$owner,$techs,$admins);

        $this->session->setFlashdata('alert',['title'=>'Appartement ajouté !','text'=>"<strong>$aptName</strong> a bien été ajouté.",'type'=>'success']);
        return redirect()->to(base_url('apartments'));
    }

    public function MergeAppt(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $from = $this->request->uri->getSegment(2);
        $to = $this->request->getPost('to');
        $type = $this->request->getPost('mergeType');

        $this->admin->MergeAppt($from,$to,$type);

        $this->session->setFlashdata('alert',['title'=>'Appartement fusionné !','text'=>"Les deux appartements ont bien fusionnés",'type'=>'success']);
        return redirect()->to(base_url('apartments'));
    }

    public function editAppt(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $aptID = $this->request->uri->getSegment(2);
        $aptName = $this->request->getPost('aptName');
        $aptAddr = $this->request->getPost('aptAddr');
        $aptCmp = $this->request->getPost('aptCmp');
        $aptState = $this->request->getPost('aptState');
        $aptLong = $this->request->getPost('aptLong');
        $aptLat = $this->request->getPost('aptLat');
        $aptType = $this->request->getPost('aptType');
        $aptMac = $this->request->getPost('aptMac');
        $aptTel1 = $this->request->getPost('aptTel1');
        $aptTel2 = $this->request->getPost('aptTel2');
        $aptBat = $this->request->getPost('aptBat');
        $aptStair = $this->request->getPost('aptStair');
        $aptFloor = $this->request->getPost('aptFloor');
        $owner = $this->request->getPost('owner');
        $techs = $this->request->getPost('techs') != null ? $this->request->getPost('techs') : [] ;
        $admins = $this->request->getPost('admins') != null ? $this->request->getPost('admins') : [] ;

        $this->admin->editApartment($aptID,$aptName,$aptAddr,$aptCmp,$aptState,$aptLong,$aptLat,$aptType,$aptMac,$aptTel1,$aptTel2,$aptBat,$aptStair,$aptFloor,$owner,$techs,$admins);

        $this->session->setFlashdata('alert',['title'=>'Appartement Modifié !','text'=>"<strong>$aptName</strong> a bien été modifié.",'type'=>'success']);
        return redirect()->to(base_url('apartments'));
    }

    public function deleteAppt(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $apptID = $this->request->uri->getSegment(2);

        $this->admin->deleteAppt($apptID);

        $this->session->setFlashdata('alert',['title'=>'Appartement supprimé !','text'=>"L'appartement a bien été supprimé.",'type'=>'success']);
        return redirect()->to(base_url('apartments'));   
    }

    public function apartmentDetails(){
        $this->tools->verify_admin_logged();

        $aptID = $this->request->uri->getSegment(2);

        $this->tools->verifyAdminManage($aptID);

        $data['datetime'] = '';
        $data['start'] = '';
        $data['end'] = '';

        if(isset($_GET['datetime'])){
            $data['datetime'] = $_GET['datetime'];
        }

        if(isset($_GET['start'])){
            $data['start'] = $_GET['start'];
        }

        if(isset($_GET['end'])){
            $data['end'] = $_GET['end'];
        }

        $data['apartment'] = $this->admin->getApartementDetails($aptID);
        $data['consignes'] = $this->admin->getConsigneByRooms($aptID);

        $data['roomStats'] = 'all';
        if($data['start'] != '' && $data['end'] != ''){
            $data['roomStats'] = $_GET['roomStats'];
            foreach($data['apartment'] as $da){
                //Graph Pie
                $data['graphPie'] = [];
                $data['graphPie']['rooms'] = [];
                $data['graphPie']['datas'] = [];

                $rooms = $this->admin->getAptRooms($da->id,$data['roomStats']);

                foreach ($rooms as $r) {
                    array_push($data['graphPie']['rooms'], $this->tools->slugify($r->single_room));
                }

                $dates = $this->admin->getDateFromTemps($da->id,date("Y-m-d H:i:s", strtotime($data['start'])),date("Y-m-d H:i:s", strtotime($data['end'])));
                
                foreach ($dates as $date) {
                    $tmp['date'] = $date->single_date;
                    $tmp['data'] = [];

                    foreach ($rooms as $r) {
                        $temp = $this->admin->getRoomMeanTemp($da->id,$r->single_room,$date->single_date);
                        $tmp['data'][$this->tools->slugify($r->single_room)] = $temp;
                    }
                    array_push($data['graphPie']['datas'],$tmp);
                }

                //Bar Chart
                $data['barChart'] = [];
                $data['barChart']['rooms'] = [];
                $data['barChart']['datas'] = [];

                foreach ($rooms as $r) {
                    array_push($data['barChart']['rooms'], $this->tools->slugify($r->single_room));
                }

                foreach ($dates as $date) {
                    $tmp['date'] = $date->single_date;
                    $tmp['data'] = [];
                    
                    foreach ($rooms as $r) {
                        $temp = $this->admin->getRoomKwh($da->id,$r->single_room,$date->single_date);
                        $tmp['data'][$this->tools->slugify($r->single_room)] = $temp;
                    }
                    array_push($data['barChart']['datas'],$tmp);
                }
            }
        }

        return view('apartDetails',$data);
    }

    public function roomDetails(){
        $this->tools->verify_admin_logged();

        $aptID = $this->request->uri->getSegment(2);

        $this->tools->verifyAdminManage($aptID);

        $data['start'] = '';
        $start = '';
        if(isset($_GET['start'])){
            $data['start'] = $_GET['start'];
            $start = date("Y-m-d H:i:s", strtotime($_GET['start']));
        }

        $data['end'] = '';
        $end = '';
        if(isset($_GET['end'])){
            $data['end'] = $_GET['end'];
            $end = date("Y-m-d H:i:s", strtotime($_GET['end']));
        }

        $room = urldecode($_GET['r']);
        $data['room'] = $room;

        $data['stats'] = $this->admin->getAptRoomDetails($aptID,$room,$start,$end);
        $data['apartment'] = $this->admin->getApartementDetails($aptID);

        return view('roomDetails',$data);        
    }
    
    public function AddTemperature(){
        if(isset($_REQUEST['json'])){
            $temp = json_decode($_REQUEST['json'],true);
            
            $this->admin->addTemperatures($temp);
            return $this->response->setJSON(['response'=>0]);
        }
        else{
            return $this->response->setJSON(['response'=>-1]);
        }

    }

    public function updateCons(){
        $aptID = $_POST['aptID'];
        $tempID = $_POST['tempID'];
        $cons = $_POST['cons'];
        $val = $_POST['value'];

        $this->admin->updateCons($tempID,$cons,$val);

        $this->session->setFlashdata('alert',['title'=>'Consigne Modifié !','text'=>"La consigne a bien été modifié.",'type'=>'success']);
        return redirect()->to(base_url('apartments/'.$aptID));
    }

    public function ShowMacs(){
        $data['users'] = $this->admin->getAllUsers();
        $data['macs'] = $this->admin->getMacs();
        return view('macs',$data);
    }

    public function AddMac(){
        $admin = $_POST['admin'];
        $mac = $_POST['mac'];

        if ($this->admin->isMACunique($mac)) {
            $this->session->setFlashdata('alert',['title'=>'MAC Ajouté !','text'=>"Une nouvelle MAC a été ajouté !",'type'=>'success']);
            $this->admin->AddMac($admin,$mac);
        } else {
            $this->session->setFlashdata('alert',['title'=>'MAC existante !','text'=>"La MAC $mac existe déjà !",'type'=>'error']);
        }
        
        return redirect()->to(base_url('macs'));        
    }

    public function deleteMac(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $mac = $this->request->getVar("mac");

        if ($this->admin->isMACunique($mac)) {
            $this->session->setFlashdata('alert',['title'=>'MAC non trouvé !','text'=>"La MAC $mac n'existe pas !",'type'=>'error']);
        } else {
            $this->admin->deleteMac($mac);

            $apt = $this->admin->getApartByMac($mac);

            if(!is_null($apt)){
                $this->admin->deleteAppt($apt->id);
            }

            $this->session->setFlashdata('alert',['title'=>'MAC supprimé !','text'=>"La MAC $mac a été supprimée ! Vous pouvez la restaurer avec le bouton <i class='fa fa-refresh'></i>",'type'=>'success']);
        }

        return redirect()->to(base_url('macs'));
    }

    public function undeleteMac(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $mac = $this->request->getVar("mac");

        if ($this->admin->isMACunique($mac)) {
            $this->session->setFlashdata('alert',['title'=>'MAC non trouvé !','text'=>"La MAC $mac n'existe pas !",'type'=>'error']);
        } else {
            $this->admin->undeleteMac($mac);

            $apt = $this->admin->getApartByMac($mac);

            if(!is_null($apt)){
                $this->admin->undeleteAppt($apt->id);
            }

            $this->session->setFlashdata('alert',['title'=>'MAC restauré !','text'=>"La MAC $mac a été restauré ainsi que l'appartement lui étant lié.",'type'=>'success']);
        }

        return redirect()->to(base_url('macs'));
    }

    public function editMac(){
        $this->tools->verify_admin_logged();
        $this->tools->verify_admin_role(['Super Admin','Admin']);

        $mac = $this->request->getVar("mac");
        $editMac = $this->request->getVar("newMac");
        $admin = $this->request->getVar("admin");

        if ($this->admin->isMACunique($mac)) {
            $this->session->setFlashdata('alert',['title'=>'MAC non trouvé !','text'=>"La MAC $mac n'existe pas !",'type'=>'error']);
        } else {
            if (!$this->admin->isMACunique($editMac) && $editMac != $mac) {
                $this->session->setFlashdata('alert',['title'=>'MAC existante !','text'=>"La MAC $editMac existe déjà !",'type'=>'error']);
            }
            else{
                $this->admin->editMac($mac,$editMac,$admin);

                $this->session->setFlashdata('alert',['title'=>'MAC modifié !','text'=>"La MAC a été modifiée !",'type'=>'success']);
            }
        }

        return redirect()->to(base_url('macs'));
    }
}
