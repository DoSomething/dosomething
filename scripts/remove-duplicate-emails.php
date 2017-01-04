<?php
/**
 * Script to remove users with duplicate email accounts.
 *
 * to run:
 * drush --script-path=../scripts/ php-script remove-duplicate-emails.php
 */

$dupes = array_keys(db_query('SELECT mail FROM users GROUP BY mail HAVING COUNT(mail) > 1')->fetchAllKeyed());
$removed = 0;

// Watch out, because we're gonna make a database table. Yee-haw!
db_query('
  CREATE TABLE IF NOT EXISTS `dosomething_northstar_delete_queue` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) unsigned NOT NULL,
    `northstar_id` varchar(32) DEFAULT NULL,
    PRIMARY KEY (`id`));
');

foreach ($dupes as $mail) {
  // Load all users with that duped email address, with the most recently accessed first.
  $users = db_query('SELECT uid FROM users WHERE mail = :mail AND uid != 0 ORDER BY access DESC', [':mail' => $mail]);
  $canonical_uid = 0;

  foreach ($users as $index => $user) {
    $user = user_load($user->uid);

    if ($index == 0) {
      print 'Keeping ' . $user->uid . ' for ' . $user->mail . '.' . PHP_EOL;
      $canonical_uid = $user->uid;
      continue;
    }

    // Set the new email for the deactivated user.
    $new_email = 'duplicate-' . $canonical_uid . '-' . $index . '@dosomething.invalid';
    print ' - Removing ' . $user->uid . ' (' . $user->mail . ' --> ' . $new_email . ')' . PHP_EOL;
    $user = user_save($user, ['mail' => $new_email, 'status' => 0]);
    $removed++;

    // Finally, try to push the updated profile to this Drupal ID in Northstar
    dosomething_northstar_update_user($user);
  }

  // And push the canonical profile once we've fixed all the dupes.
  $canonical_user = user_load($canonical_uid);
  dosomething_northstar_update_user($canonical_user);

  print PHP_EOL;
}

print '[✔] Renamed & deactivated ' . $removed . ' users.' . PHP_EOL;
