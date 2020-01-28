<?php namespace Controllers;

include_once 'utils/response.php';
require_once 'models/user.php';

use Models\User;

class UserController
{
    private $is_register_validated = true;
    private $is_login_validated = true;
    private $register_error_array = array(
    );
    private $login_error_array = array(
    );

    private function getHeaderAuth()
    {
        foreach (getallheaders() as $name => $value) {
            if ($name === 'Authorization') {
                return $value;
            }
        }
    }
    
    public function register($connection)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User($connection);
            $data = json_decode(file_get_contents("php://input"));
            $this->validateUserAttributes($user, $data);
            if ($this->is_register_validated) {
                // Check for duplicates in the MYSQL table
                $this->validateDuplicatedUserAttributes($user, $connection);
                if ($this->is_register_validated) {
                    // Generate picture.
                    $user->picture_profile_path = $this->generatePicture($user->picture_profile, $user->username);
                    // Generate access token.
                    $user->token = $this->generateAccessToken();
                    // Generate current date.
                    $user->token_expdate = $this->generateCurrentDate();
                    $status = $user->register($connection);
                    if ($status == '200') {
                        returnResponse($status, 'User has been successfully created.');
                    } else {
                        returnResponse('500', $status);
                    }
                }
            }
            if (!$this->is_register_validated) {
                returnResponse('500', $this->register_error_array);
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    public function login($connection)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->getHeaderAuth();
            $user = new User($connection);
            $data = json_decode(file_get_contents("php://input"));
            $this->validateLoginAttributes($user, $data);
            if ($this->is_login_validated) {
                $status = $user->login($connection);
                if ($status == '200') {
                    if ($this->updateExpiryTime($user, $connection)) {
                        $this->returnAccessToken($user, $connection);
                    }
                } else {
                    $this->setLoginPasswordError($status);
                }
            }
            if (!$this->is_login_validated) {
                returnResponse('500', $this->login_error_array);
            }
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }
    }

    private function updateExpiryTime(User $user, $connection)
    {
        $date = date('Y-m-d H:i:s');
        $newtimestamp = strtotime($date . '+ 30 minute');
        $new_exptime = date('Y-m-d H:i:s', $newtimestamp);
        $user->token_expdate = $new_exptime;
        $status = $user->updateExpiryTime($connection);
        if ($status == '200') {
            return true;
        } else {
            returnResponse('500', 'Internal Server Error.');
            return false;
        }
    }

    // This function returns access token to front end, yet to be stored in cookies.
    private function returnAccessToken(User $user, $connection)
    {
        $token_arr = $user->getAccessToken($connection);
        $response_arr = array();
        $response_arr['access_token'] = $token_arr[0];
        if ($token_arr != '500') {
            returnResponse('200', $response_arr);
        } else {
            returnResponse('500', 'Internal Server Error.');
        }
    }

    // This function ensures the provided access token is valid and has not expired yet.
    public function auth($connection)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $access_token = $this->getHeaderAuth();
            $user = new User($connection);
            $this->validateAccessToken($user, $connection, $access_token);
        } else {
            returnResponse('500', 'Invalid HTTP REQUEST.');
        }    
    }

    private function validateAccessToken(User $user, $connection, $access_token)
    {
        $token_expdate_arr = $user->validateAccessToken($connection, $access_token);
        $token_expdate = $token_expdate_arr[0];
        if ($token_expdate_arr !== '500') {
            if ($this->validateExpDate($token_expdate)) {
                returnResponse('200', 'Access Token Valid.');
                return true;
            }
        } else {
            returnResponse('500', 'Invalid Access Token.');
            return false;
        }
    }

    private function validateExpDate($token_expdate)
    {
        $validate = true;
        if ($token_expdate < $this->generateCurrentDate()) {
            $validate = false;
            returnResponse('500', 'Expired Access Token.');
        }
        return $validate;
    }

    private function validateLoginAttributes(User $user, $data)
    {
        // Email
        if ($this->validateEmail($data->email)) {
            $user->email = $data->email;
        } else {
            $this->setLoginEmailError();
        }
        // Password
        if ($this->validatePassword($data->password)) {
            $user->password = $data->password;
        } else {
            $this->setLoginPasswordError('Password is required.');
        }
    }

    private function validateUserAttributes(User $user, $data)
    {
        // Username
        if ($this->validateUsername($data->username)) {
            $user->username = $data->username;
        } else {
            $this->setUsernameError();
        }
        // Email
        if ($this->validateEmail($data->email)) {
            $user->email = $data->email;
        } else {
            $this->setRegisterEmailError();
        }
        // Phone Number
        if ($this->validatePhone($data->no_telp)) {
            $user->no_telp = $data->no_telp;
        } else {
            $this->setPhoneError();
        }
        // Picture Profile
        if ($this->validatePicture($data->picture_profile)) {
            $user->picture_profile = $data->picture_profile;
        } else {
            $this->setPictureError();
        }
        // Password
        if ($this->validatePassword($data->password)) {
            $user->password = password_hash($data->password, PASSWORD_DEFAULT);
        } else {
            $this->setRegisterPasswordError();
        }
    }

    private function validateUsername($username)
    {
        $validated = false;
        if (preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $validated = true;
        }
        return $validated;
    }

    private function validateEmail($email)
    {
        $validated = false;
        if (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) {
            $validated = true;
        }
        return $validated;
    }

    private function validatePhone($no_telp)
    {
        $validated = false;
        if (preg_match("/^(\d{9}|\d{12})$/", $no_telp)) {
            $validated = true;
        }
        return $validated;
    }

    private function validatePicture($picture_profile)
    {
        $validated = false;
        if ($picture_profile) {
            $validated = true;
        }
        return $validated;
    }

    private function validatePassword($password)
    {
        $validated = false;
        if ($password) {
            $validated = true;
        }
        return $validated;
    }
    // https://stackoverflow.com/questions/15153776/convert-base64-string-to-an-image-file
    private function generatePicture($picture_data, $filename)
    {
        $full_path = 'pictures/users/' . $filename;
        $file = fopen($full_path, "wb");
        $data = explode(',', $picture_data);
        // Remove file headers.
        fwrite($file, base64_decode($data[1]));
        fclose($file);
        return $full_path;
    }

    private function generateAccessToken()
    {
        return uniqid();
    }

    private function generateCurrentDate()
    {
        return date('Y-m-d H:i:s');
    }

    private function setRegisterEmailError()
    {
        $this->register_error_array['email'] = 'Invalid Email Format.';
        $this->is_register_validated = false;
    }

    private function setRegisterPasswordError()
    {
        $this->register_error_array['password'] = 'Password is required.';
        $this->is_register_validated = false;
    }

    private function setLoginEmailError()
    {
        $this->login_error_array['email'] = 'Invalid Email Format.';
        $this->is_login_validated = false;
    }

    private function setLoginPasswordError($msg)
    {
        $this->login_error_array['password'] = $msg;
        $this->is_login_validated = false;
    }

    private function setUsernameError()
    {
        $this->register_error_array['username'] = 'Username can only contain letters, numbers, and underscores.';
        $this->is_register_validated = false;
    }

    private function setPhoneError()
    {
        $this->register_error_array['no_telp'] = 'Phone number can only contain numbers (9-12 digits).';
        $this->is_register_validated = false;
    }

    private function setPictureError()
    {
        $this->register_error_array['picture_profile'] = 'Picture profile is required.';
        $this->is_register_validated = false;
    }

    private function setDuplicatedValueError($value, $value_string)
    {
        $this->register_error_array[$value_string] = $value_string . ' ' .  $value . ' already exists. Please use another ' . $value_string . '.';
        $this->is_register_validated = false;
    }

    private function validateDuplicatedUserAttributes(User $user, $connection)
    {
        if (!$user->validateDuplicate($connection, $user->username, 'username')) {
            $this->setDuplicatedValueError($user->username, 'username');
        }
        if (!$user->validateDuplicate($connection, $user->email, 'email')) {
            $this->setDuplicatedValueError($user->email, 'email');
        }
        if (!$user->validateDuplicate($connection, $user->no_telp, 'no_telp')) {
            $this->setDuplicatedValueError($user->no_telp, 'no_telp');
        }
    }
}
