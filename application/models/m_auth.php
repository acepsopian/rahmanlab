<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_auth extends CI_Model {

    var $client_service = "frontend-client";
    var $auth_key       = "rshkapi";

    public function check_auth_client(){
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key  = $this->input->get_request_header('Auth-Key', TRUE);
        
        if($client_service == $this->client_service && $auth_key == $this->auth_key){
            return true;
        } else {
            return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
        }
    }

    public function login($username,$password)
    {
        $q  = $this->db->select('password,id_user')->from('m_users')->where('username',$username)->get()->row();

        if($q == ""){
             echo $username + "Username tidak ditemukan";
            return array('status' => 401,'message' => 'Username tidak ditemukan.');
        } else {
            $hashed_password = $q->password;
            $id_user              = $q->id_user;
            // echo $hashed_password ." ".$password;
            echo $password;

        //exit;
            if (hash_equals($hashed_password, crypt($password, $hashed_password))) {
             $last_login = date('Y-m-d H:i:s');
             $token = crypt(substr( md5(rand()), 0, 7));
             $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
             $this->db->trans_start();
             $this->db->where('id_user',$id_user)->update('m_users',array('last_login' => $last_login));
             $this->db->insert('m_users_authentication',array('id_user' => $id_user,'token' => $token,'expired_at' => $expired_at));
             if ($this->db->trans_status() === FALSE){
              $this->db->trans_rollback();
              return array('status' => 500,'message' => 'Internal server error.');
          } else {
              $this->db->trans_commit();
              return array('status' => 200,'message' => 'Successfully login.','id_user' => $id_user, 'token' => $token);
          }
      } else {
        return array('status' => 401,'message' => ': Passwod Salah.');
        // echo " : Passwod Salah.";
        exit();
       // return array('status' => 201,'message' => 'Data has been created.');
        return array('status' => 401,'message' => ': Passwod Salah.');
    }
}
}

public function logout()
{
    $id_user  = $this->input->get_request_header('User-ID', TRUE);
    $token     = $this->input->get_request_header('Authorization', TRUE);
    $this->db->where('id_user',$id_user)->where('token',$token)->delete('m_users_authentication');
    return array('status' => 200,'message' => 'Successfully logout.');
}

public function auth()
{
    $id_user  = $this->input->get_request_header('User-ID', TRUE);
    $token     = $this->input->get_request_header('Authorization', TRUE);
    $q  = $this->db->select('expired_at')->from('users_authentication')->where('id_user',$id_user)->where('token',$token)->get()->row();
    if($q == ""){
        return json_output(401,array('status' => 401,'message' => 'Unauthorized.'));
    } else {
        if($q->expired_at < date('Y-m-d H:i:s')){
            return json_output(401,array('status' => 401,'message' => 'Your session has been expired.'));
        } else {
            $updated_at = date('Y-m-d H:i:s');
            $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
            $this->db->where('id_user',$id_user)->where('token',$token)->update('m_users_authentication',array('expired_at' => $expired_at,'updated_at' => $updated_at));
            return array('status' => 200,'message' => 'Authorized.');
        }
    }
}

}
