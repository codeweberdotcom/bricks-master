<?php

/**
 * Универсальная функция для кнопок分享 с поддержкой регионов
 *
 * Выводит выпадающее меню с кнопками социальных сетей, адаптированными под регион пользователя.
 * Автоматически определяет регион по языку или может быть указан вручную. Поддерживает
 * расширенную кастомизацию через параметры и фильтры WordPress.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Опционально. Массив параметров конфигурации.
 *
 *     @type string   $button_class    CSS классы для основной кнопки. По умолчанию 'btn btn-red btn-icon btn-icon-start dropdown-toggle mb-0 me-0 ' + getThemeButton()
 *     @type string   $dropdown_class  CSS классы для выпадающего меню. По умолчанию 'dropdown-menu'
 *     @type string   $item_class      CSS классы для элементов меню. По умолчанию 'dropdown-item'
 *     @type string   $button_text     Текст на кнопке. По умолчанию __('Share', 'codeweber')
 *     @type string   $button_icon     CSS класс иконки кнопки. По умолчанию 'uil uil-share-alt'
 *     @type array    $networks        Массив конкретных сетей для отображения (переопределяет регион). По умолчанию []
 *     @type string   $region          Регион: 'eu' (Европа), 'ru' (Россия), 'auto' (автоопределение). По умолчанию 'auto'
 *     @type string   $title           Заголовок для分享. По умолчанию get_the_title()
 *     @type string   $url             URL для分享. По умолчанию get_permalink()
 *     @type string   $hashtags        Хэштеги для Twitter. По умолчанию 'law,legal'
 *     @type string   $email_subject   Тема для email. По умолчанию зависит от региона
 *     @type string   $email_to        Email получателя. По умолчанию email администратора сайта
 * }
 *
 * @return void
 *
 * @example
 * // Простое использование
 * codeweber_share_page();
 *
 * @example
 * // Для российского региона
 * codeweber_share_page(['region' => 'ru']);
 *
 * @example
 * // Для Европы с кастомными параметрами
 * codeweber_share_page([
 *     'region' => 'eu',
 *     'hashtags' => 'eulaw,legaladvice',
 *     'email_subject' => 'Interesting EU legal article',
 *     'button_text' => 'Share in EU'
 * ]);
 *
 * @example  
 * // Только определенные сети
 * codeweber_share_page(['networks' => ['whatsapp', 'telegram', 'email']]);
 *
 * @example
 * // Кастомный стиль и текст
 * codeweber_share_page([
 *     'button_class' => 'btn btn-primary btn-sm',
 *     'button_text'  => 'Поделиться статьей'
 * ]);
 *
 * @uses codeweber_detect_region() Для автоматического определения региона
 * @uses codeweber_get_share_networks() Для получения списка сетей по региону
 * @uses apply_filters('codeweber_share_networks') Фильтр для кастомизации сетей
 *
 * @available_networks
 * Европа (eu): linkedin, twitter, facebook, whatsapp, email, xing
 * Россия (ru): vk, telegram, whatsapp, odnoklassniki, viber, email
 * Все доступные сети: facebook, linkedin, twitter, email, whatsapp, telegram, viber, 
 * line, pinterest, tumblr, hackernews, reddit, vk, xing, buffer, instapaper, pocket,
 * mashable, mix, flipboard, weibo, blogger, baidu, douban, okru, mailru, evernote,
 * skype, delicious, sms, trello, messenger, odnoklassniki, meneame, diaspora,
 * googlebookmarks, qzone, refind, surfingbird, yahoomail, wordpress, amazon,
 * pinboard, threema, kakaostory, yummly
 */

