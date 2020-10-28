<?php

/**
 * @file
 * ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks/settings-php/
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

// Set config directories to default location.
$config_directories['vcs'] = '../config/default';
$config_directories['sync'] = '../config/default';
$settings['config_sync_directory'] = '../config/default';
$config['search_api_solr.settings']['site_hash'] = $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'];
