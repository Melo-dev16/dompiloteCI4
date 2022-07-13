<?php
namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    public $db;
    public $userBuilder;
    public $apptBuilder;
    public $mngBuilder;
    public $tempBuilder;
    
    public function __construct(){
        $this->db = db_connect();
        $this->userBuilder = $this->db->table('user');
        $this->apptBuilder = $this->db->table('apartment');
        $this->mngBuilder = $this->db->table('manage');
        $this->tempBuilder = $this->db->table('temperature');
        $this->prBuilder = $this->db->table('password_recovery');
        $this->macBuilder = $this->db->table('macs');

        parent::__construct();
    }

    public function AutoLogin(){
        
        if(get_cookie("__dompilote_email") != null && get_cookie("__dompilote_token") != null):
            $email = get_cookie("__dompilote_email");
            $token = get_cookie("__dompilote_token");
            
            $today = date("Y-m-d H:i:s");

            $q = $this->userBuilder
            ->select("user.id as id,email,user.name as name, password,role.name as userRole")
            ->join("role","user.roleId = role.id")->where("email",$email)
            ->where("token",$token)
            ->where("deletedAt",NULL)
            ->where("disableAt >",$today);

            if ($q->countAllResults(false) == 1):
                $userFound = $q->get()->getRow();
                $NewToken = strtotime(date("d-m-y H:i:s")).md5(uniqid(rand(), TRUE));
                $data = ["token" => $NewToken];
                $this->userBuilder->where("id",$userFound->id)->update($data);
                $expire = time()+60*60*24*30;
                set_cookie('__dompilote_token', $NewToken,$expire);
                set_cookie('__dompilote_email', $email,$expire);

                $_SESSION = [
                    '__sess_dompilote_email' => $userFound->email,
                    '__sess_dompilote_id' => $userFound->id,
                    '__sess_dompilote_name' => $userFound->name,
                    '__sess_dompilote_role' => $userFound->userRole
                ];

                return redirect()->to(base_url());
            endif;
        endif;
    }

    public function login($email,$mdp,$token) {
        $today = date("Y-m-d H:i:s");

        $q = $this->userBuilder->select("user.id as id,email,user.name as name, password,role.name as userRole")
        ->join("role","user.roleId = role.id")->where("email",$email)
        ->where("deletedAt",NULL)
        ->where("disableAt >",$today);

        if ($q->countAllResults(false) == 1){
            $userFound = $q->get()->getRow();
            if (password_verify($mdp,$userFound->password)){
                
                $data = ["token" => $token];
                $this->userBuilder->where("id",$userFound->id)->update($data);
                
                $expire = time()+60*60*24*30;
                set_cookie('__dompilote_token', $token,$expire);
                set_cookie('__dompilote_email', $userFound->email,$expire);

                $_SESSION = [
                    '__sess_dompilote_email' => $userFound->email,
                    '__sess_dompilote_id' => $userFound->id,
                    '__sess_dompilote_name' => $userFound->name,
                    '__sess_dompilote_role' => $userFound->userRole
                ];

                return true;

            }
            else{
                return false;//Identifiers correct,password wrong
            }
        }
        else{
            return false;//Identifiers incorrect
        }
    }
    
    public function getAllUsers(){
        $q = $this->userBuilder->select("user.id as id,email,user.name as name,role.name as userRole,disableAt,deletedAt")
        ->join("role","user.roleId = role.id")->where('deletedAt',NULL)->get()->getResult();

        return $q;
    }

    public function checkUserPassword($email,$pwd){
        $today = date("Y-m-d H:i:s");

        $q = $this->userBuilder->select("user.id as id,email,user.name as name, password,role.name as userRole")
        ->join("role","user.roleId = role.id")->where("email",$email)
        ->where("deletedAt",NULL)
        ->where("disableAt >",$today);

        if ($q->countAllResults(false) == 1){
            $userFound = $q->get()->getRow();
            if (password_verify($pwd,$userFound->password)){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }        
    }

    public function getUserInfos($userID){
        $q = $this->userBuilder->select("user.id as id,email,user.name as name,role.name as userRole,disableAt,deletedAt")
        ->join("role","user.roleId = role.id")->where('deletedAt',NULL)->where('user.id',$userID)->get()->getRow();

        return $q;
    }

    public function getUserInfosByEmail($email){
        $q = $this->userBuilder->select("user.id as id,email,user.name as name,role.name as userRole,disableAt,deletedAt")
        ->join("role","user.roleId = role.id")->where('deletedAt',NULL)->where('user.email',$email)->get()->getRow();

        return $q;
    }

    public function newPasswordRecovery($email,$code){
        $disable = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +5 minutes"));
        $data = [
            "code" => $code,
            "email" => $email,
            "disableAt" => $disable
        ];
        $this->prBuilder->insert($data);
    }

    public function checkPasswordRecovery($mail,$code){
        $q = $this->prBuilder->select("*")->where('email',$mail)->where('code',$code)->get()->getRow();

        return $q;
    }

    public function resetPassword($mail,$pwd){
        $data = [
            "password" => password_hash($pwd, PASSWORD_DEFAULT),
        ];
        $this->userBuilder->where('email',$mail)->update($data);
    }

    public function getUserApts($userID){
        $user = $this->getUserInfos($userID);
        if($user->userRole == "Super Admin"){
            $q = $this->apptBuilder->select("*,id as apartementId")->where('deletedAt',NULL)->get()->getResult();
        }
        else{
            $q = $this->mngBuilder->select("*")
            ->join("apartment","apartment.id = manage.apartementId")
            ->where('userId',$userID)->where('deletedAt',NULL)
            ->get()->getResult();
        }

        return $q;
    }

    public function getUnknownApts(){
        if($_SESSION['__sess_dompilote_role'] == "Admin"){
            $q = $this->apptBuilder->select("*,id as apartementId,apartment.deletedAt as deletedAt")
            ->join("macs","apartment.host = macs.mac")->where('apartment.deletedAt',NULL)->where('adminId',$_SESSION['__sess_dompilote_id'])->where('unknown',1)->get()->getResult();
        }
        else{
            $q = $this->apptBuilder->select("*,id as apartementId")->where('deletedAt',NULL)->where('unknown',1)->get()->getResult();
        }
        return $q;

    }

    public function getApartUsers($aptID){
        $q = $this->mngBuilder->select("*")
            ->join("user","user.id = manage.userId")
            ->where('apartementId',$aptID)->where('deletedAt',NULL)
            ->get()->getResult();

        return $q;
    }

    public function addUser($name,$email,$role,$pwd,$disable){
        $data = [
            "name" => $name,
            "email" => $email,
            "roleId" => $role,
            "password" => $pwd,
            "disableAt" => $disable
        ];
        $this->userBuilder->insert($data);
    }

    public function deleteUser($userID){
        $today = date("Y-m-d H:i:s");

        $data = [
            "deletedAt" => $today,
        ];
        $this->userBuilder->where('id',$userID)->update($data);
    }

    public function deleteAppt($apptID){
        $today = date("Y-m-d H:i:s");
        
        $data = [
            "deletedAt" => $today,
        ];
        $this->apptBuilder->where('id',$apptID)->update($data);
    }

    public function undeleteAppt($apptID){
        
        $data = [
            "deletedAt" => NULL,
        ];
        $this->apptBuilder->where('id',$apptID)->update($data);
    }

    public function editUser($userID,$name,$email,$role,$disable){
        $data = [
            "name" => $name,
            "email" => $email
        ];

        if($role != '' && $disable != ''){
            $data["roleId"] = $role;
            $data["disableAt"] = $disable;
        }

        $this->userBuilder->where('id',$userID)->update($data);
    }

    public function updatePassword($userID,$newPwd){
        $data = [
            "password" => $newPwd
        ];

        $this->userBuilder->where('id',$userID)->update($data);
    }

    public function addApartment($aptName,$aptAddr,$aptCmp,$aptState,$aptLong,$aptLat,$aptType,$aptMac,$aptTel1,$aptTel2,$aptBat,$aptStair,$aptFloor,$owner,$techs,$admins,$unknown = 0){

        $data = [
            "aptName" => $aptName,
            "adress" => $aptAddr,
            "company" => $aptCmp,
            "state" => $aptState,
            "lat" => $aptLat,
            "long" => $aptLong,
            "host" => $aptMac,
            "tel1" => $aptTel1,
            "tel2" => $aptTel2,
            "type" => $aptType,
            "nBat" => $aptBat,
            "nStair" => $aptStair,
            "nFloor" => $aptFloor,
            "unknown" => $unknown
        ];

        $this->apptBuilder->insert($data);

        $aptID = $this->db->insertID();

        //Insertion du proprio
        $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$owner]);

        //Insertion des technos
        foreach ($techs as $t) {
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$t]);
        }

        //Insertion des managers
        foreach ($admins as $a) {
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$a]);
        }

        $macAdmin = $this->getAdminMac($aptMac);

        if(!$this->verifyAdminManage($macAdmin,$aptID)){
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$macAdmin]);
        }

        
    }

    public function MergeAppt($from,$to,$type){
        if ($type == "A") {
            $this->tempBuilder->where('apartementId',$from)->update(["apartementId"=>$to]);
            $data = ["host" => $this->getApartementDetails($from,'host')];
            $this->apptBuilder->where('id',$to)->update($data);
        }
        elseif ($type == "B") {
            $data = ["host" => $this->getApartementDetails($from,'host')];
            $this->apptBuilder->where('id',$to)->update($data);
        }
        elseif ($type == "C") {
            $this->tempBuilder->where('apartementId',$from)->update(["apartementId"=>$to]);
        }

        $this->deleteAppt($from);
    }

    public function editApartment($aptID,$aptName,$aptAddr,$aptCmp,$aptState,$aptLong,$aptLat,$aptType,$aptMac,$aptTel1,$aptTel2,$aptBat,$aptStair,$aptFloor,$owner,$techs,$admins){

        $data = [
            "aptName" => $aptName,
            "adress" => $aptAddr,
            "company" => $aptCmp,
            "state" => $aptState,
            "lat" => $aptLat,
            "long" => $aptLong,
            "host" => $aptMac,
            "tel1" => $aptTel1,
            "tel2" => $aptTel2,
            "type" => $aptType,
            "nBat" => $aptBat,
            "nStair" => $aptStair,
            "nFloor" => $aptFloor
        ];

        if (isset($_POST['unknown'])) {
            $data["unknown"] = $_POST['unknown'];
        }

        
        $this->apptBuilder->where('id',$aptID)->update($data);

        //Suppression de tous les gestionnaires

        $this->mngBuilder->delete(['apartementId'=>$aptID]);
        
        //Insertion du proprio
        $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$owner]);

        //Insertion des technos
        foreach ($techs as $t) {
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$t]);
        }

        //Insertion des managers
        foreach ($admins as $a) {
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$a]);
        }

        $macAdmin = $this->getAdminMac($aptMac);

        if(!$this->verifyAdminManage($macAdmin,$aptID)){
            $this->mngBuilder->insert(['apartementId'=>$aptID,'userId'=>$macAdmin]);
        }
        
    }

    public function verifyAdminManage($userID,$aptID){
        $q = $this->mngBuilder->select("*")
        ->where('userId',$userID)->where('apartementId',$aptID)
        ->get()->getResult();

        if(count($q) > 0){
            return true;
        }
        else{
            return false;
        }
    }

    public function getApartementDetails($aptID, $info = '*'){

        if($info == '*'){
            $q = $this->apptBuilder->select("*")
            ->where('id',$aptID)
            ->where('deletedAt',NULL)
            ->get()->getResult();
        }
        else{
            $q = $this->apptBuilder->select("*")->where('id',$aptID)
            ->where('deletedAt',NULL)->get()->getRow();

            if(isset($q->$info)){
                $q = $q->$info;
            }
            else{
                $q = "";
            }
        }

        return $q;
    }

    public function getAptRooms($aptID,$room = 'all'){

        if($room == 'all'){
            $rooms = $this->tempBuilder->select("DISTINCT(room) as single_room")
        ->where('apartementId',$aptID)->get()->getResult();
        }
        else{
            $rooms = $this->tempBuilder->select("DISTINCT(room) as single_room")
        ->where('apartementId',$aptID)->where('room',$room)->get()->getResult();
        }

        return $rooms;
    }

    public function getConsigneByRooms($aptID){
        $rooms = $this->tempBuilder->select("DISTINCT(room) as single_room")
        ->where('apartementId',$aptID)->get()->getResult();

        $datas = [];
        $tmp = [];

        foreach ($rooms as $r) {
            $data = $this->tempBuilder->select("temperature_setpoint_degC as consA,heating_demand as consB,datetime,id")
            ->where('apartementId',$aptID)->where('room',$r->single_room)
            ->orderBy('datetime','DESC')->get()->getRow();
            
            $tmp['A'] = $data->consA;
            $tmp['B'] = $data->consB;
            $tmp['datetime'] = $data->datetime;
            $tmp['TempID'] = $data->id;

            $datas[$r->single_room] = $tmp;
        }

        return $datas;
    }

    public function getAptRoomsStats($aptID,$room,$date){
        $q = $this->tempBuilder->select("*")
        ->where('apartementId',$aptID)->where('room',$room);

        if($date != ''){
            $q = $q->where('datetime <=',$date);
        }

        $data = $q->orderBy('datetime', 'DESC')->get()->getRow();
        return $data;
    }

    public function getDateFromTemps($aptID,$start,$end){
        $q = $this->tempBuilder->select("DISTINCT(CAST(datetime AS DATE)) as single_date")->where('apartementId',$aptID)->where('datetime >=',$start)->where('datetime <=',$end)->orderBy('single_date', 'ASC')->get()->getResult();

        //var_dump($q);exit(0);
        return $q;
    }

    public function getAptRoomDetails($aptID,$room,$start,$end){
        $q = $this->tempBuilder->select("*")
        ->where('apartementId',$aptID)->where('room',$room);

        if($start != '' && $end != ''){
            $q = $q->where('datetime >=',$start)->where('datetime <=',$end);
        }

        $data = $q->orderBy('datetime', 'DESC')->get()->getResult();
        return $data;
    }

    public function getRoomMeanTemp($aptID,$room,$date){
        $q = $this->tempBuilder->select("CAST(datetime AS DATE) as temp_date,ROUND(AVG(temperature_air_degC),1) as temp")->where('apartementId',$aptID)->where('room',$room)->where('CAST(datetime AS DATE) = ',$date)->get()->getRow();

        if(!isset($q->temp)){
            return 0;
        }
        $temp = $q->temp;

        return $temp;
    }

    public function getRoomKwh($aptID,$room,$date){
        $q = $this->tempBuilder->select("CAST(datetime AS DATE) as temp_date,SUM(heat_meter_kWh) as kwh")->where('apartementId',$aptID)->where('room',$room)->where('CAST(datetime AS DATE) = ',$date)->get()->getRow();

        if(!isset($q->kwh)){
            return 0;
        }
        $k = $q->kwh;

        return $k;
    }

    public function getApartByMac($mac,$info = '*'){
        if($info == '*'){
            $q = $this->apptBuilder->select("*")->where('host',$mac)->get()->getRow();
        }
        else{
            $q = $this->apptBuilder->select("*")->where('host',$mac)->get()->getRow();

            if(isset($q->$info)){
                $q = $q->$info;
            }
            else{
                $q = 0;
            }
        }

        return $q;
    }

    public function isMACunique($mac){
        $q = $this->macBuilder->select("*")->where("mac",$mac)->get()->getRow();

        return is_null($q) ? true : false;
    }

    public function getApartTemps($aptID){
        $q = $this->tempBuilder->select("*")
        ->where('apartementId',$aptID)->orderBy("datetime","DESC")->get()->getResult();

        return $q;
    }

    public function addTemperatures($temp){
        $datas = $temp['DATA'];
        $ctrl = $temp['controller'][0];

        $apt = $this->getApartByMac($ctrl['host'],"id");

        //echo "Host: ".$ctrl['host']." - ID: $apt";exit(0);
        if($apt == 0){
            $this->AddMac(NULL,$ctrl['host']);
            $this->addApartment("Appt ".$ctrl['host'],"","","","","","",$ctrl['host'],"","","","","","",[],[],1);
            $this->addTemperatures($temp);
        }
        else{
            foreach ($datas as $d) {
                $date = \DateTime::createFromFormat('ymdHis', $ctrl['datetime']);
                $datetime = $date->format('Y-m-d H:i:s');
                $myData = [
                    "apartementId" => $apt,
                    "room" => $d['room'],
                    "datetime" => $datetime,
                    "temperature_water_degC" => isset($d['temperature_water_degC']) ? $d['temperature_water_degC'] : 0,
                    "temperature_air_degC" => isset($d['temperature_air_degC']) ? $d['temperature_air_degC'] : 0,
                    "end_manual" => isset($d['end_manual']) ? $d['end_manual'] : 0,
                    "heat_operating_range" => isset($d['heat_operating_range']) ? $d['heat_operating_range'] : 0,
                    "heat_meter_dl" => isset($d['heat_meter_dl']) ? $d['heat_meter_dl'] : 0,
                    "heat_meter_kWh" => isset($d['heat_meter_kWh']) ? $d['heat_meter_kWh'] : 0,
                    "regim" => isset($d['regim']) ? $d['regim'] : 0,
                    "heater_power" => isset($d['heater_power']) ? $d['heater_power'] : 0,
                    "valve_diam" => isset($d['valve_diam']) ? $d['valve_diam'] : 0,
                    "valve_position" => isset($d['valve_position']) ? $d['valve_position'] : 0,
                    "temperature_setpoint_degC" => isset($d['temperature_setpoint_degC']) ? $d['temperature_setpoint_degC'] : 0,
                    "heating_demand" => isset($d['heating_demand']) ? $d['heating_demand'] : 0
                ];
                
                $this->tempBuilder->insert($myData);
            }
        }
    }

    public function updateCons($tempID,$cons,$val){

        if($cons == 'A'){
            $data = [
                "temperature_setpoint_degC" => $val,
            ];
        }
        else{
            $data = [
                "heating_demand" => $val
            ];
        }
        
        $this->tempBuilder->where("id",$tempID)->update($data);
    }

    public function AddMac($admin,$mac){
        $data = [
            "adminId" => $admin,
            "mac" => $mac
        ];
        $this->macBuilder->insert($data);
    }

    public function getAdminMac($mac){
        $q = $this->macBuilder->select("*")->where("mac",$mac)->get()->getRow()->adminId;

        return $q;
    }

    public function getMacs(){
        $q = $this->macBuilder->select("*,macs.deletedAt as deletedAt")
        ->join("user","user.id = macs.adminId","left")->get()->getResult();

        return $q;
    }

    public function undeleteMac($mac){
        $data = [
            "deletedAt" => NULL,
        ];
        $this->macBuilder->where('mac',$mac)->update($data);
    }

    public function deleteMac($mac){
        $today = date("Y-m-d H:i:s");
        
        $data = [
            "deletedAt" => $today,
        ];
        $this->macBuilder->where('mac',$mac)->update($data);
    }

    public function editMac($mac,$editMac,$admin){
        $data = [
            "adminId" => $admin,
            "mac" => $editMac
        ];
        $this->macBuilder->where("mac",$mac)->update($data);

        $apt = $this->getApartByMac($mac);

        if(!is_null($apt)){
            if(!$this->verifyAdminManage($admin,$apt->id)){
                $this->mngBuilder->insert(['apartementId'=>$apt->id,'userId'=>$admin]);
            }   
        }
    }

    public function getAdminMacs($adminID){
        $user = $this->getUserInfos($adminID);

        if($user->userRole == "Super Admin"){
            $q = $this->macBuilder->select("*")->where("deletedAt",NULL)->get()->getResult();
        }
        else {
            $q = $this->macBuilder->select("*")->where("adminId",$adminID)->where("deletedAt",NULL)->get()->getResult();
        }

        return $q;
    }
}