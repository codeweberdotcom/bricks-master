<?php
/**
 * Demo данные для CPT Events (Мероприятия)
 *
 * Создаёт 50 тестовых мероприятий с разными датами и статусами.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Возвращает массив с данными для 50 демо-мероприятий.
 * Даты рассчитаны относительно 2026-03 (актуальная дата разработки).
 *
 * @return array[]
 */
function cw_demo_get_events_data() {
	// Изображения из src/assets/img/photos/ (about*.jpg, 35 штук, цикл)
	$photos = [
		'about2.jpg','about3.jpg','about4.jpg','about5.jpg','about6.jpg',
		'about7.jpg','about8.jpg','about9.jpg','about10.jpg','about11.jpg',
		'about12.jpg','about13.jpg','about14.jpg','about15.jpg','about16.jpg',
		'about17.jpg','about18.jpg','about19.jpg','about20.jpg','about21.jpg',
		'about22.jpg','about23.jpg','about24.jpg','about25.jpg','about27.jpg',
		'about28.jpg','about29.jpg','about30.jpg','about31.jpg','about32.jpg',
		'about33.jpg','about34.jpg','about35.jpg','about36.jpg','about37.jpg',
	];
	$total_photos = count( $photos );

	$items = [];
	$idx   = 0;

	// ── 1. ПРОШЕДШИЕ события (event_ended) ─────────────────────────────────
	// 15 событий в 2025 году, date_end < today (2026-03-23)
	$past = [
		[ 'Международная конференция по цифровым технологиям', '2025-01-20', '2025-01-22', '2024-11-01', '2025-01-10', 'Москва', 'ул. Тверская, 12', 'DigitalConf RU', 5000, 200, 'Конференция', 'Оффлайн', 'От 5 000 ₽' ],
		[ 'Семинар по управлению проектами', '2025-02-14', '2025-02-15', '2024-12-01', '2025-02-01', 'Санкт-Петербург', 'пр. Невский, 48', 'PM Academy', 3000, 50, 'Семинар', 'Оффлайн', 'От 3 000 ₽' ],
		[ 'Вебинар: основы маркетинга', '2025-03-10', '2025-03-10', '2025-01-01', '2025-03-08', 'Онлайн', '', 'MarketingPro', 500, 300, 'Вебинар', 'Онлайн', 'Бесплатно' ],
		[ 'Форум предпринимателей 2025', '2025-04-05', '2025-04-07', '2025-01-15', '2025-03-25', 'Москва', 'Экспоцентр, павильон 7', 'Бизнес Форум', 8000, 500, 'Форум', 'Оффлайн', 'От 8 000 ₽' ],
		[ 'Мастер-класс по брендингу', '2025-04-25', '2025-04-25', '2025-03-01', '2025-04-20', 'Казань', 'ул. Баумана, 5', 'Branding Lab', 4500, 30, 'Мастер-класс', 'Оффлайн', '4 500 ₽' ],
		[ 'IT-конференция DevFest Spring', '2025-05-16', '2025-05-17', '2025-03-01', '2025-05-10', 'Новосибирск', 'Технопарк', 'DevFest', 6000, 400, 'Конференция', 'Гибридный', 'От 6 000 ₽' ],
		[ 'Воркшоп по дизайн-мышлению', '2025-06-07', '2025-06-08', '2025-04-01', '2025-05-30', 'Москва', 'Artplay, корп. 6', 'Design Hub', 7000, 20, 'Мастер-класс', 'Оффлайн', '7 000 ₽' ],
		[ 'Онлайн-марафон по программированию', '2025-07-01', '2025-07-05', '2025-05-01', '2025-06-25', 'Онлайн', '', 'CodeBase', 0, 0, 'Вебинар', 'Онлайн', 'Бесплатно' ],
		[ 'HR-конференция 2025', '2025-08-21', '2025-08-22', '2025-06-01', '2025-08-15', 'Сочи', 'Сочи-Парк Отель', 'HR Club', 9000, 250, 'Конференция', 'Оффлайн', 'От 9 000 ₽' ],
		[ 'Семинар по финансовой грамотности', '2025-09-11', '2025-09-11', '2025-07-01', '2025-09-05', 'Екатеринбург', 'ул. Ленина, 24', 'FinSkills', 1500, 80, 'Семинар', 'Оффлайн', '1 500 ₽' ],
		[ 'Выставка инновационных технологий', '2025-09-25', '2025-09-27', '2025-07-15', '2025-09-20', 'Москва', 'ВДНХ, павильон 75', 'InnoTech', 0, 1000, 'Форум', 'Оффлайн', 'Бесплатно' ],
		[ 'Конференция по кибербезопасности', '2025-10-15', '2025-10-16', '2025-08-01', '2025-10-10', 'Москва', 'Digital October', 'SecureNet', 12000, 300, 'Конференция', 'Гибридный', 'От 12 000 ₽' ],
		[ 'Мастер-класс по Python', '2025-11-08', '2025-11-08', '2025-09-01', '2025-11-01', 'Санкт-Петербург', 'Точка кипения СПб', 'PythonRU', 3500, 25, 'Мастер-класс', 'Оффлайн', '3 500 ₽' ],
		[ 'Форум стартапов StartupFest 2025', '2025-11-28', '2025-11-29', '2025-09-15', '2025-11-20', 'Москва', 'Сколково, Гиперкуб', 'StartupFest', 5000, 350, 'Форум', 'Гибридный', 'От 5 000 ₽' ],
		[ 'Новогодний вебинар: итоги года', '2025-12-26', '2025-12-26', '2025-11-01', '2025-12-24', 'Онлайн', '', 'DigitalConf RU', 0, 500, 'Вебинар', 'Онлайн', 'Бесплатно' ],
	];

	foreach ( $past as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	// ── 2. ПРОШЕДШИЕ события в нач. 2026 (event_ended) ─────────────────────
	// 5 событий: date_start до 2026-03-23
	$past_2026 = [
		[ 'Конференция Product Managers 2026', '2026-01-17', '2026-01-18', '2025-11-01', '2026-01-10', 'Москва', 'Москва-Сити, башня А', 'ProductConf', 7500, 180, 'Конференция', 'Гибридный', 'От 7 500 ₽' ],
		[ 'Семинар: лидерство в бизнесе', '2026-02-06', '2026-02-06', '2025-12-01', '2026-01-30', 'Санкт-Петербург', 'Lotte Hotel', 'Business Leaders', 5500, 60, 'Семинар', 'Оффлайн', '5 500 ₽' ],
		[ 'Вебинар по UX-исследованиям', '2026-02-19', '2026-02-19', '2026-01-01', '2026-02-17', 'Онлайн', '', 'UX Lab', 0, 200, 'Вебинар', 'Онлайн', 'Бесплатно' ],
		[ 'Мастер-класс по фотографии', '2026-03-07', '2026-03-08', '2026-01-15', '2026-03-01', 'Москва', 'ул. Малая Ордынка, 21', 'PhotoSchool', 9000, 15, 'Мастер-класс', 'Оффлайн', '9 000 ₽' ],
		[ 'HR Tech Forum 2026', '2026-03-19', '2026-03-20', '2026-01-01', '2026-03-12', 'Москва', 'Экспоцентр', 'HR Tech', 8000, 400, 'Форум', 'Оффлайн', 'От 8 000 ₽' ],
	];

	foreach ( $past_2026 as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	// ── 3. ЗАПИСЬ ЕЩЁНЕ ОТКРЫЛАСЬ (not_open_yet) ───────────────────────────
	// 5 событий: date_start 2026-07+, reg_open 2026-05+
	$not_open = [
		[ 'Конференция AI Summit 2026', '2026-07-10', '2026-07-11', '2026-05-01', '2026-06-30', 'Москва', 'Экспоцентр, зал А', 'AI Summit', 15000, 300, 'Конференция', 'Гибридный', 'От 15 000 ₽' ],
		[ 'Летний форум разработчиков', '2026-07-24', '2026-07-26', '2026-05-15', '2026-07-15', 'Сочи', 'Radisson Blu Resort', 'DevForum', 20000, 250, 'Форум', 'Оффлайн', 'От 20 000 ₽' ],
		[ 'Семинар по ESG-стратегиям', '2026-08-13', '2026-08-14', '2026-06-01', '2026-08-01', 'Санкт-Петербург', 'Отель Астория', 'GreenBiz', 10000, 80, 'Семинар', 'Оффлайн', '10 000 ₽' ],
		[ 'Онлайн-фестиваль цифрового искусства', '2026-09-05', '2026-09-07', '2026-07-01', '2026-08-31', 'Онлайн', '', 'DigitalArt Fest', 0, 0, 'Форум', 'Онлайн', 'Бесплатно' ],
		[ 'Конгресс по инновациям в образовании', '2026-09-18', '2026-09-19', '2026-07-01', '2026-09-10', 'Казань', 'КЦ «Корстон»', 'EduInno', 6000, 200, 'Конференция', 'Гибридный', 'От 6 000 ₽' ],
	];

	foreach ( $not_open as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	// ── 4. ЗАПИСЬ ЗАКРЫТА (registration_closed) ─────────────────────────────
	// 8 событий: date_start 2026-04+, reg_close до 2026-03-22
	$reg_closed = [
		[ 'Весенний SEO-интенсив', '2026-04-04', '2026-04-05', '2026-01-15', '2026-03-20', 'Москва', 'Учебный центр Яндекса', 'SEO School', 9500, 40, 'Семинар', 'Оффлайн', '9 500 ₽' ],
		[ 'Конференция eCommerce Russia', '2026-04-11', '2026-04-12', '2026-01-10', '2026-03-15', 'Москва', 'Radisson Slavyanskaya', 'eCommerce Conf', 14000, 350, 'Конференция', 'Гибридный', 'От 14 000 ₽' ],
		[ 'Воркшоп по видеомаркетингу', '2026-04-18', '2026-04-18', '2026-02-01', '2026-03-10', 'Санкт-Петербург', 'Скорая творческая помощь', 'Video Lab', 8000, 20, 'Мастер-класс', 'Оффлайн', '8 000 ₽' ],
		[ 'Круглый стол по блокчейну', '2026-04-24', '2026-04-24', '2026-01-20', '2026-03-22', 'Москва', 'Иннополис Москва', 'BlockchainHub', 5000, 60, 'Форум', 'Оффлайн', '5 000 ₽' ],
		[ 'Мастер-класс по переговорам', '2026-05-07', '2026-05-07', '2026-01-01', '2026-03-01', 'Нижний Новгород', 'Отель Ibis', 'NegotiationPro', 6500, 25, 'Мастер-класс', 'Оффлайн', '6 500 ₽' ],
		[ 'Tech Leadership Summit', '2026-05-14', '2026-05-15', '2025-12-01', '2026-03-05', 'Москва', 'Сколково', 'TechLeaders', 18000, 200, 'Конференция', 'Оффлайн', 'От 18 000 ₽' ],
		[ 'Онлайн-курс по Data Science', '2026-05-20', '2026-05-27', '2026-01-01', '2026-03-10', 'Онлайн', '', 'Data Academy', 12000, 100, 'Семинар', 'Онлайн', '12 000 ₽' ],
		[ 'Форум женщин в IT', '2026-06-06', '2026-06-07', '2026-01-01', '2026-03-15', 'Москва', 'Точка кипения', 'WomenIT', 0, 150, 'Форум', 'Гибридный', 'Бесплатно' ],
	];

	foreach ( $reg_closed as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	// ── 5. ЗАПИСЬ ОТКРЫТА (open) ─────────────────────────────────────────────
	// 12 событий: date_start 2026-04+, reg_open уже прошёл, reg_close в будущем
	$reg_open = [
		[ 'Конференция по маркетингу Spring 2026', '2026-04-10', '2026-04-11', '2026-01-01', '2026-04-05', 'Москва', 'Центр международной торговли', 'MarketConf', 10000, 250, 'Конференция', 'Оффлайн', 'От 10 000 ₽' ],
		[ 'Вебинар: социальные сети для бизнеса', '2026-04-15', '2026-04-15', '2026-02-01', '2026-04-12', 'Онлайн', '', 'SMM Agency', 0, 500, 'Вебинар', 'Онлайн', 'Бесплатно' ],
		[ 'Мастер-класс по созданию стартапа', '2026-04-25', '2026-04-26', '2026-02-15', '2026-04-20', 'Москва', 'Hub of Intelligence, ул. Берсеневская', 'StartupSchool', 11000, 30, 'Мастер-класс', 'Оффлайн', '11 000 ₽' ],
		[ 'Форум «Цифровая трансформация»', '2026-05-06', '2026-05-07', '2026-02-01', '2026-04-30', 'Москва', 'Президент-Отель', 'DigiTrans', 13000, 400, 'Форум', 'Гибридный', 'От 13 000 ₽' ],
		[ 'Семинар по UX/UI-дизайну', '2026-05-12', '2026-05-13', '2026-02-01', '2026-05-05', 'Санкт-Петербург', 'Студия ODESL', 'UX School', 7500, 20, 'Семинар', 'Оффлайн', '7 500 ₽' ],
		[ 'Конференция DevOps Days 2026', '2026-05-22', '2026-05-23', '2026-03-01', '2026-05-15', 'Москва', 'Технопарк Сколково', 'DevOps RU', 8500, 300, 'Конференция', 'Гибридный', 'От 8 500 ₽' ],
		[ 'Онлайн-семинар по инвестициям', '2026-06-03', '2026-06-03', '2026-02-20', '2026-05-30', 'Онлайн', '', 'InvestClub', 3000, 200, 'Семинар', 'Онлайн', '3 000 ₽' ],
		[ 'Мастер-класс по публичным выступлениям', '2026-06-13', '2026-06-14', '2026-03-01', '2026-06-07', 'Москва', 'Деловой центр «Москва»', 'SpeakPro', 12000, 15, 'Мастер-класс', 'Оффлайн', '12 000 ₽' ],
		[ 'Конгресс по искусственному интеллекту', '2026-06-19', '2026-06-21', '2026-02-01', '2026-06-10', 'Москва', 'Крокус Экспо', 'AI Congress', 20000, 1000, 'Конференция', 'Гибридный', 'От 20 000 ₽' ],
		[ 'Воркшоп по Agile', '2026-07-04', '2026-07-04', '2026-02-01', '2026-06-28', 'Санкт-Петербург', 'Офис Luxoft', 'Agile RU', 5000, 30, 'Мастер-класс', 'Оффлайн', '5 000 ₽' ],
		[ 'Вебинар: автоматизация бизнеса', '2026-07-09', '2026-07-09', '2026-03-01', '2026-07-07', 'Онлайн', '', 'BizAuto', 2500, 300, 'Вебинар', 'Онлайн', '2 500 ₽' ],
		[ 'Летняя школа предпринимателей', '2026-07-20', '2026-07-24', '2026-03-01', '2026-07-10', 'Сочи', 'Sirius, бульвар Надежды', 'BizSchool', 25000, 50, 'Форум', 'Оффлайн', '25 000 ₽' ],
	];

	foreach ( $reg_open as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	// ── 6. БЕЗ ДЫТ РЕГИСТРАЦИИ (открытая запись по умолчанию) ───────────────
	// 5 событий: есть дата мероприятия, нет дат регистрации
	$no_reg_dates = [
		[ 'Форум технологий RusTech 2026', '2026-10-08', '2026-10-10', '', '', 'Москва', 'Экспоцентр', 'RusTech', 6500, 600, 'Форум', 'Оффлайн', 'От 6 500 ₽' ],
		[ 'Конференция по облачным технологиям', '2026-10-22', '2026-10-23', '', '', 'Санкт-Петербург', 'EXPOFORUM', 'CloudConf', 11000, 350, 'Конференция', 'Гибридный', 'От 11 000 ₽' ],
		[ 'Новогодний IT-марафон', '2026-12-05', '2026-12-06', '', '', 'Онлайн', '', 'ITMarathon', 0, 0, 'Вебинар', 'Онлайн', 'Бесплатно' ],
		[ 'Мастер-класс: будущее рекламы', '2026-11-14', '2026-11-14', '', '', 'Москва', 'Artplay', 'AdFuture', 7000, 40, 'Мастер-класс', 'Оффлайн', '7 000 ₽' ],
		[ 'Семинар по стратегическому управлению', '2026-11-27', '2026-11-28', '', '', 'Екатеринбург', 'Бизнес-центр «Высоцкий»', 'Strategy Lab', 9000, 70, 'Семинар', 'Оффлайн', '9 000 ₽' ],
	];

	foreach ( $no_reg_dates as $e ) {
		$items[] = [
			'title'      => $e[0],
			'date_start' => $e[1],
			'date_end'   => $e[2],
			'reg_open'   => $e[3],
			'reg_close'  => $e[4],
			'location'   => $e[5],
			'address'    => $e[6],
			'organizer'  => $e[7],
			'price'      => $e[12],
			'seats'      => $e[8],
			'category'   => $e[10],
			'format'     => $e[11],
			'image'      => $photos[ $idx % $total_photos ],
		];
		$idx++;
	}

	return $items;
}

/**
 * Импортирует изображение из src/assets/img/photos/ в медиабиблиотеку.
 *
 * @param string $filename Имя файла.
 * @param int    $post_id  ID записи.
 * @return int|false
 */
function cw_demo_import_event_image( $filename, $post_id ) {
	$source_path = get_template_directory() . '/src/assets/img/photos/' . $filename;

	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	$file_type = wp_check_filetype( basename( $source_path ), null );
	if ( ! $file_type['type'] ) {
		return false;
	}

	$upload_dir = wp_upload_dir();
	$file_name  = 'event-demo-' . $filename;
	$file_path  = $upload_dir['path'] . '/' . $file_name;

	if ( ! copy( $source_path, $file_path ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		[ 'name' => $file_name, 'tmp_name' => $file_path ],
		$post_id
	);

	if ( file_exists( $file_path ) ) {
		@unlink( $file_path );
	}

	if ( is_wp_error( $attachment_id ) ) {
		return false;
	}

	wp_update_post( [ 'ID' => $attachment_id, 'post_parent' => $post_id ] );
	set_post_thumbnail( $post_id, $attachment_id );
	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) )
	);

	return $attachment_id;
}

/**
 * Возвращает ID термина таксономии, создавая его при необходимости.
 *
 * @param string $name     Название.
 * @param string $taxonomy Таксономия.
 * @return int|false
 */
function cw_demo_get_or_create_event_term( $name, $taxonomy ) {
	if ( empty( $name ) ) {
		return false;
	}
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term ) {
		return $term->term_id;
	}
	$result = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $result ) ) {
		return false;
	}
	return $result['term_id'];
}

