<?php

/**
 * @file
 * Factory hook implementation for including secrets file on ACSF.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$secrets_file = sprintf(
  '/mnt/files/%s.%s/secrets.settings.php',
  $_ENV['AH_SITE_GROUP'],
  $_ENV['AH_SITE_ENVIRONMENT']
);

if (file_exists($secrets_file)) {
  require $secrets_file;
}
