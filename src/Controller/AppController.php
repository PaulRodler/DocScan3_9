<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use \Crud\Controller\ControllerTrait;
use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public function initialize():void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');

        $this->dbDefault = ConnectionManager::get('default');

        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'email', 'password' => 'pwd'],
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Users',
                    'fields' => [
                        'username' => 'uid'
                    ],
                    'queryDatasource' => true
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize'
        ]);

        //TODO in ADMIN-SERVER HASH Passwort
        $this->Auth->allow(['login', 'getcsrf', 'register', 'token']);


        #$this->set('uname', $this->Auth->user());
        #$this->set('csrfToken', $this->request->getAttribute('csrfToken'));
    }
    public function errorCallback($code, $cols = ''){
        if($code == 404){
            $message = "request now allowed";
        } elseif ($code == 402){
            $message = "entries not found";
        } elseif ($code == 403){
            $message = "internal error";
        } elseif ($code == 405 && $code == ''){
            $message = "parameter error";
        } elseif ($code == 405 && $code != ''){
            $message = "parameter error; ".$cols." are missing";
        } elseif ($code == 410){
            $message = "unauthorized error, Invalid username or password";
        } elseif ($code == 450){
            $message = "Email or Password not correct";
        } else{ $message= "unkown error";}

        $this->set('res', ["state" => "error", "code"=> $code, "message" =>$message]);
        $this->set('_serialize', 'res');
    }
    public function successCallback($data = null){
        $this->set('res', ["state"=> "success", "code" =>202, "message" => "successful", "data" => $data]);
        $this->set('_serialize', 'res');
    }

}
