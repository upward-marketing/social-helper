<?php

require_once(dirname(__FILE__) . '/../helpers/error-response.php');
require_once(dirname(__FILE__) . '/../models/cron.php');

class Cron_Controller {
  private $cron = false;
  private $account_id = false;

  public function update($user, $account_id, $cron) {
    if (!Cron_Model::set_account_cron($account_id, $cron)) {
      return error_response(39);
    }

    return $user->read();

  }
}
