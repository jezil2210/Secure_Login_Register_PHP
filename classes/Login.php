<?php

class Login{

    private $db_connection = null;
    public errors = array();
    public $messages = array()

    public function __construct(){
                      
           session_start();
           
           if (isset($_GET['logout'])){
              $this->doLogout(); 
           }

           elseif (isset($_POST['login'])){
              $this->doLoginWithPostData(); 
           }          
    }
    
    private function dologinWithPostData(){
            
            if (empty($_POST['user_name'])) {
                $this->errors[] = "Username field was empty.";
            } elseif(empty($_POST['user_password'])){
                $this->errors[] = "Password field was empty.";
            } elseif(!empty($_POST['user_name']) && !empty($_POST['user_password'])){
              
                 $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 
                 
                 if (!$this->db_connection->set_charset('utf-8')){
                     $this->errors[] = $this->db_connection->error;
                 } 

                 if (!$this->db_connection->connect-errno){
                     
                     $user_name = $this->db_connection->real_escape_string($_POST['user_name']);
                      
                     $stmt = $this->db_connection->prepare("SELECT user_name, user_email, user_pasword_hash
                      FROM users WHERE user_name = ? OR user_mail = ?");
                     $stmt->bind_param("ss", $user_name, $user_email);
                     $stmt->execute();
                     $result_of_login_check = $stmt->get_result();
                     $num_rows = $result_of_login_check->num_rows;

                     //if the user exists
                     if($num_rows == 1){
                        
                        $result_row = $result_of_login_check->fetch_object();
                        
                        if (password_verify($_POST['user_password'], $result_row->user_password_hash)){
                            
                            $_SESSION['user_name'] = $result_row->user_name;
                            $_SESSION['user_email'] = $result_row->user_email;
                            $_SESSION['user_login_status'] = 1; 
                          
                        } else {
                            $this->errors[] = "Wrong password. Try again...";
                        } 
                     } else { 
                         $this->errors[] = "This user does not exists..."
                     } 
                 } else { 
                     $this->errors[] = "Database connection problem..."
                 }
            }

    }
    
    public function doLogout(){
        // delete the session of the user
        $_SESSION = array();
        session_destroy();
        // return a little feeedback message
        $this->messages[] = "You have been logged out.";
    }

    public function isUserLoggedIn(){
        if (isset($_SESSION['user_login_status']) AND $_SESSION['user_login_status'] == 1) {
            return true;
        }
        // default return
        return false;
    }


}

?>
