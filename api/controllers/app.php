<?php

class App_Controller {
  public function authenticate($auth) {
    if ($auth == APP_AUTH) {
      return true;
    }

    return false;
  }
}
