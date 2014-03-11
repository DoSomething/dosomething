<?php
/**
 * @file
 * Drupal instance-specific configuration file. This is included at the end of
 * the distributed settings.php, so use this to set everything related to
 * your local instance.
 *
 * Example settings below. See the original settings.php for full
 * documentation.
 *
 * TO USE: Copy to html/sites/default/settings.local.php and edit. Your local
 * copy will be ignored by Git.
 */

// Database settings.
$databases = array (
  'default' =>
    array (
      'default' =>
        array (
          'database' => 'dosomething',
          'username' => 'root',
          'password' => '',
          'host' => 'localhost',
          'port' => '',
          'driver' => 'mysql',
          'prefix' => '',
        ),
    ),
);

// Salt for one-time login links and cancel links, form tokens, etc.
$drupal_hash_salt = '3i_SZ1VTl_8FBxXeZhTEvf6LkeVNypM0EV90tNuHs5k';

// Base URL (optional). This should make correspond to the securepages_basepath
// setting, below.
$base_url = 'http://dev.dosomething.org:8888';  // NO trailing slash!

// Secure Pages integration.
$conf['https'] = TRUE;
$conf['securepages_basepath'] = 'http://dev.dosomething.org:8888';
$conf['securepages_basepath_ssl'] = 'https://dev.dosomething.org:8889';

// Add Varnish as the page cache handler.
$conf['varnish_version'] = '3';
$conf['cache_backends'] = array('profiles/dosomething/modules/contrib/varnish/varnish.cache.inc');
$conf['cache_class_cache_page'] = 'VarnishCache';

// This is managed from salt://varnishd/secret
$conf['varnish_control_key'] = '00c9203c65874ca5b4c359e19f00bf56';
    
// Drupal 7 does not cache pages when we invoke hooks during bootstrap.
// This needs to be disabled.
$conf['page_cache_invoke_hooks'] = FALSE;

// These settings point to the solr instance on staging.
$conf['apachesolr_host'] = '192.168.1.169';
$conf['apachesolr_port'] = '8008';
$conf['apachesolr_path'] = '/solr/collection1';
$conf['apachesolr_read_only'] = 1;
