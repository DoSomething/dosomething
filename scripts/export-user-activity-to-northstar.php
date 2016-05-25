<?php
/**
 * Script to export user information from drupal into Northstar.
 *
 * to run:
 * drush --script-path=../scripts/ php-script export-user-actvity-to-northstar.php
 */

include_once('../lib/modules/dosomething/dosomething_northstar/dosomething_northstar.module');

$last_saved = variable_get('dosomething_northstar_last_user_activity_migrated', NULL);
if ($last_saved) {
  $users = db_query("SELECT u.uid
            FROM users u
            WHERE uid > $last_saved");
}
else {
  // Get all the users!
  $users = db_query('SELECT u.uid
                   FROM users u');
}

foreach ($users as $user) {
  // Create json object
  $user = user_load($user->uid);
  $ns_user = build_northstar_user($user);

  // Use old drupal_http_request method.
  $client = _dosomething_northstar_build_http_client();
  $response = drupal_http_request($client['base_url'] . '/users', [
    'headers' => $client['headers'],
    'method' => 'POST',
    'data' => json_encode($ns_user),
    ]);

  // If the script fails, we can use this to start the script from a previous person.
  variable_set('dosomething_northstar_last_user_activity_migrated', $user->uid);
}

/**
 *
 */
function build_northstar_user($user) {
  // Optional fields
  $optional = [
    'mobile'       => 'field_mobile',
    'birthdate'    => 'field_birthdate',
    'first_name'   => 'field_first_name',
    'last_name'    => 'field_last_name',
    'source'       => 'field_user_registration_source',
    'school_id'    => 'field_school_id',
  ];

  // Address fields
  $address = [
    'country'        => 'country',
    'addr_street1'   => 'thoroughfare',
    'addr_street2'   => 'premise',
    'addr_city'      => 'locality',
    'addr_state'     => 'administrative_area',
    'addr_zip'       => 'postal_code',
  ];

  $ns_user = [
    'email'            => $user->mail,
    'drupal_id'        => $user->uid,
    'drupal_password'  => $user->pass,
    'created_at'       => $user->created,
  ];

  // Set values in ns_user if they are set.
  foreach ($optional as $ns_key => $drupal_key) {
   $field = $user->$drupal_key;
    if (!empty($field[LANGUAGE_NONE][0]['value'])) {
      $ns_user[$ns_key] = $field[LANGUAGE_NONE][0]['value'];
    }
  }
  // Set address values.
  foreach ($address as $ns_key => $drupal_key) {
    $field = $user->field_address[LANGUAGE_NONE][0];
    if (!empty($field[$drupal_key]['value'])) {
      $ns_user[$ns_key] = $field[$drupal_key]['value'];
    }
  }
  return $ns_user;
}
