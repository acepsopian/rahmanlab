<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function login()
	{


		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_client = $this->m_auth->check_auth_client();
			if($check_auth_client == true){
				$params = json_decode(file_get_contents('php://input'), TRUE);
				if ($params['username'] == "" || $params['password'] == "") {

					$response = array('status' => 400,'message' =>  'User atau password Salah!');
				} else {
					$username = $params['username'];
					$password = $params['password'];
					$response = $this->m_auth->login($username,$password);
					//}
					json_output($response['status'],$response);
					
				}
			}


		}
	}

	public function logout()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_client = $this->m_auth->check_auth_client();
			if($check_auth_client == true){
				$response = $this->m_auth->logout();
				json_output($response['status'],$response);
			}
		}
	}
	
}
