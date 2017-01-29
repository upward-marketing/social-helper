<?php

require_once(dirname(__FILE__) . '/../controllers/account.php');
require_once(dirname(__FILE__) . '/../models/accounts.php');
require_once(dirname(__FILE__) . '/../helpers/error-response.php');

class Accounts_Controller {
  private $accounts = array();

  private function get_accounts() {
    $accounts = Accounts_Model::get_accounts();

    if (!$accounts) {
      return error_response(9863);
    }

    $accounts_array = array();

    foreach ($accounts as $account_data) {
      $account = new Account_Controller;
      $account->initialise_account($account_data);
      $accounts_array[] = $account;
    }

    $this->accounts = $accounts_array;
  }

  private function should_tweet_if_ready($account) {
    $last_started = strtotime($account->get_last_started_cron());
    $last_ran = strtotime($account->get_last_ran_cron());

    if ($last_ran >= $last_started) {
      return true;
    }

    if ($last_started > strtotime('+10 minutes')) {
      return true;
    }

    return false;
  }

  public function tweet_from_accounts() {
    $this->get_accounts();

    foreach ($this->accounts as $account) {
      if ($this->should_tweet_if_ready($account)) {
        $account->set_last_started_cron();
        $account->tweet_if_ready();
        $account->set_last_ran_cron();
      }
    }
  }
}