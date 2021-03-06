<?php

require_once(dirname(__FILE__) . '/../helpers/success-response.php');
require_once(dirname(__FILE__) . '/../helpers/error-response.php');
require_once(dirname(__FILE__) . '/../models/user.php');
require_once(dirname(__FILE__) . '/../controllers/user-tokens.php');
require_once(dirname(__FILE__) . '/../controllers/user-accounts.php');

class User_Controller {
  private $id = false;
  private $email = false;
  private $password = false;
  private $accounts = false;
  private $token = false;

  public function get_user_id() {
    return $this->id;
  }

  private function initialise($user) {
    $this->id = $user['id'];
    $this->email = $user['email'];
    $this->password = $user['password'];
    return true;
  }

  private function generate_new_token() {
    $user_token = new User_Tokens_Controller;
    $user_id = $this->id;

    $auth = $user_token->generate_new_token($user_id);

    if (!$auth) {
      return error_response(48);
    }

    $this->token = $auth;
    return $this->token;
  }

  public function login($email, $password) {
    $user = User_Model::get_user_by_email($email);

    // If the user does not exist then error
    if (!$user) {
      return error_response(49);
    }

    // If the passwords do not match then error
    if ($user['password'] !== $password) {
      return error_response(50);
    }

    $this->initialise($user);
    $auth = $this->generate_new_token();
    $this->read($auth);
  }

  public function create($email, $password, $password_confirm) {
    // If the passwords do not match then error
    if ($password !== $password_confirm) {
      return error_response(15);
    }

    // If the email is not valid then error
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return error_response(16);
    }

    // If the user already exists then error
    if (User_Model::get_user_by_email($email)) {
      return error_response(17);
    }

    // Set the user and error if it fails
    if (!User_Model::set_user($email, $password)) {
      return error_response(18);
    }

    return $this->login($email, $password);
  }

  public function authenticate($auth) {
    $this->token = $auth;
    $user_token = new User_Tokens_Controller;
    $user = $user_token->get_user_by_token($this->token);

    if (!$user) {
      return error_response(51);
    }

    return $this->initialise($user);
  }

  private function get_accounts() {
    $user_accounts = new User_Accounts_Controller;
    $user_accounts->get_accounts_by_user_id($this->id);
    $accounts = $user_accounts->get_accounts_array();

    if ($accounts === false) {
      return error_response(52);
    }

    $this->accounts = $accounts;
    return $this->accounts;
  }

  public function read($auth = false) {
    if (!$this->id) {
      return error_response(53);
    }

    if ($this->get_accounts() === false) {
      return error_response(54);
    }

    $data = array(
      'accounts' => $this->accounts,
      'loggedIn' => true
    );

    if ($auth) {
      $data['auth'] = $auth;
    }

    return success_response($data);
  }

  public function logout() {
    $user_token = new User_Tokens_Controller;
    $user_token->delete_user_token($this->token, $this->id);

    $data = array('loggedIn' => false);
    return success_response($data);
  }
}
