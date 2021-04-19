<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Files Controller
 *
 * @property \App\Model\Table\FilesTable $Files
 * @method \App\Model\Entity\File[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FilesController extends AppController{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(){
        if ($this->request->is('POST')){
            //TODO adopt to folders and Categories
            $uid = $this->Auth->user('uid');
            $ufolder = $this->dbDefault->execute('SELECT * FROM user_folders WHERE uid=:uid', ['uid'=>$uid])->fetchAll('assoc');
            $i=0;
            foreach ($ufolder as $fold){
                $ufolder[$i]['categories'] = $this->dbDefault->execute('SELECT * FROM user_categories WHERE uid=:uid AND folder=:folder_id', ['uid'=>$uid, 'folder_id'=>$fold['id']])->fetchAll('assoc');
                $j=0;
                foreach ($ufolder[$i]['categories'] as $cat){
                    $ufolder[$i]['categories'][$j] = $this->dbDefault->execute('SELECT * FROM files WHERE uid=:uid AND folder=:folder_id', ['uid'=>$uid, 'folder_id'=>$cat['id']])->fetchAll('assoc');
                    $ufolder[$i]['categories'][$j]['category'] = $cat['name'];
                    $j++;
                }
                $i++;
            }
            if ($ufolder){
                $this->successCallback($ufolder);
            } else { $this->errorCallback(405);}
        } else { $this->errorCallback(403);}
    }
    public function getFiles(){
        if ($this->request->is('POST')){
            $uid = $this->Auth->user('uid');
            $id = $this->request->getData('id');

            $files = $this->dbDefault->execute('SELECT file_id, uid, name, category, file_type FROM files WHERE uid=:uid AND category=:id', ["uid"=>$uid,"id"=>$id])->fetchAll('assoc');
            if ($files){
                $this->successCallback($files);
            } else { $this->errorCallback(405);}
        } else { $this->errorCallback(404);}
    }

    public function view(){
        if ($this->request->is('POST')){
            #$uid = $this->Auth->user('uid');
            $id = $this->request->getData('id');

            $files = $this->dbDefault->execute('SELECT * FROM files WHERE file_id=:id', ["id"=>$id])->fetch('assoc');
            $files["shared"] = $this->dbDefault->execute('SELECT s.*, u.uname FROM shared s, users u WHERE u.uid=s.uid AND s.file_id=:id', ["id"=>$id])->fetchAll('assoc');
            if ($files){
                $this->successCallback($files);
            } else { $this->errorCallback(405);}
        } else { $this->errorCallback(404);}
    }

    public function add(){
        //TODO store file on server
        //{"name": "test", "path":"path", "category": "aaa"}
        if ($this->request->is('POST')){
        $uid = $this->Auth->user('uid');
        $data = $this->request->getData('data');
        $res = $this->dbDefault->execute('INSERT INTO files (uid, name, content, trans_text, category, file_type) VALUES(:uid, :name, :content, :trans_text, :category, :file_type)',
            ["uid"=>$uid, "name" => $data["name"], "content" => $data["content"], "trans_text" => '', "category" => $data["category"], "file_type"=>$data["file_type"]]);
        if ($res){
            $this->successCallback(true);
        } else { $this->errorCallback(405);}
       } else { $this->errorCallback(404);}
    }

    public function edit(){
        if ($this->request->is('POST')){

            $uid = $this->Auth->user('uid');
            $data = $this->request->getData('data');

            $files = $this->dbDefault->execute('UPDATE files set name=:name, content=:content WHERE file_id=:id AND uid=:uid',
                ["id"=>$data["file_id"],"name" => $data["name"], "content"=>$data["content"], "uid"=>$uid]);
            if ($files){
                $this->successCallback(true);
            } else { $this->errorCallback(405);}
        } else { $this->errorCallback(404);}
    }

    public function delete(){
        if ($this->request->is('POST')){
            $uid = $this->Auth->user('uid');
            $id = $this->request->getData('id');
            $files = $this->dbDefault->execute('DELETE FROM files WHERE file_id=:id AND uid=:uid', ["uid"=>$uid, "id"=>$id]);
            if ($files){
                $this->successCallback(true);
            } else { $this->errorCallback(405);}
        } else { $this->errorCallback(404);}
    }

    public function translate(){
        //TODO transalte | Tariks part API
    }

    public function getAll(){
        if($this->request->is('POST')){
            $uid = $this->Auth->user('uid');

            $files = $this->dbDefault->execute('SELECT * FROM files WHERE uid=:uid', ["uid"=> $uid])->fetchAll('assoc');
            if($files){
                $this->successCallback($files);
            } $this->errorCallback(451);
        } else $this->errorCallback(404);

    }
    public function shareWith(){
        if($this->request->is('POST')){
            $file_id = $this->request->getData('file_id');
            $uid = $this->request->getData('uid');
            $insert = $this->dbDefault->execute('INSERT INTO shared (uid, file_id) VALUES(:uid, :file_id)', ['uid'=>$uid, 'file_id'=>$file_id]);
            if($insert)
                $this->successCallback(true);
            else $this->errorCallback(405);
        } else $this->errorCallback(404);
    }

    public function unshareWith(){
        if($this->request->is('POST')){
            $file_id = $this->request->getData('file_id');
            $uid = $this->request->getData('uid');
            $insert = $this->dbDefault->execute('DELETE FROM shared WHERE uid=:uid AND file_id=:file_id', ['uid'=>$uid, 'file_id'=>$file_id]);
            if($insert){
                $this->successCallback(true);
            } else $this->errorCallback(405);
        } else $this->errorCallback(404);
    }

    public function getShared(){
        if($this->request->is('POST')){
            $uid = $this->Auth->user('uid');

            $shared = $this->dbDefault->execute('SELECT * FROM shared WHERE uid=:uid', ["uid"=> $uid])->fetchAll('assoc');
            if($shared){
                $i = 0;
                foreach ($shared as $item) {
                    $shared[$i]['file'] = $this->dbDefault->execute('SELECT uid, name FROM files WHERE file_id=:id', ["id"=>$item['file_id']])->fetch('assoc');
                    $shared[$i]['uname'] = $this->dbDefault->execute('SELECT uname FROM users WHERE uid=:uid', ["uid"=>$shared[$i]['file']['uid']])->fetch()[0];
                    $i++;
                }
                $this->successCallback($shared);
            } else  $this->errorCallback(405);
        } else $this->errorCallback(404);
    }

    public function getAllUsers(){
        if($this->request->is('POST')){
            $files = $this->dbDefault->execute('SELECT uname, uid FROM users WHERE uid!=:uid', ["uid"=>$this->Auth->user('uid')])->fetchAll('assoc');
            if($files){
                $this->successCallback($files);
            } else $this->errorCallback(403);
        } else $this->errorCallback(404);
    }
}
