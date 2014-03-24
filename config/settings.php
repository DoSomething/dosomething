<?php

/**
 * Lets do something about globals
 */
define('DS_PROFILE_PATH', 'profiles/dosomething');
define('DS_MODULES_PATH', DS_PROFILE_PATH . '/modules');
define('DS_THEMES_PATH', DS_PROFILE_PATH . 'themes');
define('DS_LIBRARIES_PATH', DS_PROFILE_PATH . '/libraries');

/**
 * Database settings:
 */
$databases['default']['default'] = array(
  'database' => getenv('DS_DB_MASTER_NAME') ?: 'dosomething',
  'username' => getenv('DS_DB_MASTER_USER') ?: 'root',
  'password' => getenv('DS_DB_MASTER_PASS') ?: '',
  'host' => getenv('DS_DB_MASTER_HOST') ?: 'localhost',
  'port' => getenv('DS_DB_MASTER_PORT') ?: '3306',
  'driver' => getenv('DS_DB_MASTER_DRIVER') ?: 'mysql',
  'prefix' => getenv('DS_DB_MASTER_PREFIX') ?: '',
);

/**
 * Hosts & urls
 */
$hostname = $_SERVER['HTTP_HOST'] ?: 'dev.dosomething.org';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$insecure_port = getenv('DS_INSECURE_PORT') ?: 8888;
$secure_port = getenv('DS_SECURE_PORT') ?: 8889;

// $hostname may already have port included. Check it before you wreck it!
if (!preg_match('/.+:[0-9]+$/', $hostname) && !empty($insecure_port)
    && ($insecure_port != 80)) {

  $base_url = $protocol . $hostname . ':' . $insecure_port;
}
else {
  $base_url = $protocol . $hostname;
}

$conf['https'] = TRUE;

/**
 * Caching
 */
// Add Varnish as the page cache handler.
$conf['varnish_version'] = '3';
$conf['cache_backends'] = array('profiles/dosomething/modules/contrib/varnish/varnish.cache.inc');
$conf['cache_class_cache_page'] = 'VarnishCache';

// This is managed from salt://varnishd/secret
$conf['varnish_control_key'] = '00c9203c65874ca5b4c359e19f00bf56';

// Drupal 7 does not cache pages when we invoke hooks during bootstrap.
// This needs to be disabled.
$conf['page_cache_invoke_hooks'] = FALSE;

require_once DS_MODULES_PATH . '/contrib/redis/redis.autoload.inc';

// Predis autoloader not working. Declare here instead.
spl_autoload_register(function($classname) {
  if (0 === strpos($classname, 'Predis\\')) {
    $filename = DS_LIBRARIES_PATH . '/predis/lib/';
    $filename .= str_replace('\\', '/', $classname) . '.php';
    return (bool)require_once $filename;
  }
  return false;
});

$conf['cache_backends'][] = DS_MODULES_PATH . '/contrib/redis/redis.autoload.inc';
$conf['redis_client_interface'] = 'Predis';
$conf['redis_client_host'] = getenv('DS_REDIS_HOST') ?: '127.0.0.1';
$conf['redis_client_port'] = getenv('DS_REDIS_PORT') ?: '6379';
$conf['cache_class_cache'] = 'Redis_Cache';
$conf['cache_class_cache_menu'] = 'Redis_Cache';
$conf['cache_class_cache_bootstrap'] = 'Redis_Cache';

$conf['cache_class_cache_form'] = 'DrupalDatabaseCache';
$conf['lock_inc'] = DS_MODULES_PATH . '/contrib/redis/redis.lock.inc';

/**
 * Salt hash:
 */
$drupal_hash_salt = '3i_SZ1VTl_8FBxXeZhTEvf6LkeVNypM0EV90tNuHs5k';

/**
 * PHP Configuration overrides:
 */
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.cookie_lifetime', 2000000);

/**
 * Fast 404 pages:
 */
$conf['404_fast_paths_exclude'] = '/\/(?:styles)\//';
$conf['404_fast_paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$conf['404_fast_html'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

/**
 * Setting the beta and legacy running campaign nids.
 */
$conf['MOMM'] = array('legacy' =>731098, 'beta' => 850);

/**
 * Load environment aware settings override files
 */
$environment = getenv('DS_ENVIRONMENT') ?: 'local';

// Include local settings file if it exists.
if (is_readable('sites/default/settings.'. $environment .'.php')) {
  include_once('sites/default/settings.'. $environment .'.php');
}

$conf['locale_custom_strings_en'][''] = array(
   'Registration successful. You are now logged in.' => "You've created an account with DoSomething.org."
 );

$conf['optimizely_id'] = '747623297';
