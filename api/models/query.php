<?php

require_once('db.php');

class Query_Model {
  public static function get_query_by_value($query_string) {
    $query = '
      SELECT *
      FROM queries
      WHERE query = ?
    ';

    $params = array(
      array('s', $query_string)
    );

    return Db::get_only_row($query, $params);
  }

  public static function create_query($query_string) {
    $query = '
      INSERT INTO queries (query)
      VALUES (?)
    ';

    $params = array(
      array('s', $query_string)
    );

    return Db::insert_return_row($query, $params, 'queries');
  }
}
