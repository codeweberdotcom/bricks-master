<?php

namespace Codeweber\Functions\Fetch;

require_once __DIR__ . '/Fetch.php';
require_once __DIR__ . '/exampleFunction.php';
require_once __DIR__ . '/getPosts.php';

add_action('wp_ajax_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');
add_action('wp_ajax_nopriv_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');

function handle_fetch_action()
{
   $actionType = $_POST['actionType'] ?? null;
   $params = json_decode(stripslashes($_POST['params'] ?? '[]'), true);

   if ($actionType === 'exampleFunction') {
      $response = exampleFunction($params);
      wp_send_json($response);
   }

   if ($actionType === 'getPosts') {
      $response = getPosts($params);
      wp_send_json($response);
   }

   wp_send_json([
      'status' => 'error',
      'message' => 'Неизвестное действие.',
   ]);
}