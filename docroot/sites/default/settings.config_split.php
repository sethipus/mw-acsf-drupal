<?php

/**
 * @file
 * Setup config split variables, include required files.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

/**
 * Configuration split settings for environments.
 *
 * At anytime one configuration is recommended per environment.
 * If any new split is created, same should be updated in this file. This configurations is
 * used for setting the split for appropriate environment from environment variable.
 *
 * Remove this code if config_spit module is not used.
 */
$env = getenv("AH_SITE_ENVIRONMENT");

$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.stage']['status'] = FALSE;
$config['config_split.config_split.ci']['status'] = FALSE;
$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.ah_other']['status'] = FALSE;
$config['config_split.config_split.local']['status'] = FALSE;
if ($env == '01live') {
  $config['config_split.config_split.prod']['status'] = TRUE;
}elseif ($env == "01test") {
  $config['config_split.config_split.stage']['status'] = TRUE;
}elseif (EnvironmentDetector::isCiEnv()) {
  $config['config_split.config_split.ci']['status'] = TRUE;
}elseif ($env == "01dev") {
  $config['config_split.config_split.dev']['status'] = TRUE;
}elseif ($env == "0101qa") {
  $config['config_split.config_split.ah_other']['status'] = TRUE;
}elseif (EnvironmentDetector::isLocalEnv()) {
  $config['config_split.config_split.local']['status'] = TRUE;
}
