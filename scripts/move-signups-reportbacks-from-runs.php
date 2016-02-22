<?php
/*
 * Script to move archived signups or reportbacks to a campaign node.
 *
 * To run you need to pass which table you would like to update. signup|reportback
 * drush --script-path=../scripts/ php-script move-signups-reportbacks-from-runs.php signup|reportback
 *
 */

$arg = drush_get_arguments();
$table = 'dosomething_' . $arg[2];

if (db_table_exists($table)) {
  $query = db_select($table, 't');
  $query->join('field_data_field_campaigns', 'c', 't.nid = c.entity_id');
  $query->join('node', 'n', 't.nid = n.nid');
  $results = $query->fields('t', ['nid'])
  ->fields('c', ['field_campaigns_target_id'])
  ->condition('n.type', 'campaign_run')
  ->execute();

  foreach($results as $result) {
    try{
      db_update($table)
      ->fields(['nid' => $result->field_campaigns_target_id])
      ->condition('nid', $result->nid)
      ->execute();
    }
    catch(Exception $e) {
      echo 'bad things: ' . $e;
    }
  }
  echo 'done';
}
else {
  echo 'Are you outta your mind? ' . $arg[2] . ' is not a valid table' .  "\n";
}
