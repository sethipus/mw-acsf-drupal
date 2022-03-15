<?php

namespace Drupal\mars_cloudflarepurge;

/**
 * Cloudflare Purge Credentials.
 */
class CloudflarePurgeCredentials {

  /**
   * Function to get response.
   */
  public static function cfPurgeCache(string $zoneId, string $authorization, string $specific_urls, string $purge_specific_url_toggle) {   
    
    if ($purge_specific_url_toggle == FALSE) {
      // Purge everything for specific Zone ID.
      $ch_purge = curl_init();
      curl_setopt($ch_purge, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/".$zoneId."/purge_cache");
      curl_setopt($ch_purge, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch_purge, CURLOPT_RETURNTRANSFER, 1);
      $headers = [
          'Authorization: Bearer '.$authorization,
          'Content-Type: application/json'
      ];
      $data = json_encode(array("purge_everything" => true));
      curl_setopt($ch_purge, CURLOPT_POST, true);
      curl_setopt($ch_purge, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch_purge, CURLOPT_HTTPHEADER, $headers);

      $result = json_decode(curl_exec($ch_purge),true);
      curl_close($ch_purge);
      if ($result['success'] == 1) {
        return 200;
      }
      elseif ($result['errors'][0]['message']) {
        $error_msg = $result['errors'][0]['message'];
        return $error_msg;
      }
      else{
        $error_msg = 'Some issue happened related to Cloudflare. Please contact site admin';
      }

    }
    else{
      // cloudflare purge for specific URLs.
      $ch_purge = curl_init();
      curl_setopt($ch_purge, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/".$zoneId."/purge_cache");
      curl_setopt($ch_purge, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch_purge, CURLOPT_RETURNTRANSFER, 1);
      $headers = [
          'Authorization: Bearer '.$authorization,
          'Content-Type: application/json'
      ];
      
      $specific_urls = trim(preg_replace('/\s+/', ' ', $specific_urls));
      $data = '{"prefixes":['.$specific_urls.'] }';
      curl_setopt($ch_purge, CURLOPT_POST, true);
      curl_setopt($ch_purge, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch_purge, CURLOPT_HTTPHEADER, $headers);

      $result = json_decode(curl_exec($ch_purge),true);
      curl_close($ch_purge);
      if ($result['success'] == 1) {
        return 200;
      }
      elseif ($result['errors'][0]['message']) {
        $error_msg = $result['errors'][0]['message'];
        return $error_msg;
      }
      else{
        $error_msg = 'Some issue happened related to Cloudflare. Please contact site admin';
      }
    }
  }

}
