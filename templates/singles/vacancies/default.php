<?php
/**
 * Template: Single Vacancy Default
 * 
 * Template for displaying vacancy page
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

$vacancy_data = get_vacancy_data_[];

// Получаем ID записи для функции vacancy_social_links
$vacancy_post_id = get_the_ID();

// Получаем тип соцсетей из Redux
global $opt_name;
if (empty($opt_name)) {
	$opt_name = 'redux_demo';
}
// Глобальные настройки иконок (Стилизация Темы → Codeweber)
$social_icon_type = Redux::get_option($opt_name, 'global-social-icon-type', Redux::get_option($opt_name, 'social-icon-type', '1'));
$social_type = 'type' . ($social_icon_type ? $social_icon_type : '1');
$social_button_style = Redux::get_option($opt_name, 'global-social-button-style', 'circle');
$social_size = Redux::get_option($opt_name, 'global-social-button-size', 'md');
?>
<div class="blog single">
   <div class="classic-view">
      <article class="post">
         <div class="post-content mb-5">
            <?php if ($vacancy_data) : ?>

               <div class="mb-6">
                  <h3 class="mb-3"><?php esc_html_e('About the vacancy', 'codeweber'); ?></h3>
                  <div class="">
                     <span class="h6 mb-1"><?php esc_html_e('Position:', 'codeweber'); ?> </span>
                     <span><?php echo esc_html(get_the_title()); ?></span>
                  </div>
                  <?php if (!empty($vacancy_data['company'])) : ?>
                  <div class="align-self-start">
                     <span class="h6 mb-1"><?php esc_html_e('Company:', 'codeweber'); ?> </span>
                     <span><?php echo esc_html($vacancy_data['company']); ?></span>
                  </div>
                  <?php endif; ?>
                  <?php
                  $schedule_term = !empty($vacancy_data['vacancy_schedules']) && !is_wp_error($vacancy_data['vacancy_schedules']) ? $vacancy_data['vacancy_schedules'][0] : null;
                  if ($schedule_term) : ?>
                  <div class="align-self-start">
                     <span class="h6 mb-1"><?php esc_html_e('Work schedule:', 'codeweber'); ?> </span>
                     <span><?php echo esc_html($schedule_term->name); ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($vacancy_data['languages']) && is_array($vacancy_data['languages'])) : ?>
                  <div class="align-self-start">
                     <span class="h6 mb-1"><?php esc_html_e('Languages:', 'codeweber'); ?> </span>
                     <span><?php echo esc_html(implode(', ', $vacancy_data['languages'])); ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($vacancy_data['status'])) : ?>
                  <div class="align-self-start">
                     <span class="h6 mb-1"><?php esc_html_e('Status:', 'codeweber'); ?> </span>
                     <span><?php
                        $status_labels = array(
                           'open'   => __('Open', 'codeweber'),
                           'closed' => __('Closed', 'codeweber'),
                        );
                        echo esc_html(isset($status_labels[ $vacancy_data['status'] ]) ? $status_labels[ $vacancy_data['status'] ] : $vacancy_data['status']);
                     ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($vacancy_data['salary'])) : ?>
                  <div class="align-self-start">
                     <span class="h6 mb-1"><?php esc_html_e('Salary:', 'codeweber'); ?> </span>
                     <span><?php echo esc_html($vacancy_data['salary']); ?></span>
                  </div>
                  <?php endif; ?>
               </div>

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

               <?php if (!empty($vacancy_data['skills']) && is_array($vacancy_data['skills'])) : ?>
                  <div class="mb-6">
                     <h3 class="mb-3"><?php _e('Skills', 'codeweber'); ?></h3>
                     <ul class="unordered-list bullet-primary cc-2 pb-lg-1">
                        <?php foreach ($vacancy_data['skills'] as $skill) : ?>
                           <li><?php echo esc_html($skill); ?></li>
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
                  <?php echo vacancy_social_links($vacancy_post_id, '', $social_type, $social_size, 'primary', 'solid', $social_button_style); ?>
               </div>
            <?php endif; ?>
            <div class="mb-0 mb-md-2">
               <?php 
               $share_button_class = 'btn btn-red btn-sm btn-icon btn-icon-start dropdown-toggle mb-0 me-0 has-ripple';
               if (class_exists('Codeweber_Options')) {
                  $share_button_class .= Codeweber_Options::style('button');
               }
               codeweber_share_page(['region' => 'auto', 'button_class' => $share_button_class]); 
               ?>
            </div>
         </div>
         <!-- /.post-footer -->
      </article>
      <!-- /.post -->
   </div>
   <!-- /.classic-view -->
</div>
