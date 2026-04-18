<?php
/**
 * Template: Vacancy Avatar Card
 *
 * Компактная горизонтальная карточка: цветной аватар с инициалами,
 * значок типа вакансии, название, локация. Вся карточка — ссылка.
 *
 * Ожидается вызов внутри WP loop (get_the_ID() вернёт ID вакансии).
 * Можно передать $template_args['avatar_index'] для детерминированного
 * выбора цвета аватара (по позиции в loop), иначе берётся хэш от ID.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$post_id = absint( get_the_ID() );
if ( ! $post_id ) {
	return;
}

$vacancy_data      = function_exists( 'get_vacancy_data_array' ) ? get_vacancy_data_array( $post_id ) : [];
$title             = get_the_title( $post_id );
$link              = get_permalink( $post_id );
$location          = ! empty( $vacancy_data['location'] ) ? $vacancy_data['location'] : '';
$vacancy_types     = ! empty( $vacancy_data['vacancy_types'] ) && ! is_wp_error( $vacancy_data['vacancy_types'] )
	? $vacancy_data['vacancy_types'] : [];
$category_name     = ! empty( $vacancy_types ) ? $vacancy_types[0]->name : '';

// ── Инициалы (первые буквы первых двух слов) ─────────────────────────────────
$words    = preg_split( '/\s+/u', trim( $title ) );
$initials = '';
if ( ! empty( $words[0] ) ) {
	$initials .= mb_substr( $words[0], 0, 1 );
}
if ( ! empty( $words[1] ) ) {
	$initials .= mb_substr( $words[1], 0, 1 );
}
$initials = mb_strtoupper( $initials );

// ── Цвет аватара — детерминированный, ротация по позиции/ID ──────────────────
$avatar_colors = [ 'red', 'green', 'yellow', 'purple', 'orange', 'pink', 'blue', 'aqua', 'violet' ];
$avatar_index  = isset( $template_args['avatar_index'] ) ? (int) $template_args['avatar_index'] : $post_id;
$avatar_color  = $avatar_colors[ $avatar_index % count( $avatar_colors ) ];

// ── Цвет badge — производный от названия типа вакансии (fallback blue) ────────
$badge_color_map = [
	'full time' => 'blue',
	'part time' => 'violet',
	'remote'    => 'aqua',
	'contract'  => 'orange',
	'internship' => 'green',
];
$badge_key   = mb_strtolower( $category_name );
$badge_color = $badge_color_map[ $badge_key ] ?? 'blue';

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
?>
<a href="<?php echo esc_url( $link ); ?>" class="card shadow-lg lift h-100 text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
	<div class="card-body p-5 d-flex flex-row">
		<div>
			<span class="avatar bg-<?php echo esc_attr( $avatar_color ); ?> text-white w-11 h-11 fs-20 me-4"><?php echo esc_html( $initials ); ?></span>
		</div>
		<div>
			<?php if ( $category_name ) : ?>
				<span class="badge bg-pale-<?php echo esc_attr( $badge_color ); ?> text-<?php echo esc_attr( $badge_color ); ?> rounded py-1 mb-2"><?php echo esc_html( $category_name ); ?></span>
			<?php endif; ?>
			<h4 class="mb-1"><?php echo esc_html( $title ); ?></h4>
			<?php if ( $location ) : ?>
				<p class="mb-0 text-body"><?php echo esc_html( $location ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</a>