if (!function_exists('codeweber_share_page')) {
   /**
    * Universal share function with regional support
    *
    * @param array $args {
    *     Optional. Array of arguments.
    *
    *     @type string $button_class    CSS class for the main button
    *     @type string $dropdown_class  CSS class for the dropdown menu
    *     @type string $item_class      CSS class for dropdown items
    *     @type string $button_text     Text for the share button
    *     @type string $button_icon     Icon class for the button
    *     @type array  $networks        Specific networks to show (overrides regional detection)
    *     @type string $region          Force region: 'eu', 'ru', or 'auto'
    *     @type string $title           Custom title for sharing
    *     @type string $url             Custom URL for sharing
    * }
    */
   function codeweber_share_page($args = [])
   {
      $defaults = [
         'button_class'   => 'btn btn-red btn-icon btn-icon-start dropdown-toggle mb-0 me-0 ' . getThemeButton(),
         'dropdown_class' => 'dropdown-menu',
         'item_class'     => 'dropdown-item',
         'button_text'    => __('Share', 'codeweber'),
         'button_icon'    => 'uil uil-share-alt',
         'networks'       => [],
         'region'         => 'auto',
         'title'          => get_the_title(),
         'url'            => get_permalink(),
      ];

      $args = wp_parse_args($args, $defaults);

      // Detect region if auto
      $region = $args['region'] === 'auto' ? codeweber_detect_region() : $args['region'];

      // Get networks for the region
      $networks = !empty($args['networks']) ? $args['networks'] : codeweber_get_share_networks($region);

      if (empty($networks)) {
         return;
      }

      // Prepare sharing data
      $share_title = esc_attr($args['title']);
      $share_url = esc_url($args['url']);
      $site_name = esc_attr(get_bloginfo('name'));
?>

      <div class="dropdown share-dropdown btn-group">
         <button class="<?php echo esc_attr($args['button_class']); ?>"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <i class="<?php echo esc_attr($args['button_icon']); ?>"></i>
            <?php echo esc_html($args['button_text']); ?>
         </button>

         <div class="<?php echo esc_attr($args['dropdown_class']); ?>">
            <?php foreach ($networks as $network => $network_data) : ?>
               <?php
               $icon_class = $network_data['icon'] ?? 'uil uil-share-alt';
               $label = $network_data['label'] ?? ucfirst($network);
               $data_attrs = '';

               // Prepare data attributes
               $data_attrs .= ' data-sharer="' . esc_attr($network) . '"';
               $data_attrs .= ' data-title="' . $share_title . '"';
               $data_attrs .= ' data-url="' . $share_url . '"';

               // Add network-specific attributes
               if (!empty($network_data['attributes'])) {
                  foreach ($network_data['attributes'] as $attr => $value) {
                     if ($value) {
                        $data_attrs .= ' data-' . esc_attr($attr) . '="' . esc_attr($value) . '"';
                     }
                  }
               }
               ?>

               <button class="<?php echo esc_attr($args['item_class']); ?> share-button"
                  <?php echo $data_attrs; ?>
                  type="button">
                  <i class="<?php echo esc_attr($icon_class); ?>"></i>
                  <?php echo esc_html($label); ?>
               </button>
            <?php endforeach; ?>
         </div>
      </div>

   <?php
   }
}

/**
 * Detect user region based on language/location
 */
function codeweber_detect_region()
{
   // Check if region is set in cookie/session
   if (isset($_COOKIE['user_region'])) {
      return sanitize_text_field($_COOKIE['user_region']);
   }

   // Detect by language
   $language = get_locale();
   if (strpos($language, 'ru') === 0) {
      return 'ru';
   }

   // Default to EU
   return 'eu';
}

/**
 * Get share networks based on region
 */
function codeweber_get_share_networks($region = 'eu')
{
   $networks = [
      'eu' => [
         'linkedin' => [
            'label' => __('LinkedIn', 'codeweber'),
            'icon' => 'uil uil-linkedin',
            'attributes' => []
         ],
         'twitter' => [
            'label' => __('Twitter', 'codeweber'),
            'icon' => 'uil uil-twitter',
            'attributes' => [
               'hashtags' => 'law,legal'
            ]
         ],
         'facebook' => [
            'label' => __('Facebook', 'codeweber'),
            'icon' => 'uil uil-facebook-f',
            'attributes' => []
         ],
         'whatsapp' => [
            'label' => __('WhatsApp', 'codeweber'),
            'icon' => 'uil uil-whatsapp',
            'attributes' => []
         ],
         'email' => [
            'label' => __('Email', 'codeweber'),
            'icon' => 'uil uil-envelope-share',
            'attributes' => [
               'subject' => sprintf(__('Посмотрите: %s', 'codeweber'), get_bloginfo('name')),
               'to' => get_option('admin_email') // ← И ЗДЕСЬ ТОЖЕ
            ]
            ],
         'xing' => [
            'label' => __('Xing', 'codeweber'),
            'icon' => 'uil uil-xing',
            'attributes' => []
         ]
      ],
      'ru' => [
         'vk' => [
            'label' => __('VKontakte', 'codeweber'),
            'icon' => 'uil uil-vk',
            'attributes' => [
               'hashtag' => 'law'
            ]
         ],
         'telegram' => [
            'label' => __('Telegram', 'codeweber'),
            'icon' => 'uil uil-telegram',
            'attributes' => []
         ],
         'whatsapp' => [
            'label' => __('WhatsApp', 'codeweber'),
            'icon' => 'uil uil-whatsapp',
            'attributes' => []
         ],
         'odnoklassniki' => [
            'label' => __('Odnoklassniki', 'codeweber'),
            'icon' => 'uil uil-odnoklassniki',
            'attributes' => []
         ],
         'viber' => [
            'label' => __('Viber', 'codeweber'),
            'icon' => 'uil uil-viber',
            'attributes' => []
         ],
         'email' => [
            'label' => __('Email', 'codeweber'),
            'icon' => 'uil uil-envelope-share',
            'attributes' => [
               'subject' => sprintf(__('Посмотрите: %s', 'codeweber'), get_bloginfo('name')),
               'to' => get_option('admin_email') // ← И ЗДЕСЬ ТОЖЕ
            ]
         ]
      ]
   ];

   // Allow filtering networks
   $networks = apply_filters('codeweber_share_networks', $networks, $region);

   return $networks[$region] ?? $networks['eu'];
}

