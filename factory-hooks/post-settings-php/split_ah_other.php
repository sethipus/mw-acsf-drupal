<?php

/**
 * Configuration split settings for QA environments.
 *
 * At anytime one configuration is recommended per environment.
 * If any new split is created using ah_other, same should be updated in this file.
 * This configurations is used for setting the split for appropriate environment
 * from environment variable.
 *
 * Remove this code if config_spit module is not used.
 */
$env = getenv("AH_SITE_ENVIRONMENT");

$config['config_split.config_split.ah_other']['status'] = FALSE;
if ($env == "0101qa") {
  $config['config_split.config_split.ah_other']['status'] = TRUE;
}
