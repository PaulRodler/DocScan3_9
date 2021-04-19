<?php


namespace App\Controller;


class CategoriesController extends AppController {
    public function indexCats(){
        if($this->request->is('POST')){
            $folders = $this->dbDefault->execute("SELECT * FROM categories")->fetchAll('assoc');
            if($folders){
                $this->successCallback($folders);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }
    public function index() {
        if ($this->request->is('POST')) {
            $folders = $this->dbDefault->execute("SELECT cats.*, fo.color FROM user_categories cats, user_folders fo WHERE cats.uid=:uid AND cats.folder=fo.id ORDER BY cats.last_opened DESC", ["uid" => $this->Auth->user('uid')])->fetchAll('assoc');
            if ($folders) {
                $this->successCallback($folders);
            } else $this->errorCallback(405);
        } else $this->errorCallback(404);
    }
    public function view(){
        if ($this->request->is('POST')) {
            $id = $this->request->getData('id');
            $this->dbDefault->execute("UPDATE user_categories SET last_opened=current_timestamp WHERE id=:id", ["id"=>$id]);
            $folders = $this->dbDefault->execute("SELECT * FROM user_categories WHERE uid=:uid AND id=:id", ["id"=>$id,"uid" => $this->Auth->user('uid')])->fetchAll('assoc');
            if ($folders) {
                $this->successCallback($folders);
            } else $this->errorCallback(405);
        } else $this->errorCallback(404);
    }
    public function add(){
        if($this->request->is('POST')){
            $data = $this->request->getData('data');

            $folders = $this->dbDefault->execute("INSERT INTO user_categories (uid, folder, name) VALUES(:uid, :folder, :namee)",["uid"=>$this->Auth->user('uid'),"folder"=>$data["folder_id"], "namee"=>$data["name"]]);
            if($folders){
                $this->successCallback(true);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }
    public function delete(){
        if($this->request->is('POST')){
            $id = $this->request->getData('id');
            $folders = $this->dbDefault->execute("DELETE FROM user_categories WHERE uid=:uid AND id=:id",["uid"=>$this->Auth->user('uid'), "id"=>$id]);
            if($folders){
                $this->successCallback(true);
            }else $this->errorCallback(405);
        }else $this->errorCallback(404);
    }

}
