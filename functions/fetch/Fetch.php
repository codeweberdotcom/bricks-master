<?php

namespace Codeweber\Functions\Fetch;

class Fetch
{
   public static function request($url, $method = 'GET', $params = [])
   {
      $args = [
         'method' => strtoupper($method),
         'body' => $params,
      ];

      $response = wp_remote_request($url, $args);

      if (is_wp_error($response)) {
         return ['status' => 'error', 'message' => $response->get_error_message()];
      }

      $body = wp_remote_retrieve_body($response);
      return json_decode($body, true);
   }
}
