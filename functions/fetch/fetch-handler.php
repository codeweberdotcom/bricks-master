<?php

defined( 'ABSPATH' ) || exit;

namespace Codeweber\Functions\Fetch;

require_once __DIR__ . '/Fetch.php';
require_once __DIR__ . '/exampleFunction.php';
require_once __DIR__ . '/getPosts.php';
require_once __DIR__ . '/loadMoreItems.php';
require_once __DIR__ . '/getHotspotContent.php';
require_once __DIR__ . '/getPostsForHotspot.php';
require_once __DIR__ . '/getPostCardTemplates.php';

add_action('wp_ajax_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');
add_action('wp_ajax_nopriv_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');

function handle_fetch_action()
{
   if (!check_ajax_referer('fetch_action_nonce', 'nonce', false)) {
      wp_send_json_error(['message' => 'Security check failed.'], 403);
   }

   $actionType = sanitize_text_field(wp_unslash($_POST['actionType'] ?? ''));
   $params = json_decode(wp_unslash($_POST['params'] ?? '[]'), true);

   if ($actionType === 'exampleFunction') {
      $response = exampleFunction($params);
      wp_send_json($response);
   }

   if ($actionType === 'getPosts') {
      $response = getPosts($params);
      wp_send_json($response);
   }

   if ($actionType === 'loadMoreItems') {
      $response = loadMoreItems($params);
      wp_send_json($response);
   }

   if ($actionType === 'getHotspotContent') {
      $response = getHotspotContent($params);
      wp_send_json($response);
   }

   if ($actionType === 'getPostsForHotspot') {
      $response = getPostsForHotspot($params);
      wp_send_json($response);
   }

   if ($actionType === 'getPostCardTemplates') {
      $response = getPostCardTemplates($params);
      wp_send_json($response);
   }

   wp_send_json([
      'status' => 'error',
      'message' => 'Неизвестное действие.',
   ]);
}