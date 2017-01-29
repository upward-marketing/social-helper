<?php

require_once(dirname(__FILE__) . '/../models/account-queries.php');
require_once(dirname(__FILE__) . '/../controllers/query.php');
require_once(dirname(__FILE__) . '/../controllers/account.php');
require_once(dirname(__FILE__) . '/../controllers/tweet-query.php');
require_once(dirname(__FILE__) . '/../controllers/account-tweets.php');

class Account_Queries_Controller {
  private $account = false;
  private $query = false;
  private $id = false;
  private $queries = array();

  private function filter($tweet) {
    $exists = Account_Tweets_Model::does_account_tweet_exist(
      $tweet->get_id(),
      $this->account->get_account_id()
    );

    if ($exists) {
      return false;
    }

    return true;
  }

  private function get_next_tweets($before_id = false) {
    // get query tweets in best order (by id for now, as twitter figures out ordr for us)
    $tweet_queries = new Tweet_Query_Controller;
    $tweet_queries->set_query($this->query);
    $tweets = $tweet_queries->get_query_tweets($before_id);

    if (!$tweets) {
      return false;
    }

    $last_count = count($tweets) - 1;
    $last_id = $tweets[$last_count]->get_id();

    if (!$last_id) {
      return error_response(385709);
    }

    $tweet_array = array();

    foreach($tweets as $tweet) {
      if ($this->filter($tweet)) {
        $tweet_array[] = $tweet;
      }
    }

    if (!$tweet_array) {
      return $this->get_next_tweets($last_id);
    }

    $account_tweets = array();

    foreach ($tweet_array as $tweet) {
      $account_tweet = new Account_Tweets_Controller;
      $account_tweet->set_tweet($tweet);
      $account_tweet->set_account($this->account);
      $account_tweets[] = $account_tweet;
    }

    return $account_tweets;
  }

  public function tweet_next() {
    $tweets = $this->get_next_tweets();

    if ($tweets) {
      foreach($tweets as $account_tweets) {
        if ($account_tweets->retweet($this->query->get_id())) {
          return true;
        }
      }
    }

    return false;
  }

  public function get_queries() {
    return $this->queries;
  }

  public function set_account($account_id) {
    $account = new Account_Controller;
    $account->get_account_by_id($account_id);
    $this->account = $account;
  }

  public function set_query($query_id) {
    $query = new Query_Controller;
    $query->set_id($query_id);
    $this->query = $query;
  }

  public function get_account_queries($account_id) {
    $queries = Account_Queries_Model::get_account_queries($account_id);
    $array = array();

    foreach($queries as $query_data) {
      $query = new Query_Controller;
      $query->initilise($query_data);
      $array[] = $query;
    }

    $this->queries = $array;
    return $this->queries;
  }

  public function account_query_exists($account_id, $query) {
    return Account_Queries_Model::account_query_exists(
      $account_id,
      $query->get_id()
    );
  }

  public function create_account_query($account_id, $query) {
    return Account_Queries_Model::create_account_query(
      $account_id,
      $query->get_id()
    );
  }
}