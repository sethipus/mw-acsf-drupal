<?php

namespace Drupal\mars_cloudflarepurge;

/**
 * The PurgeCloudFlareCache class.
 */
class PurgeCloudFlareCache {

  /**
   * Sends a request to the CloudFlare.
   *
   * @param string $request_type
   *   The type of request being made.
   *   Expected to be one of: REQUEST_TYPE_PURGE_TAG, REQUEST_TYPE_PURGE_URL
   *   REQUEST_TYPE_PURGE_EVERYTHING.
   * @param string $zone_id
   *   Zone Id to access Zone.
   * @param string $authorization
   *   Authorization to access Cloudflare API.
   * @param string $data
   *   Data to be passed with the CURL request.
   */
  public static function makeRequest($request_type, $zone_id, $authorization, $data) {
    \Drupal::logger('mars_cloudflarepurge')->info('Cloudflare purger : Calling makeRequest function while purging ' . $request_type);
    try {
      $ch_purge = curl_init();
      curl_setopt($ch_purge, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/" . $zone_id . "/purge_cache");
      curl_setopt($ch_purge, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch_purge, CURLOPT_RETURNTRANSFER, 1);
      $headers = [
        'Authorization: Bearer ' . $authorization,
        'Content-Type: application/json',
      ];
      curl_setopt($ch_purge, CURLOPT_POST, TRUE);
      curl_setopt($ch_purge, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch_purge, CURLOPT_HTTPHEADER, $headers);

      $result = json_decode(curl_exec($ch_purge), TRUE);
      curl_close($ch_purge);
      if ($result['success'] == 1) {
        \Drupal::logger('mars_cloudflarepurge')->info('Cloudflare purger : Got success response with code 200 while purging ' . $request_type);
      }

      elseif ($result['errors'][0]['message']) {
        $error_msg = $result['errors'][0]['message'];
        \Drupal::logger('mars_cloudflarepurge')->error('Cloudflare purger : Got error ' . $error_msg . 'while purging ' . $request_type);
      }

      else {
        \Drupal::logger('mars_cloudflarepurge')->error('Cloudflare purger : Some issue happened related to Cloudflare curl request while purging ' . $request_type . ' Please contact site admin');
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('mars_cloudflarepurge')->error($e->getMessage());
    }

  }

}
