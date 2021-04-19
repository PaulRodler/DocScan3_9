<?php


namespace App\Controller;


class FolderController extends AppController{
    public function indexFolders()
    {
        if ($this->request->is('POST')) {
            $folders = $this->dbDefault->execute("SELECT * FROM folders")->fetchAll('assoc');
            if ($folders) {
                $this->successCallback($folders);
            } else $this->errorCallback(405);
        } else $this->errorCallback(404);
    }
    public function index(){
        if($this->request->is('POST')){
            $folders = $this->dbDefault->execute("SELECT * FROM user_folders WHERE uid=:uid",["uid"=>$this->Auth->user('uid')])->fetchAll('assoc');
            if($folders){
                $this->successCallback($folders);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }
    public function view(){
        if($this->request->is('POST')){
            $id = $this->request->getData('id');

            $folders = $this->dbDefault->execute("SELECT * FROM user_categories WHERE uid=:uid AND folder=:id",["uid"=>$this->Auth->user('uid'), "id"=>$id])->fetchAll('assoc');
            if($folders){
                $this->successCallback($folders);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }
    public function add(){
        if($this->request->is('POST')){
            $data = $this->request->getData('data');

            $folders = $this->dbDefault->execute("INSERT INTO user_folders (uid, name, color) VALUES(:uid, :name, :color)",["uid"=>$this->Auth->user('uid'), "name"=>$data["name"], "color"=>$data["color"]]);
            if($folders){
                $this->successCallback(true);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }
    public function delete(){
        if($this->request->is('POST')){
            $id = $this->request->getData('id');

            $folders = $this->dbDefault->execute("DELETE FROM user_folders WHERE uid=:uid AND id=:id",["uid"=>$this->Auth->user('uid'), "id"=>$id]);
            if($folders){
                $this->successCallback(true);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }

}
