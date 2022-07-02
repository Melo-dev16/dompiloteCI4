<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function index()
    {
        if (isset($_SESSION["__sess_dompilote_id"])){
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
            $this->admin->AutoLogin();
            return view('login');
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
        
        delete_cookie('__dompilote_token');
        delete_cookie('__dompilote_email');
        return redirect()->to(base_url());
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
        $techs = $this->request->getPost('techs');
        $admins = $this->request->getPost('admins');

        
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
            
            return $this->response->setJSON(['response'=>$this->admin->addTemperatures($temp)]);
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
}
