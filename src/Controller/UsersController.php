<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController{

    public function index(){
        if($this->request->is('POST')){
            $id = $this->Auth->user('uid');
            $mbUser = $this->dbDefault->execute('SELECT uname, firstname, surname, gender, email FROM users WHERE uid=:id',["id"=>$id])->fetch('assoc');
            $count = $this->dbDefault->execute('SELECT COUNT(*) FROM files WHERE uid=:uid',["uid"=>$id])->fetch();

            if ($mbUser && $count){
                $this->successCallback(["user"=>$mbUser, "fileCount"=>$count[0]]);
            } else { $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }

    public function token(){
        if($this->request->is('POST')){
            $getData = $this->request->getData();

            $email = $getData["username"];
            $pwd = $getData["password"];


            if($email != null && $pwd != null){
                $hasher = new DefaultPasswordHasher();
                $user = $this->dbDefault->execute('SELECT * FROM users WHERE email=:email',["email"=>$email])->fetch('assoc');
                if($user){
                    $user["pwd"] = strip_tags(trim($user["pwd"]));
                    if ($hasher->check((string)$pwd, $user["pwd"])){
                        unset($user["pwd"]);
                        $this->Auth->setUser($user);
                        $this->successCallback(['user'=>$this->Auth->user(),
                            'token' => JWT::encode([
                                'sub' => $user['uid'],
                                'exp' =>  time() + 604800
                            ],
                                Security::getSalt())
                        ]);
                    } else{ $this->errorCallback(450);}
                } else{  $this->errorCallback(450);}
            } else{ $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }

    public function logout(){
        if($this->request->is('POST')){
            $this->Auth->logout();
            $this->successCallback();
        } else{ $this->errorCallback(404);}
    }

    public function register(){
        if($this->request->is('POST')){
            $hasher = new DefaultPasswordHasher();
            $data = $this->request->getData('data');
            $outcome = $this->dbDefault->execute('INSERT INTO users (uname, firstname, surname, gender, email, pwd) VALUES(:uname, :fname, :sname, :gender, :email, :pwd);',["uname"=>$data['uname'], "fname"=>$data["firstname"], "sname"=>$data["surname"],
                "gender" => $data["gender"], "email" =>$data["email"], "pwd"=>$hasher->hash($data["password"])]);
            if($outcome){
                $user = $this->dbDefault->execute('SELECT uid FROM users WHERE email=:email',["email"=>$data["email"]])->fetch('assoc');
                $this->Auth->setUser($user);
                $this->successCallback([
                    'id' => $user['uid'],
                    'token' => JWT::encode(
                        [
                            'sub' => $user['uid'],
                            'exp' =>  time() + 604800
                        ],
                        Security::getSalt())
                ]);
            } else{ $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }

    public function view(){
        if($this->request->is('POST')){
            $id = $this->Auth->user('uid');
            $mbUser = $this->dbDefault->execute('SELECT uname, firstname, surname, gender, email FROM users WHERE uid=:id',["id"=>$id])->fetch('assoc');
            if ($mbUser){
                $this->successCallback($mbUser);
            } else { $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }
    public function edit(){
        if($this->request->is('POST')){
            $uid = $this->Auth->user('uid');
            $data = $this->request->getData('data');
            $mbUser = $this->dbDefault->execute('UPDATE users SET firstname =:fname, surname =:sname, gender =:gender, email =:email WHERE uid=:uid',
                ["uid"=>$uid, "fname"=>$data["firstname"], "sname"=>$data["surname"], "gender"=> $data["gender"], "email" => $data["email"]]);
            if ($mbUser){
                $this->successCallback(true);
            } else { $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }

    public function delete(){
        if($this->request->is('POST')){
            $id = $this->Auth->user('uid');
            $outcome = $this->dbDefault->execute('DELETE FROM users WHERE uid=:id',["id"=>$id]);
            if ($outcome){
                $this->successCallback(true);
            } else { $this->errorCallback(405);}
        } else{ $this->errorCallback(404);}
    }
    public function changePwd($newPwd){
        if($this->request->is('POST')){
            $hasher = new DefaultPasswordHasher();
            $uid = $this->Auth->user('uid');
            $dbPwd = $this->dbDefault->execute("SELECT pwd FROM users WHERE uid=:id",["id"=>$uid])->fetch()[0];
            $oldPwd = $this->request->getData('oldpwd');
            $newPwd = $this->request->getData('newpwd');

            if ($hasher->check((string)$oldPwd, (string)$dbPwd)){
                $outcome = $this->dbDefault->execute('UPDATE users SET pwd=:pwd WHERE uid=:id',["id"=>$uid, 'pwd'=>$newPwd]);
                if ($outcome){
                    $user = $mbUser = $this->dbDefault->execute('SELECT uname, firstname, surname, gender, email FROM users WHERE uid=:id',["id"=>$uid])->fetch('assoc');
                    $this->successCallback($user);
                } else { $this->errorCallback(405);}
            } else{ $this->errorCallback(403);}
        } else{ $this->errorCallback(404);}
    }
    public function getcsrf(){
        #$this->successCallback($this->request->getAttribute('csrfToken'));
        $this->successCallback($this->request->getParam('_csrfToken'));
    }
}