/**
 * Создаёт 50 demo мероприятий.
 *
 * @return array{success:bool,message:string,created:int,total:int,errors:string[]}
 */
function cw_demo_create_events() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$items   = cw_demo_get_events_data();
	$created = 0;
	$errors  = [];

	foreach ( $items as $e ) {
		$post_id = wp_insert_post( [
			'post_type'    => 'events',
			'post_title'   => sanitize_text_field( $e['title'] ),
			'post_content' => sprintf(
				'<p>%s — это возможность познакомиться с последними тенденциями, обменяться опытом с коллегами и завязать полезные профессиональные контакты.</p><p>В программе: доклады ведущих экспертов, практические воркшопы, нетворкинг-сессии. Количество мест ограничено — регистрируйтесь заранее.</p>',
				esc_html( $e['title'] )
			),
			'post_excerpt' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
		], true );

		if ( is_wp_error( $post_id ) ) {
			$errors[] = sprintf( 'Ошибка создания «%s»: %s', $e['title'], $post_id->get_error_message() );
			continue;
		}

		// Мета-поля
		update_post_meta( $post_id, '_event_date_start', $e['date_start'] );
		update_post_meta( $post_id, '_event_date_end',   $e['date_end'] );
		if ( $e['reg_open'] ) {
			update_post_meta( $post_id, '_event_reg_open', $e['reg_open'] );
		}
		if ( $e['reg_close'] ) {
			update_post_meta( $post_id, '_event_reg_close', $e['reg_close'] );
		}
		update_post_meta( $post_id, '_event_location',  sanitize_text_field( $e['location'] ) );
		update_post_meta( $post_id, '_event_address',   sanitize_text_field( $e['address'] ) );
		update_post_meta( $post_id, '_event_organizer', sanitize_text_field( $e['organizer'] ) );
		update_post_meta( $post_id, '_event_price',     sanitize_text_field( $e['price'] ) );
		if ( $e['seats'] > 0 ) {
			update_post_meta( $post_id, '_event_seats', absint( $e['seats'] ) );
		}

		// Флаг demo-записи для последующего удаления
		update_post_meta( $post_id, '_cw_demo_events', '1' );

		// Таксономии
		$cat_id = cw_demo_get_or_create_event_term( $e['category'], 'event_category' );
		if ( $cat_id ) {
			wp_set_post_terms( $post_id, [ $cat_id ], 'event_category' );
		}
		$fmt_id = cw_demo_get_or_create_event_term( $e['format'], 'event_format' );
		if ( $fmt_id ) {
			wp_set_post_terms( $post_id, [ $fmt_id ], 'event_format' );
		}

		// Изображение
		if ( ! empty( $e['image'] ) ) {
			cw_demo_import_event_image( $e['image'], $post_id );
		}

		$created++;
	}

	$message = sprintf(
		/* translators: %d: number of created events */
		__( 'Created %d demo events.', 'codeweber' ),
		$created
	);

	return [
		'success' => true,
		'message' => $message,
		'created' => $created,
		'total'   => count( $items ),
		'errors'  => $errors,
	];
}

/**
 * Удаляет все demo мероприятия (помеченные мета _cw_demo_events = 1).
 *
 * @return array{success:bool,message:string,deleted:int,errors:string[]}
 */
function cw_demo_delete_events() {
	$posts = get_posts( [
		'post_type'      => 'events',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_key'       => '_cw_demo_events',
		'meta_value'     => '1',
		'fields'         => 'ids',
	] );

	$deleted = 0;
	$errors  = [];

	foreach ( $posts as $post_id ) {
		// Удаляем прикреплённые медиафайлы
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			wp_delete_attachment( $thumbnail_id, true );
		}

		$result = wp_delete_post( $post_id, true );
		if ( $result ) {
			$deleted++;
		} else {
			$errors[] = sprintf( 'Не удалось удалить запись #%d', $post_id );
		}
	}

	$message = sprintf(
		/* translators: %d: number of deleted events */
		__( 'Deleted %d demo events.', 'codeweber' ),
		$deleted
	);

	return [
		'success' => true,
		'message' => $message,
		'deleted' => $deleted,
		'errors'  => $errors,
	];
}