/**
 * Shortcode for share buttons
 */
add_shortcode('share_buttons', 'codeweber_share_shortcode');
function codeweber_share_shortcode($atts)
{
   $atts = shortcode_atts([
      'region' => 'auto',
      'networks' => '',
      'class' => '',
      'title' => get_the_title(),
      'url' => get_permalink()
   ], $atts);

   if (!empty($atts['networks'])) {
      $atts['networks'] = array_map('trim', explode(',', $atts['networks']));
   }

   if (!empty($atts['class'])) {
      $atts['button_class'] .= ' ' . $atts['class'];
   }

   ob_start();
   codeweber_share_page($atts);
   return ob_get_clean();
}


if (! function_exists('page_share_js')) {
   function page_share_js()
   { ?>

      <script>
         /**
          * @preserve
          * Sharer.js
          *
          * @description Create your own social share buttons
          * @version 0.5.1
          * @author Ellison Leao <ellisonleao@gmail.com>
          * @license MIT
          *
          */

         (function(window, document) {
            'use strict';
            /**
             * @constructor
             */
            var Sharer = function(elem) {
               this.elem = elem;
            };

            /**
             *  @function init
             *  @description bind the events for multiple sharer elements
             *  @returns {Empty}
             */
            Sharer.init = function() {
               var elems = document.querySelectorAll('[data-sharer]'),
                  i,
                  l = elems.length;

               for (i = 0; i < l; i++) {
                  elems[i].addEventListener('click', Sharer.add);
               }
            };

            /**
             *  @function add
             *  @description bind the share event for a single dom element
             *  @returns {Empty}
             */
            Sharer.add = function(elem) {
               var target = elem.currentTarget || elem.srcElement;
               var sharer = new Sharer(target);
               sharer.share();
            };

            // instance methods
            Sharer.prototype = {
               constructor: Sharer,
               /**
                *  @function getValue
                *  @description Helper to get the attribute of a DOM element
                *  @param {String} attr DOM element attribute
                *  @returns {String|Empty} returns the attr value or empty string
                */
               getValue: function(attr) {
                  var val = this.elem.getAttribute('data-' + attr);
                  // handing facebook hashtag attribute
                  if (val && attr === 'hashtag') {
                     if (!val.startsWith('#')) {
                        val = '#' + val;
                     }
                  }
                  return val === null ? '' : val;
               },

               /**
                * @event share
                * @description Main share event. Will pop a window or redirect to a link
                * based on the data-sharer attribute.
                */
               share: function() {
                  var sharer = this.getValue('sharer').toLowerCase(),
                     sharers = {
                        facebook: {
                           shareUrl: 'https://www.facebook.com/sharer/sharer.php',
                           params: {
                              u: this.getValue('url'),
                              hashtag: this.getValue('hashtag'),
                              quote: this.getValue('quote'),
                           },
                        },
                        linkedin: {
                           shareUrl: 'https://www.linkedin.com/shareArticle',
                           params: {
                              url: this.getValue('url'),
                              mini: true,
                           },
                        },
                        twitter: {
                           shareUrl: 'https://twitter.com/intent/tweet/',
                           params: {
                              text: this.getValue('title'),
                              url: this.getValue('url'),
                              hashtags: this.getValue('hashtags'),
                              via: this.getValue('via'),
                              related: this.getValue('related'),
                              in_reply_to: this.getValue('in_reply_to')
                           },
                        },
                        email: {
                           shareUrl: 'mailto:' + this.getValue('to'),
                           params: {
                              subject: this.getValue('subject'),
                              body: this.getValue('title') + '\n' + this.getValue('url'),
                           },
                        },
                        whatsapp: {
                           shareUrl: this.getValue('web') === 'true' ? 'https://web.whatsapp.com/send' : 'https://wa.me/',
                           params: {
                              phone: this.getValue('to'),
                              text: this.getValue('title') + ' ' + this.getValue('url'),
                           },
                        },
                        telegram: {
                           shareUrl: 'https://t.me/share',
                           params: {
                              text: this.getValue('title'),
                              url: this.getValue('url'),
                           },
                        },
                        viber: {
                           shareUrl: 'viber://forward',
                           params: {
                              text: this.getValue('title') + ' ' + this.getValue('url'),
                           },
                        },
                        line: {
                           shareUrl: 'http://line.me/R/msg/text/?' + encodeURIComponent(this.getValue('title') + ' ' + this.getValue('url')),
                        },
                        pinterest: {
                           shareUrl: 'https://www.pinterest.com/pin/create/button/',
                           params: {
                              url: this.getValue('url'),
                              media: this.getValue('image'),
                              description: this.getValue('description'),
                           },
                        },
                        tumblr: {
                           shareUrl: 'http://tumblr.com/widgets/share/tool',
                           params: {
                              canonicalUrl: this.getValue('url'),
                              content: this.getValue('url'),
                              posttype: 'link',
                              title: this.getValue('title'),
                              caption: this.getValue('caption'),
                              tags: this.getValue('tags'),
                           },
                        },
                        hackernews: {
                           shareUrl: 'https://news.ycombinator.com/submitlink',
                           params: {
                              u: this.getValue('url'),
                              t: this.getValue('title'),
                           },
                        },
                        reddit: {
                           shareUrl: 'https://www.reddit.com/submit',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title')
                           },
                        },
                        vk: {
                           shareUrl: 'http://vk.com/share.php',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              description: this.getValue('caption'),
                              image: this.getValue('image'),
                           },
                        },
                        xing: {
                           shareUrl: 'https://www.xing.com/social/share/spi',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        buffer: {
                           shareUrl: 'https://buffer.com/add',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              via: this.getValue('via'),
                              picture: this.getValue('picture'),
                           },
                        },
                        instapaper: {
                           shareUrl: 'http://www.instapaper.com/edit',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              description: this.getValue('description'),
                           },
                        },
                        pocket: {
                           shareUrl: 'https://getpocket.com/save',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        mashable: {
                           shareUrl: 'https://mashable.com/submit',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        mix: {
                           shareUrl: 'https://mix.com/add',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        flipboard: {
                           shareUrl: 'https://share.flipboard.com/bookmarklet/popout',
                           params: {
                              v: 2,
                              title: this.getValue('title'),
                              url: this.getValue('url'),
                              t: Date.now(),
                           },
                        },
                        weibo: {
                           shareUrl: 'http://service.weibo.com/share/share.php',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              pic: this.getValue('image'),
                              appkey: this.getValue('appkey'),
                              ralateUid: this.getValue('ralateuid'),
                              language: 'zh_cn',
                           },
                        },
                        blogger: {
                           shareUrl: 'https://www.blogger.com/blog-this.g',
                           params: {
                              u: this.getValue('url'),
                              n: this.getValue('title'),
                              t: this.getValue('description'),
                           },
                        },
                        baidu: {
                           shareUrl: 'http://cang.baidu.com/do/add',
                           params: {
                              it: this.getValue('title'),
                              iu: this.getValue('url'),
                           },
                        },
                        douban: {
                           shareUrl: 'https://www.douban.com/share/service',
                           params: {
                              name: this.getValue('name'),
                              href: this.getValue('url'),
                              image: this.getValue('image'),
                              comment: this.getValue('description'),
                           },
                        },
                        okru: {
                           shareUrl: 'https://connect.ok.ru/dk',
                           params: {
                              'st.cmd': 'WidgetSharePreview',
                              'st.shareUrl': this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        mailru: {
                           shareUrl: 'http://connect.mail.ru/share',
                           params: {
                              share_url: this.getValue('url'),
                              linkname: this.getValue('title'),
                              linknote: this.getValue('description'),
                              type: 'page',
                           },
                        },
                        evernote: {
                           shareUrl: 'https://www.evernote.com/clip.action',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        skype: {
                           shareUrl: 'https://web.skype.com/share',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        delicious: {
                           shareUrl: 'https://del.icio.us/post',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        sms: {
                           shareUrl: 'sms://',
                           params: {
                              body: this.getValue('body'),
                           },
                        },
                        trello: {
                           shareUrl: 'https://trello.com/add-card',
                           params: {
                              url: this.getValue('url'),
                              name: this.getValue('title'),
                              desc: this.getValue('description'),
                              mode: 'popup',
                           },
                        },
                        messenger: {
                           shareUrl: 'fb-messenger://share',
                           params: {
                              link: this.getValue('url'),
                           },
                        },
                        odnoklassniki: {
                           shareUrl: 'https://connect.ok.ru/dk',
                           params: {
                              st: {
                                 cmd: 'WidgetSharePreview',
                                 deprecated: 1,
                                 shareUrl: this.getValue('url'),
                              },
                           },
                        },
                        meneame: {
                           shareUrl: 'https://www.meneame.net/submit',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        diaspora: {
                           shareUrl: 'https://share.diasporafoundation.org',
                           params: {
                              title: this.getValue('title'),
                              url: this.getValue('url'),
                           },
                        },
                        googlebookmarks: {
                           shareUrl: 'https://www.google.com/bookmarks/mark',
                           params: {
                              op: 'edit',
                              bkmk: this.getValue('url'),
                              title: this.getValue('title'),
                           },
                        },
                        qzone: {
                           shareUrl: 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        refind: {
                           shareUrl: 'https://refind.com',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        surfingbird: {
                           shareUrl: 'https://surfingbird.ru/share',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              description: this.getValue('description'),
                           },
                        },
                        yahoomail: {
                           shareUrl: 'http://compose.mail.yahoo.com',
                           params: {
                              to: this.getValue('to'),
                              subject: this.getValue('subject'),
                              body: this.getValue('body'),
                           },
                        },
                        wordpress: {
                           shareUrl: 'https://wordpress.com/wp-admin/press-this.php',
                           params: {
                              u: this.getValue('url'),
                              t: this.getValue('title'),
                              s: this.getValue('title'),
                           },
                        },
                        amazon: {
                           shareUrl: 'https://www.amazon.com/gp/wishlist/static-add',
                           params: {
                              u: this.getValue('url'),
                              t: this.getValue('title'),
                           },
                        },
                        pinboard: {
                           shareUrl: 'https://pinboard.in/add',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              description: this.getValue('description'),
                           },
                        },
                        threema: {
                           shareUrl: 'threema://compose',
                           params: {
                              text: this.getValue('text'),
                              id: this.getValue('id'),
                           },
                        },
                        kakaostory: {
                           shareUrl: 'https://story.kakao.com/share',
                           params: {
                              url: this.getValue('url'),
                           },
                        },
                        yummly: {
                           shareUrl: 'http://www.yummly.com/urb/verify',
                           params: {
                              url: this.getValue('url'),
                              title: this.getValue('title'),
                              yumtype: 'button',
                           },
                        },
                     },
                     s = sharers[sharer];

                  // custom popups sizes
                  if (s) {
                     s.width = this.getValue('width');
                     s.height = this.getValue('height');
                  }
                  return s !== undefined ? this.urlSharer(s) : false;
               },
               /**
                * @event urlSharer
                * @param {Object} sharer
                */
               urlSharer: function(sharer) {
                  var p = sharer.params || {},
                     keys = Object.keys(p),
                     i,
                     str = keys.length > 0 ? '?' : '';
                  for (i = 0; i < keys.length; i++) {
                     if (str !== '?') {
                        str += '&';
                     }
                     if (p[keys[i]]) {
                        str += keys[i] + '=' + encodeURIComponent(p[keys[i]]);
                     }
                  }
                  sharer.shareUrl += str;

                  var isLink = this.getValue('link') === 'true';
                  var isBlank = this.getValue('blank') === 'true';

                  if (isLink) {
                     if (isBlank) {
                        window.open(sharer.shareUrl, '_blank');
                     } else {
                        window.location.href = sharer.shareUrl;
                     }
                  } else {
                     console.log(sharer.shareUrl);
                     // defaults to popup if no data-link is provided
                     var popWidth = sharer.width || 600,
                        popHeight = sharer.height || 480,
                        left = window.innerWidth / 2 - popWidth / 2 + window.screenX,
                        top = window.innerHeight / 2 - popHeight / 2 + window.screenY,
                        popParams = 'scrollbars=no, width=' + popWidth + ', height=' + popHeight + ', top=' + top + ', left=' + left,
                        newWindow = window.open(sharer.shareUrl, '', popParams);

                     if (window.focus) {
                        newWindow.focus();
                     }
                  }
               },
            };

            // adding sharer events on domcontentload
            if (document.readyState === 'complete' || document.readyState !== 'loading') {
               Sharer.init();
            } else {
               document.addEventListener('DOMContentLoaded', Sharer.init);
            }

            // exporting sharer for external usage
            window.Sharer = Sharer;
         })(window, document);
      </script>
<?php
   }
}
// Add hook for admin <head></head>
add_action('wp_head', 'page_share_js');
