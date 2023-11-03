<?php

namespace RocketChat;
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
require_once 'lib/httpful.phar';
use Httpful\Request;
use RocketChat\Client;

class User extends Client {
	public $username;
	private $password;
	public $id;
	public $nickname;
	public $email;

	public function __construct($username, $password, $fields = array()){
		parent::__construct();
		$this->username = $username;
		$this->password = $password;
		if( isset($fields['nickname']) ) {
			$this->nickname = $fields['nickname'];
		}
		if( isset($fields['email']) ) {
			$this->email = $fields['email'];
		}
	}

	
	public function login($save_auth = true) {
		$response = Request::post( $this->api . 'login' )
			->body(array( 'user' => $this->username, 'password' => $this->password ))
			->send();

		if( $response->code == 200 && isset($response->body->status) && $response->body->status == 'success' ) {
			if( $save_auth) {
								$tmp = Request::init()
					->addHeader('X-Auth-Token', $response->body->data->authToken)
					->addHeader('X-User-Id', $response->body->data->userId);
				Request::ini( $tmp );
			}
			$this->id = $response->body->data->userId;
			return true;
		} else {
			return false;
		}
	}

	
	public function info() {
		$response = Request::get( $this->api . 'users.info?userId=' . $this->id )->send();
                		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			$this->nickname = $response->body->user->name;
                        if(property_exists($response->body->user,"emails"))
                            $this->email = $response->body->user->emails[0]->address;
			return $response->body;
		} else {
						return false;
		}
	}

	
	public function create() {              
		$response = Request::post( $this->api . 'users.create' )
			->body(array( 
				'name' => $this->nickname,
				'email' => $this->email,
				'username' => $this->username,
				'password' => $this->password,
			))
			->send();                
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			return $response->body->user;
		} else {
			return false;                    
		}
	}

	
	public function deleteUser($userId) {
		$response = Request::post( $this->api . 'users.delete' )
			->body(array('userId' => $userId))
			->send();
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
						return false;
		}
	}
        

       
        public function getAgentsList() {            
            $response = Request::get( $this->api . 'livechat/users/agent' )->send();        
            if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    return $response->raw_body;
            } else {
                    echo( $response->body->error . "\n" );
                    return false;
            }
        }
        
        
        public function regAgent($username) {	
		$response = Request::post( $this->api . 'livechat/users/agent' )
			->body(array('username' => $username))
			->send();
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}
        
                
        public function delAgent($userId) {		
		$response = Request::delete( $this->api . 'livechat/users/agent/'.$userId )->send();               
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}
        
        
        
        
        public function getDeptList() {            
            $response = Request::get( $this->api . 'livechat/department' )->send();                
            if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    return $response->raw_body;
            } else {
                    echo( $response->body->error . "\n" );
                    return false;
            }            
        }   
    
        
        public function regDept($dept,$agent) {            
            $response = Request::post( $this->api . 'livechat/department' )
                    ->body(array("department" =>$dept,
                            "agents" => $agent))
                    ->send();
            if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    return $response->body;
            } else {
                    echo( $response->body->error . "\n" );
                    return false;
            }
    }
       
    
    public function updateDept($deptId,$dept,$agent) {
        try {            
            $response = Request::put( $this->api . 'livechat/department/'.$deptId )
                    ->body(array("department" => $dept ,"agents" => $agent))
                    ->send();
            if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                    return $response->body;
            } else {
                    echo( $response->body->error . "\n" );
                    return false;
            }
        } catch(Exception $e){
           echo  'Caught exception: ',  $e->getMessage(), "<br>"; 
        }
        
    }
    
    
    public function getDeptInfo($deptId) {            
        $response = Request::get( $this->api . 'livechat/department/'.$deptId )->send();        
        if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                return $response->raw_body;
        } else {
                echo( $response->body->error . "\n" );
                return false;
        }
    }
    
    
    
    
    public function deleteDept($deptId) {            
        $response = Request::delete( $this->api . 'livechat/department/'.$deptId )->send();         
        if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
                return true;
        } else {
                echo( $response->body->error . "\n" );
                return false;
        }
    }
}

