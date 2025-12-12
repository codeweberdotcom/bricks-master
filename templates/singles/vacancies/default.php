<?php
/**
 * Template: Single Vacancy Default
 * 
 * Шаблон для отображения страницы вакансии
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

$vacancy_data = get_vacancy_data_array();

// Получаем ID записи для функции vacancy_social_links
$vacancy_post_id = get_the_ID();

// Получаем тип соцсетей из Redux
global $opt_name;
if (empty($opt_name)) {
	$opt_name = 'redux_demo';
}
$social_icon_type = Redux::get_option($opt_name, 'social-icon-type');
$social_type = 'type' . ($social_icon_type ? $social_icon_type : '1'); // По умолчанию type1
?>
<div class="blog single">
   <div class="classic-view">
      <article class="post">
         <div class="post-content mb-5">
            <?php if ($vacancy_data) : ?>

               <?php if (!empty($vacancy_data['introduction'])) : ?>
                  <div class="mb-6"><?php echo wp_kses_post($vacancy_data['introduction']); ?></div>
               <?php endif; ?>

               <?php if (!empty($vacancy_data['requirements']) && is_array($vacancy_data['requirements'])) : ?>
                  <div class="mb-6">
                     <h3 class="mb-3"><?php _e('Requirements', 'codeweber'); ?></h3>
                     <ul class="unordered-list bullet-primary">
                        <?php foreach ($vacancy_data['requirements'] as $requirement) : ?>
                           <li><?php echo esc_html($requirement); ?></li>
                        <?php endforeach; ?>
                     </ul>
                  </div>
               <?php endif; ?>

               <?php if (!empty($vacancy_data['responsibilities']) && is_array($vacancy_data['responsibilities'])) : ?>
                  <div class="mb-6">
                     <h3 class="mb-3"><?php _e('Responsibilities', 'codeweber'); ?></h3>
                     <ul class="unordered-list bullet-primary">
                        <?php foreach ($vacancy_data['responsibilities'] as $responsibility) : ?>
                           <li><?php echo esc_html($responsibility); ?></li>
                        <?php endforeach; ?>
                     </ul>
                  </div>
               <?php endif; ?>

               <?php if (!empty($vacancy_data['additional_info'])) : ?>
                  <div class="mb-6"><?php echo wp_kses_post($vacancy_data['additional_info']); ?></div>
               <?php endif; ?>

            <?php endif; ?>
         </div>
         <!-- /.post-content -->

         <div class="post-footer d-md-flex flex-md-row justify-content-md-between align-items-center mt-8">
            <?php if ($vacancy_data && (function_exists('vacancy_social_links'))) : ?>
               <div class="d-flex">
                  <?php echo vacancy_social_links($vacancy_post_id, '', $social_type, 'sm'); ?>
               </div>
            <?php endif; ?>
            
            <div class="mb-0 mb-md-2">
               <?php 
               $share_button_class = 'btn btn-red btn-sm btn-icon btn-icon-start dropdown-toggle mb-0 me-0 has-ripple';
               if (function_exists('getThemeButton')) {
                  $share_button_class .= getThemeButton();
               }
               codeweber_share_page(['region' => 'eu', 'button_class' => $share_button_class]); 
               ?>
            </div>
         </div>
         <!-- /.post-footer -->
      </article>
      <!-- /.post -->
   </div>
   <!-- /.classic-view -->
</div>

