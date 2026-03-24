<?php
/**
 * Demo данные для CPT Events (Мероприятия)
 *
 * Создаёт 50 тестовых мероприятий. Язык данных определяется локалью WordPress:
 * ru_* → русские данные, остальные локали → английские данные.
 *
 * Формат строки в массивах данных:
 * [ title, date_start (YYYY-MM-DDTHH:MM), date_end, reg_open, reg_close,
 *   location, address, organizer, seats, -, category, format, price,
 *   report_text, reg_url, hide_cal ]
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Русские демо-данные
// ---------------------------------------------------------------------------

function cw_demo_get_events_data(): array {
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
	$items        = [];
	$idx          = 0;

	// ── 1. ПРОШЕДШИЕ события 2025 ────────────────────────────────────────────
	$past = [
		[
			'Международная конференция по цифровым технологиям',
			'2025-01-20T09:00', '2025-01-22T18:00', '2024-11-01', '2025-01-10',
			'Москва', 'ул. Тверская, 12', 'DigitalConf RU', 5000, 0,
			'Конференция', 'Оффлайн', 'От 5 000 ₽',
			'<p>В конференции приняли участие 178 специалистов отрасли. Участники отметили высокий уровень докладов и насыщенную программу нетворкинга.</p>',
			'', false,
		],
		[
			'Семинар по управлению проектами',
			'2025-02-14T10:00', '2025-02-15T17:00', '2024-12-01', '2025-02-01',
			'Санкт-Петербург', 'пр. Невский, 48', 'PM Academy', 3000, 0,
			'Семинар', 'Оффлайн', 'От 3 000 ₽',
			'<p>Семинар собрал 47 руководителей проектов. Участники разобрали 6 практических кейсов по методологиям Agile и классическому управлению.</p>',
			'', false,
		],
		[
			'Вебинар: основы маркетинга',
			'2025-03-10T11:00', '2025-03-10T13:00', '2025-01-01', '2025-03-08',
			'Онлайн', '', 'MarketingPro', 500, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'<p>Вебинар посетили 291 специалист. Запись трансляции доступна в личном кабинете участника.</p>',
			'https://zoom.us/j/95123456789', false,
		],
		[
			'Форум предпринимателей 2025',
			'2025-04-05T09:00', '2025-04-07T19:00', '2025-01-15', '2025-03-25',
			'Москва', 'Экспоцентр, павильон 7', 'Бизнес Форум', 8000, 0,
			'Форум', 'Оффлайн', 'От 8 000 ₽',
			'<p>Форум объединил более 460 предпринимателей. Главными темами стали выход на новые рынки и привлечение инвестиций.</p>',
			'', false,
		],
		[
			'Мастер-класс по брендингу',
			'2025-04-25T10:00', '2025-04-25T18:00', '2025-03-01', '2025-04-20',
			'Казань', 'ул. Баумана, 5', 'Branding Lab', 4500, 0,
			'Мастер-класс', 'Оффлайн', '4 500 ₽',
			'<p>Все 28 участников завершили программу. По итогам каждый разработал собственную стратегию позиционирования бренда.</p>',
			'', false,
		],
		[
			'IT-конференция DevFest Spring',
			'2025-05-16T09:00', '2025-05-17T18:00', '2025-03-01', '2025-05-10',
			'Новосибирск', 'Технопарк', 'DevFest', 6000, 0,
			'Конференция', 'Гибридный', 'От 6 000 ₽',
			'<p>Конференция прошла в гибридном формате: 380 участников очно и более 600 онлайн. Было представлено 18 технических докладов.</p>',
			'', false,
		],
		[
			'Воркшоп по дизайн-мышлению',
			'2025-06-07T10:00', '2025-06-08T18:00', '2025-04-01', '2025-05-30',
			'Москва', 'Artplay, корп. 6', 'Design Hub', 7000, 0,
			'Мастер-класс', 'Оффлайн', '7 000 ₽',
			'<p>19 участников создали прототипы решений для реальных задач бизнеса в режиме интенсивных групповых сессий.</p>',
			'', false,
		],
		[
			'Онлайн-марафон по программированию',
			'2025-07-01T10:00', '2025-07-05T18:00', '2025-05-01', '2025-06-25',
			'Онлайн', '', 'CodeBase', 0, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'<p>В марафоне участвовали команды из 12 стран. Победители получили гранты на развитие своих проектов.</p>',
			'https://zoom.us/j/94876543210', false,
		],
		[
			'HR-конференция 2025',
			'2025-08-21T09:00', '2025-08-22T18:00', '2025-06-01', '2025-08-15',
			'Сочи', 'Сочи-Парк Отель', 'HR Club', 9000, 0,
			'Конференция', 'Оффлайн', 'От 9 000 ₽',
			'<p>Конференция собрала 237 HR-директоров и специалистов. Ключевые темы: вовлечённость сотрудников и HR-аналитика.</p>',
			'', false,
		],
		[
			'Семинар по финансовой грамотности',
			'2025-09-11T10:00', '2025-09-11T17:00', '2025-07-01', '2025-09-05',
			'Екатеринбург', 'ул. Ленина, 24', 'FinSkills', 1500, 0,
			'Семинар', 'Оффлайн', '1 500 ₽',
			'<p>Семинар прошёл при полной аудитории. Участники изучили основы инвестирования и личного финансового планирования.</p>',
			'', false,
		],
		[
			'Выставка инновационных технологий',
			'2025-09-25T10:00', '2025-09-27T18:00', '2025-07-15', '2025-09-20',
			'Москва', 'ВДНХ, павильон 75', 'InnoTech', 0, 0,
			'Форум', 'Оффлайн', 'Бесплатно',
			'<p>Выставку посетили более 900 человек. Было представлено 64 инновационных проекта из 8 регионов России.</p>',
			'', false,
		],
		[
			'Конференция по кибербезопасности',
			'2025-10-15T09:00', '2025-10-16T18:00', '2025-08-01', '2025-10-10',
			'Москва', 'Digital October', 'SecureNet', 12000, 0,
			'Конференция', 'Гибридный', 'От 12 000 ₽',
			'<p>Конференция собрала рекордные 312 специалистов по безопасности. Обсуждались угрозы 2025–2026 годов и стратегии нулевого доверия.</p>',
			'', false,
		],
		[
			'Мастер-класс по Python',
			'2025-11-08T10:00', '2025-11-08T18:00', '2025-09-01', '2025-11-01',
			'Санкт-Петербург', 'Точка кипения СПб', 'PythonRU', 3500, 0,
			'Мастер-класс', 'Оффлайн', '3 500 ₽',
			'<p>Все 24 участника успешно выполнили финальный проект. Материалы курса открыты для повторного изучения в течение 6 месяцев.</p>',
			'', false,
		],
		[
			'Форум стартапов StartupFest 2025',
			'2025-11-28T09:00', '2025-11-29T19:00', '2025-09-15', '2025-11-20',
			'Москва', 'Сколково, Гиперкуб', 'StartupFest', 5000, 0,
			'Форум', 'Гибридный', 'От 5 000 ₽',
			'<p>На форуме было представлено 45 стартапов. Трое победителей получили инвестиции на общую сумму 15 млн рублей.</p>',
			'', false,
		],
		[
			'Новогодний вебинар: итоги года',
			'2025-12-26T11:00', '2025-12-26T13:00', '2025-11-01', '2025-12-24',
			'Онлайн', '', 'DigitalConf RU', 0, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'<p>Итоговый вебинар года собрал 489 участников. Записи и презентации спикеров отправлены на почту всем зарегистрировавшимся.</p>',
			'https://meet.google.com/yta-year-end', true,
		],
	];

	foreach ( $past as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => $e[13] ?? '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => ! empty( $e[15] ),
		];
		$idx++;
	}

	// ── 2. ПРОШЕДШИЕ события нач. 2026 ──────────────────────────────────────
	$past_2026 = [
		[
			'Конференция Product Managers 2026',
			'2026-01-17T09:00', '2026-01-18T18:00', '2025-11-01', '2026-01-10',
			'Москва', 'Москва-Сити, башня А', 'ProductConf', 7500, 0,
			'Конференция', 'Гибридный', 'От 7 500 ₽',
			'<p>Конференция собрала 175 продакт-менеджеров. В центре обсуждения — AI-инструменты в продуктовой разработке.</p>',
			'', false,
		],
		[
			'Семинар: лидерство в бизнесе',
			'2026-02-06T10:00', '2026-02-06T17:00', '2025-12-01', '2026-01-30',
			'Санкт-Петербург', 'Lotte Hotel', 'Business Leaders', 5500, 0,
			'Семинар', 'Оффлайн', '5 500 ₽',
			'<p>Семинар посетили 58 топ-менеджеров. Участники разработали персональные планы лидерского развития.</p>',
			'', false,
		],
		[
			'Вебинар по UX-исследованиям',
			'2026-02-19T11:00', '2026-02-19T13:00', '2026-01-01', '2026-02-17',
			'Онлайн', '', 'UX Lab', 0, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'<p>Вебинар привлёк 193 UX-специалиста. Разобраны кейсы успешных исследований из практики российских компаний.</p>',
			'https://zoom.us/j/93456789012', false,
		],
		[
			'Мастер-класс по фотографии',
			'2026-03-07T10:00', '2026-03-08T18:00', '2026-01-15', '2026-03-01',
			'Москва', 'ул. Малая Ордынка, 21', 'PhotoSchool', 9000, 0,
			'Мастер-класс', 'Оффлайн', '9 000 ₽',
			'<p>Все 14 участников защитили итоговые фотопроекты. Лучшие работы вошли в выставочную коллекцию школы.</p>',
			'', false,
		],
		[
			'HR Tech Forum 2026',
			'2026-03-19T09:00', '2026-03-20T18:00', '2026-01-01', '2026-03-12',
			'Москва', 'Экспоцентр', 'HR Tech', 8000, 0,
			'Форум', 'Оффлайн', 'От 8 000 ₽',
			'<p>Форум объединил 387 HR-технологов и руководителей. Представлены 22 HR-платформы и 3 инновационных стартапа.</p>',
			'', false,
		],
	];

	foreach ( $past_2026 as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => $e[13] ?? '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => ! empty( $e[15] ),
		];
		$idx++;
	}

	// ── 3. ЗАПИСЬ ЕЩЁ НЕ ОТКРЫЛАСЬ ──────────────────────────────────────────
	$not_open = [
		[
			'Конференция AI Summit 2026',
			'2026-07-10T09:00', '2026-07-11T18:00', '2026-05-01', '2026-06-30',
			'Москва', 'Экспоцентр, зал А', 'AI Summit', 15000, 0,
			'Конференция', 'Гибридный', 'От 15 000 ₽',
			'', '', false,
		],
		[
			'Летний форум разработчиков',
			'2026-07-24T09:00', '2026-07-26T19:00', '2026-05-15', '2026-07-15',
			'Сочи', 'Radisson Blu Resort', 'DevForum', 20000, 0,
			'Форум', 'Оффлайн', 'От 20 000 ₽',
			'', '', false,
		],
		[
			'Семинар по ESG-стратегиям',
			'2026-08-13T10:00', '2026-08-14T17:00', '2026-06-01', '2026-08-01',
			'Санкт-Петербург', 'Отель Астория', 'GreenBiz', 10000, 0,
			'Семинар', 'Оффлайн', '10 000 ₽',
			'', '', false,
		],
		[
			'Онлайн-фестиваль цифрового искусства',
			'2026-09-05T11:00', '2026-09-07T18:00', '2026-07-01', '2026-08-31',
			'Онлайн', '', 'DigitalArt Fest', 0, 0,
			'Форум', 'Онлайн', 'Бесплатно',
			'', 'https://meet.google.com/art-fest-2026', false,
		],
		[
			'Конгресс по инновациям в образовании',
			'2026-09-18T09:00', '2026-09-19T18:00', '2026-07-01', '2026-09-10',
			'Казань', 'КЦ «Корстон»', 'EduInno', 6000, 0,
			'Конференция', 'Гибридный', 'От 6 000 ₽',
			'', '', false,
		],
	];

	foreach ( $not_open as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 4. ЗАПИСЬ ЗАКРЫТА ────────────────────────────────────────────────────
	$reg_closed = [
		[
			'Весенний SEO-интенсив',
			'2026-04-04T10:00', '2026-04-05T17:00', '2026-01-15', '2026-03-20',
			'Москва', 'Учебный центр Яндекса', 'SEO School', 9500, 0,
			'Семинар', 'Оффлайн', '9 500 ₽',
			'', '', false,
		],
		[
			'Конференция eCommerce Russia',
			'2026-04-11T09:00', '2026-04-12T18:00', '2026-01-10', '2026-03-15',
			'Москва', 'Radisson Slavyanskaya', 'eCommerce Conf', 14000, 0,
			'Конференция', 'Гибридный', 'От 14 000 ₽',
			'', '', false,
		],
		[
			'Воркшоп по видеомаркетингу',
			'2026-04-18T10:00', '2026-04-18T18:00', '2026-02-01', '2026-03-10',
			'Санкт-Петербург', 'Скорая творческая помощь', 'Video Lab', 8000, 0,
			'Мастер-класс', 'Оффлайн', '8 000 ₽',
			'', '', false,
		],
		[
			'Круглый стол по блокчейну',
			'2026-04-24T10:00', '2026-04-24T17:00', '2026-01-20', '2026-03-22',
			'Москва', 'Иннополис Москва', 'BlockchainHub', 5000, 0,
			'Форум', 'Оффлайн', '5 000 ₽',
			'', '', false,
		],
		[
			'Мастер-класс по переговорам',
			'2026-05-07T10:00', '2026-05-07T18:00', '2026-01-01', '2026-03-01',
			'Нижний Новгород', 'Отель Ibis', 'NegotiationPro', 6500, 0,
			'Мастер-класс', 'Оффлайн', '6 500 ₽',
			'', '', false,
		],
		[
			'Tech Leadership Summit',
			'2026-05-14T09:00', '2026-05-15T18:00', '2025-12-01', '2026-03-05',
			'Москва', 'Сколково', 'TechLeaders', 18000, 0,
			'Конференция', 'Оффлайн', 'От 18 000 ₽',
			'', '', false,
		],
		[
			'Онлайн-курс по Data Science',
			'2026-05-20T10:00', '2026-05-27T18:00', '2026-01-01', '2026-03-10',
			'Онлайн', '', 'Data Academy', 12000, 0,
			'Семинар', 'Онлайн', '12 000 ₽',
			'', 'https://zoom.us/j/90123456789', false,
		],
		[
			'Форум женщин в IT',
			'2026-06-06T10:00', '2026-06-07T18:00', '2026-01-01', '2026-03-15',
			'Москва', 'Точка кипения', 'WomenIT', 0, 0,
			'Форум', 'Гибридный', 'Бесплатно',
			'', '', false,
		],
	];

	foreach ( $reg_closed as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 5. ЗАПИСЬ ОТКРЫТА ────────────────────────────────────────────────────
	$reg_open = [
		[
			'Конференция по маркетингу Spring 2026',
			'2026-04-10T09:00', '2026-04-11T18:00', '2026-01-01', '2026-04-05',
			'Москва', 'Центр международной торговли', 'MarketConf', 10000, 0,
			'Конференция', 'Оффлайн', 'От 10 000 ₽',
			'', '', false,
		],
		[
			'Вебинар: социальные сети для бизнеса',
			'2026-04-15T11:00', '2026-04-15T13:00', '2026-02-01', '2026-04-12',
			'Онлайн', '', 'SMM Agency', 0, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'', 'https://zoom.us/j/92345678901', false,
		],
		[
			'Мастер-класс по созданию стартапа',
			'2026-04-25T10:00', '2026-04-26T18:00', '2026-02-15', '2026-04-20',
			'Москва', 'Hub of Intelligence, ул. Берсеневская', 'StartupSchool', 11000, 0,
			'Мастер-класс', 'Оффлайн', '11 000 ₽',
			'', '', false,
		],
		[
			'Форум «Цифровая трансформация»',
			'2026-05-06T09:00', '2026-05-07T19:00', '2026-02-01', '2026-04-30',
			'Москва', 'Президент-Отель', 'DigiTrans', 13000, 0,
			'Форум', 'Гибридный', 'От 13 000 ₽',
			'', '', false,
		],
		[
			'Семинар по UX/UI-дизайну',
			'2026-05-12T10:00', '2026-05-13T17:00', '2026-02-01', '2026-05-05',
			'Санкт-Петербург', 'Студия ODESL', 'UX School', 7500, 0,
			'Семинар', 'Оффлайн', '7 500 ₽',
			'', '', false,
		],
		[
			'Конференция DevOps Days 2026',
			'2026-05-22T09:00', '2026-05-23T18:00', '2026-03-01', '2026-05-15',
			'Москва', 'Технопарк Сколково', 'DevOps RU', 8500, 0,
			'Конференция', 'Гибридный', 'От 8 500 ₽',
			'', '', false,
		],
		[
			'Онлайн-семинар по инвестициям',
			'2026-06-03T11:00', '2026-06-03T13:00', '2026-02-20', '2026-05-30',
			'Онлайн', '', 'InvestClub', 3000, 0,
			'Семинар', 'Онлайн', '3 000 ₽',
			'', 'https://meet.google.com/inv-est-2026', false,
		],
		[
			'Мастер-класс по публичным выступлениям',
			'2026-06-13T10:00', '2026-06-14T18:00', '2026-03-01', '2026-06-07',
			'Москва', 'Деловой центр «Москва»', 'SpeakPro', 12000, 0,
			'Мастер-класс', 'Оффлайн', '12 000 ₽',
			'', '', false,
		],
		[
			'Конгресс по искусственному интеллекту',
			'2026-06-19T09:00', '2026-06-21T19:00', '2026-02-01', '2026-06-10',
			'Москва', 'Крокус Экспо', 'AI Congress', 20000, 0,
			'Конференция', 'Гибридный', 'От 20 000 ₽',
			'', '', false,
		],
		[
			'Воркшоп по Agile',
			'2026-07-04T10:00', '2026-07-04T18:00', '2026-02-01', '2026-06-28',
			'Санкт-Петербург', 'Офис Luxoft', 'Agile RU', 5000, 0,
			'Мастер-класс', 'Оффлайн', '5 000 ₽',
			'', '', false,
		],
		[
			'Вебинар: автоматизация бизнеса',
			'2026-07-09T11:00', '2026-07-09T13:00', '2026-03-01', '2026-07-07',
			'Онлайн', '', 'BizAuto', 2500, 0,
			'Вебинар', 'Онлайн', '2 500 ₽',
			'', 'https://zoom.us/j/91234567890', false,
		],
		[
			'Летняя школа предпринимателей',
			'2026-07-20T09:00', '2026-07-24T19:00', '2026-03-01', '2026-07-10',
			'Сочи', 'Sirius, бульвар Надежды', 'BizSchool', 25000, 0,
			'Форум', 'Оффлайн', '25 000 ₽',
			'', '', false,
		],
	];

	foreach ( $reg_open as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 6. БЕЗ ДАТ РЕГИСТРАЦИИ ──────────────────────────────────────────────
	$no_reg_dates = [
		[
			'Форум технологий RusTech 2026',
			'2026-10-08T09:00', '2026-10-10T19:00', '', '',
			'Москва', 'Экспоцентр', 'RusTech', 6500, 0,
			'Форум', 'Оффлайн', 'От 6 500 ₽',
			'', '', false,
		],
		[
			'Конференция по облачным технологиям',
			'2026-10-22T09:00', '2026-10-23T18:00', '', '',
			'Санкт-Петербург', 'EXPOFORUM', 'CloudConf', 11000, 0,
			'Конференция', 'Гибридный', 'От 11 000 ₽',
			'', '', false,
		],
		[
			'Новогодний IT-марафон',
			'2026-12-05T11:00', '2026-12-06T18:00', '', '',
			'Онлайн', '', 'ITMarathon', 0, 0,
			'Вебинар', 'Онлайн', 'Бесплатно',
			'', 'https://meet.google.com/new-year-marathon', false,
		],
		[
			'Мастер-класс: будущее рекламы',
			'2026-11-14T10:00', '2026-11-14T18:00', '', '',
			'Москва', 'Artplay', 'AdFuture', 7000, 0,
			'Мастер-класс', 'Оффлайн', '7 000 ₽',
			'', '', false,
		],
		[
			'Семинар по стратегическому управлению',
			'2026-11-27T10:00', '2026-11-28T17:00', '', '',
			'Екатеринбург', 'Бизнес-центр «Высоцкий»', 'Strategy Lab', 9000, 0,
			'Семинар', 'Оффлайн', '9 000 ₽',
			'', '', false,
		],
	];

	foreach ( $no_reg_dates as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	return $items;
}

// ---------------------------------------------------------------------------
// English demo data
// ---------------------------------------------------------------------------

function cw_demo_get_events_data_en(): array {
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
	$items        = [];
	$idx          = 0;

	// ── 1. PAST events 2025 ──────────────────────────────────────────────────
	$past = [
		[
			'Global Digital Innovation Summit',
			'2025-01-20T09:00', '2025-01-22T18:00', '2024-11-01', '2025-01-10',
			'London', 'ExCeL London, Royal Docks', 'DigitalConf International', 5000, 0,
			'Conference', 'In-person', 'From £150',
			'<p>The summit brought together 182 industry experts across 14 keynote sessions and two panel discussions, closing with a networking gala evening.</p>',
			'', false,
		],
		[
			'Agile & Project Management Workshop',
			'2025-02-14T10:00', '2025-02-15T17:00', '2024-12-01', '2025-02-01',
			'New York', 'Marriott Times Square', 'PM Academy', 3000, 0,
			'Seminar', 'In-person', 'From $120',
			'<p>48 project managers worked through six intensive case studies covering Agile, Scrum and traditional Waterfall methodologies.</p>',
			'', false,
		],
		[
			'Marketing Fundamentals Webinar',
			'2025-03-10T11:00', '2025-03-10T13:00', '2025-01-01', '2025-03-08',
			'Online', '', 'MarketingPro', 500, 0,
			'Webinar', 'Online', 'Free',
			'<p>291 marketing specialists attended the live session. The recording is available in each participant\'s personal account.</p>',
			'https://zoom.us/j/95123456789', false,
		],
		[
			'Entrepreneurs Forum 2025',
			'2025-04-05T09:00', '2025-04-07T19:00', '2025-01-15', '2025-03-25',
			'Berlin', 'Messe Berlin, Hall 7', 'BizForum Europe', 8000, 0,
			'Forum', 'In-person', 'From €200',
			'<p>The forum united over 460 entrepreneurs from 28 countries. Key topics included international market entry and venture capital strategies.</p>',
			'', false,
		],
		[
			'Brand Identity Masterclass',
			'2025-04-25T10:00', '2025-04-25T18:00', '2025-03-01', '2025-04-20',
			'Amsterdam', 'Pakhuis de Zwijger', 'Branding Lab', 4500, 0,
			'Workshop', 'In-person', '€180',
			'<p>All 28 participants completed their brand positioning projects. Three standout works were selected for the school\'s annual portfolio exhibition.</p>',
			'', false,
		],
		[
			'DevFest Spring Conference',
			'2025-05-16T09:00', '2025-05-17T18:00', '2025-03-01', '2025-05-10',
			'San Francisco', 'Moscone Center', 'DevFest Global', 6000, 0,
			'Conference', 'Hybrid', 'From $250',
			'<p>The hybrid event drew 390 in-person and over 620 online attendees. 18 technical talks were delivered across two themed tracks.</p>',
			'', false,
		],
		[
			'Design Thinking Bootcamp',
			'2025-06-07T10:00', '2025-06-08T18:00', '2025-04-01', '2025-05-30',
			'London', 'Central St Martins', 'Design Hub', 7000, 0,
			'Workshop', 'In-person', '£280',
			'<p>19 participants created high-fidelity prototypes solving real business challenges. Three teams went on to present at London Design Week.</p>',
			'', false,
		],
		[
			'Online Coding Marathon',
			'2025-07-01T10:00', '2025-07-05T18:00', '2025-05-01', '2025-06-25',
			'Online', '', 'CodeBase', 0, 0,
			'Webinar', 'Online', 'Free',
			'<p>Teams from 14 countries competed over five days. Prize-winning teams received grants to continue developing their open-source projects.</p>',
			'https://zoom.us/j/94876543210', false,
		],
		[
			'HR Leaders Conference 2025',
			'2025-08-21T09:00', '2025-08-22T18:00', '2025-06-01', '2025-08-15',
			'Dubai', 'DIFC Conference Centre', 'HR Club International', 9000, 0,
			'Conference', 'In-person', 'From $350',
			'<p>237 HR directors and specialists gathered to discuss employee engagement, retention, and the future of people analytics.</p>',
			'', false,
		],
		[
			'Financial Literacy Seminar',
			'2025-09-11T10:00', '2025-09-11T17:00', '2025-07-01', '2025-09-05',
			'Toronto', 'MaRS Discovery District', 'FinSkills', 1500, 0,
			'Seminar', 'In-person', '$60',
			'<p>The seminar ran to a full house. Participants explored investment basics, tax planning, and personal financial goal-setting frameworks.</p>',
			'', false,
		],
		[
			'Innovation & Technology Expo',
			'2025-09-25T10:00', '2025-09-27T18:00', '2025-07-15', '2025-09-20',
			'Amsterdam', 'RAI Amsterdam', 'InnoTech', 0, 0,
			'Forum', 'In-person', 'Free',
			'<p>Over 900 visitors attended the expo. 64 innovative projects from 12 countries were showcased across four thematic pavilions.</p>',
			'', false,
		],
		[
			'Cybersecurity Summit 2025',
			'2025-10-15T09:00', '2025-10-16T18:00', '2025-08-01', '2025-10-10',
			'Washington DC', 'Ronald Reagan Building', 'SecureNet', 12000, 0,
			'Conference', 'Hybrid', 'From $500',
			'<p>A record 316 cybersecurity professionals attended. Main sessions covered emerging threats and practical zero-trust architecture implementation.</p>',
			'', false,
		],
		[
			'Python Development Workshop',
			'2025-11-08T10:00', '2025-11-08T18:00', '2025-09-01', '2025-11-01',
			'London', 'Google Campus London', 'PythonUK', 3500, 0,
			'Workshop', 'In-person', '£140',
			'<p>All 24 participants submitted and presented final projects. Course materials remain accessible to attendees for six months post-event.</p>',
			'', false,
		],
		[
			'StartupFest Global 2025',
			'2025-11-28T09:00', '2025-11-29T19:00', '2025-09-15', '2025-11-20',
			'New York', 'Brooklyn Navy Yard', 'StartupFest International', 5000, 0,
			'Forum', 'Hybrid', 'From $200',
			'<p>45 startups pitched to over 60 investors. Three winners secured seed funding totalling $1.2 million to scale their products.</p>',
			'', false,
		],
		[
			'Year in Review: Tech Webinar',
			'2025-12-26T11:00', '2025-12-26T13:00', '2025-11-01', '2025-12-24',
			'Online', '', 'DigitalConf International', 0, 0,
			'Webinar', 'Online', 'Free',
			'<p>The closing webinar of the year attracted 492 participants. Slides and recordings were emailed to all registrants within 24 hours.</p>',
			'https://meet.google.com/yta-year-end', true,
		],
	];

	foreach ( $past as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => $e[13] ?? '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => ! empty( $e[15] ),
		];
		$idx++;
	}

	// ── 2. PAST events early 2026 ────────────────────────────────────────────
	$past_2026 = [
		[
			'Product Managers Summit 2026',
			'2026-01-17T09:00', '2026-01-18T18:00', '2025-11-01', '2026-01-10',
			'London', 'The Barbican Centre', 'ProductConf', 7500, 0,
			'Conference', 'Hybrid', 'From £300',
			'<p>175 product managers shared insights on AI-driven product development, roadmap prioritisation and cross-functional leadership.</p>',
			'', false,
		],
		[
			'Business Leadership Seminar',
			'2026-02-06T10:00', '2026-02-06T17:00', '2025-12-01', '2026-01-30',
			'Singapore', 'Marina Bay Sands', 'Business Leaders Asia', 5500, 0,
			'Seminar', 'In-person', 'S$220',
			'<p>58 senior executives developed personal leadership plans through guided coaching and structured peer feedback workshops.</p>',
			'', false,
		],
		[
			'UX Research Methods Webinar',
			'2026-02-19T11:00', '2026-02-19T13:00', '2026-01-01', '2026-02-17',
			'Online', '', 'UX Lab', 0, 0,
			'Webinar', 'Online', 'Free',
			'<p>193 UX specialists explored advanced qualitative and quantitative research techniques with live case-study analysis.</p>',
			'https://zoom.us/j/93456789012', false,
		],
		[
			'Photography Masterclass',
			'2026-03-07T10:00', '2026-03-08T18:00', '2026-01-15', '2026-03-01',
			'Paris', 'Palais de Tokyo', 'PhotoSchool Paris', 9000, 0,
			'Workshop', 'In-person', '€360',
			'<p>All 14 participants presented their final portfolios. Selected works were included in the school\'s annual group exhibition.</p>',
			'', false,
		],
		[
			'HR Tech Forum 2026',
			'2026-03-19T09:00', '2026-03-20T18:00', '2026-01-01', '2026-03-12',
			'Berlin', 'Tempelhof Hangar', 'HR Tech Europe', 8000, 0,
			'Forum', 'In-person', 'From €320',
			'<p>387 HR technology leaders attended. 22 HR platforms were demonstrated and three startups received the HR Tech Innovation Award.</p>',
			'', false,
		],
	];

	foreach ( $past_2026 as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => $e[13] ?? '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => ! empty( $e[15] ),
		];
		$idx++;
	}

	// ── 3. REGISTRATION NOT OPEN YET ─────────────────────────────────────────
	$not_open = [
		[
			'AI Summit Europe 2026',
			'2026-07-10T09:00', '2026-07-11T18:00', '2026-05-01', '2026-06-30',
			'London', 'ExCeL London', 'AI Summit Global', 15000, 0,
			'Conference', 'Hybrid', 'From £600',
			'', '', false,
		],
		[
			'Summer Developers Forum',
			'2026-07-24T09:00', '2026-07-26T19:00', '2026-05-15', '2026-07-15',
			'Barcelona', 'CCIB – Centre de Convencions', 'DevForum Europe', 20000, 0,
			'Forum', 'In-person', 'From €800',
			'', '', false,
		],
		[
			'ESG Strategy Seminar',
			'2026-08-13T10:00', '2026-08-14T17:00', '2026-06-01', '2026-08-01',
			'Zurich', 'The Dolder Grand', 'GreenBiz Europe', 10000, 0,
			'Seminar', 'In-person', 'CHF 400',
			'', '', false,
		],
		[
			'Digital Art Festival',
			'2026-09-05T11:00', '2026-09-07T18:00', '2026-07-01', '2026-08-31',
			'Online', '', 'DigitalArt Fest', 0, 0,
			'Forum', 'Online', 'Free',
			'', 'https://meet.google.com/art-fest-2026', false,
		],
		[
			'EdTech Innovation Congress',
			'2026-09-18T09:00', '2026-09-19T18:00', '2026-07-01', '2026-09-10',
			'Toronto', 'Metro Toronto Convention Centre', 'EduInno', 6000, 0,
			'Conference', 'Hybrid', 'From C$240',
			'', '', false,
		],
	];

	foreach ( $not_open as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 4. REGISTRATION CLOSED ───────────────────────────────────────────────
	$reg_closed = [
		[
			'Spring SEO Intensive',
			'2026-04-04T10:00', '2026-04-05T17:00', '2026-01-15', '2026-03-20',
			'London', 'Google UK HQ, 6 Pancras Square', 'SEO School UK', 9500, 0,
			'Seminar', 'In-person', '£380',
			'', '', false,
		],
		[
			'eCommerce World Conference',
			'2026-04-11T09:00', '2026-04-12T18:00', '2026-01-10', '2026-03-15',
			'Amsterdam', 'RAI Amsterdam', 'eCommerce Europe', 14000, 0,
			'Conference', 'Hybrid', 'From €560',
			'', '', false,
		],
		[
			'Video Marketing Workshop',
			'2026-04-18T10:00', '2026-04-18T18:00', '2026-02-01', '2026-03-10',
			'Manchester', 'MediaCity UK', 'Video Lab', 8000, 0,
			'Workshop', 'In-person', '£320',
			'', '', false,
		],
		[
			'Blockchain Roundtable',
			'2026-04-24T10:00', '2026-04-24T17:00', '2026-01-20', '2026-03-22',
			'Zurich', 'Crypto Valley Convention Center', 'BlockchainHub', 5000, 0,
			'Forum', 'In-person', 'CHF 200',
			'', '', false,
		],
		[
			'Negotiation Skills Masterclass',
			'2026-05-07T10:00', '2026-05-07T18:00', '2026-01-01', '2026-03-01',
			'Dublin', 'Radisson Blu Royal Hotel', 'NegotiationPro', 6500, 0,
			'Workshop', 'In-person', '€260',
			'', '', false,
		],
		[
			'Tech Leadership Summit',
			'2026-05-14T09:00', '2026-05-15T18:00', '2025-12-01', '2026-03-05',
			'San Francisco', 'Salesforce Tower', 'TechLeaders', 18000, 0,
			'Conference', 'In-person', 'From $720',
			'', '', false,
		],
		[
			'Data Science Online Bootcamp',
			'2026-05-20T10:00', '2026-05-27T18:00', '2026-01-01', '2026-03-10',
			'Online', '', 'Data Academy', 12000, 0,
			'Seminar', 'Online', '$480',
			'', 'https://zoom.us/j/90123456789', false,
		],
		[
			'Women in Tech Forum',
			'2026-06-06T10:00', '2026-06-07T18:00', '2026-01-01', '2026-03-15',
			'London', 'Code First Girls HQ', 'WomenIT', 0, 0,
			'Forum', 'Hybrid', 'Free',
			'', '', false,
		],
	];

	foreach ( $reg_closed as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 5. REGISTRATION OPEN ─────────────────────────────────────────────────
	$reg_open = [
		[
			'Spring Marketing Conference 2026',
			'2026-04-10T09:00', '2026-04-11T18:00', '2026-01-01', '2026-04-05',
			'New York', 'Javits Center', 'MarketConf', 10000, 0,
			'Conference', 'In-person', 'From $400',
			'', '', false,
		],
		[
			'Social Media for Business Webinar',
			'2026-04-15T11:00', '2026-04-15T13:00', '2026-02-01', '2026-04-12',
			'Online', '', 'SMM Agency', 0, 0,
			'Webinar', 'Online', 'Free',
			'', 'https://zoom.us/j/92345678901', false,
		],
		[
			'Build Your Startup Masterclass',
			'2026-04-25T10:00', '2026-04-26T18:00', '2026-02-15', '2026-04-20',
			'Berlin', 'Factory Berlin', 'StartupSchool', 11000, 0,
			'Workshop', 'In-person', '€440',
			'', '', false,
		],
		[
			'Digital Transformation Forum',
			'2026-05-06T09:00', '2026-05-07T19:00', '2026-02-01', '2026-04-30',
			'London', 'The O2 Arena', 'DigiTrans', 13000, 0,
			'Forum', 'Hybrid', 'From £520',
			'', '', false,
		],
		[
			'UX/UI Design Intensive',
			'2026-05-12T10:00', '2026-05-13T17:00', '2026-02-01', '2026-05-05',
			'Amsterdam', 'Spaces Vijzelstraat', 'UX School', 7500, 0,
			'Seminar', 'In-person', '€300',
			'', '', false,
		],
		[
			'DevOps Days 2026',
			'2026-05-22T09:00', '2026-05-23T18:00', '2026-03-01', '2026-05-15',
			'San Francisco', 'Moscone Center', 'DevOps Global', 8500, 0,
			'Conference', 'Hybrid', 'From $340',
			'', '', false,
		],
		[
			'Online Investment Seminar',
			'2026-06-03T11:00', '2026-06-03T13:00', '2026-02-20', '2026-05-30',
			'Online', '', 'InvestClub', 3000, 0,
			'Seminar', 'Online', '$120',
			'', 'https://meet.google.com/inv-est-2026', false,
		],
		[
			'Public Speaking Masterclass',
			'2026-06-13T10:00', '2026-06-14T18:00', '2026-03-01', '2026-06-07',
			'London', '30 Euston Square', 'SpeakPro', 12000, 0,
			'Workshop', 'In-person', '£480',
			'', '', false,
		],
		[
			'AI & Machine Learning Congress',
			'2026-06-19T09:00', '2026-06-21T19:00', '2026-02-01', '2026-06-10',
			'London', 'ExCeL London', 'AI Congress', 20000, 0,
			'Conference', 'Hybrid', 'From £800',
			'', '', false,
		],
		[
			'Agile Practitioner Workshop',
			'2026-07-04T10:00', '2026-07-04T18:00', '2026-02-01', '2026-06-28',
			'Dublin', 'Dogpatch Labs', 'Agile Europe', 5000, 0,
			'Workshop', 'In-person', '€200',
			'', '', false,
		],
		[
			'Business Automation Webinar',
			'2026-07-09T11:00', '2026-07-09T13:00', '2026-03-01', '2026-07-07',
			'Online', '', 'BizAuto', 2500, 0,
			'Webinar', 'Online', '$100',
			'', 'https://zoom.us/j/91234567890', false,
		],
		[
			'Summer Entrepreneurship School',
			'2026-07-20T09:00', '2026-07-24T19:00', '2026-03-01', '2026-07-10',
			'Lisbon', 'Web Summit Campus', 'BizSchool', 25000, 0,
			'Forum', 'In-person', 'From €1 000',
			'', '', false,
		],
	];

	foreach ( $reg_open as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	// ── 6. NO REGISTRATION DATES ─────────────────────────────────────────────
	$no_reg_dates = [
		[
			'Global Tech Forum 2026',
			'2026-10-08T09:00', '2026-10-10T19:00', '', '',
			'London', 'ExCeL London', 'GlobalTech', 6500, 0,
			'Forum', 'In-person', 'From £260',
			'', '', false,
		],
		[
			'Cloud Computing Conference',
			'2026-10-22T09:00', '2026-10-23T18:00', '', '',
			'Amsterdam', 'RAI Amsterdam', 'CloudConf', 11000, 0,
			'Conference', 'Hybrid', 'From €440',
			'', '', false,
		],
		[
			'New Year IT Marathon',
			'2026-12-05T11:00', '2026-12-06T18:00', '', '',
			'Online', '', 'ITMarathon', 0, 0,
			'Webinar', 'Online', 'Free',
			'', 'https://meet.google.com/new-year-marathon', false,
		],
		[
			'Future of Advertising Masterclass',
			'2026-11-14T10:00', '2026-11-14T18:00', '', '',
			'New York', 'Cannes Lions NY, 346 Park Ave', 'AdFuture', 7000, 0,
			'Workshop', 'In-person', 'From $280',
			'', '', false,
		],
		[
			'Strategic Management Seminar',
			'2026-11-27T10:00', '2026-11-28T17:00', '', '',
			'Toronto', 'Fairmont Royal York', 'Strategy Lab', 9000, 0,
			'Seminar', 'In-person', 'C$360',
			'', '', false,
		],
	];

	foreach ( $no_reg_dates as $e ) {
		$items[] = [
			'title'       => $e[0],
			'date_start'  => $e[1],
			'date_end'    => $e[2],
			'reg_open'    => $e[3],
			'reg_close'   => $e[4],
			'location'    => $e[5],
			'address'     => $e[6],
			'organizer'   => $e[7],
			'price'       => $e[12],
			'seats'       => $e[8],
			'category'    => $e[10],
			'format'      => $e[11],
			'image'       => $photos[ $idx % $total_photos ],
			'report_text' => '',
			'reg_url'     => $e[14] ?? '',
			'hide_cal'    => false,
		];
		$idx++;
	}

	return $items;
}

// ---------------------------------------------------------------------------
// Helpers (locale-independent)
// ---------------------------------------------------------------------------

function cw_demo_compute_fake_registered( array $item, int $index ): int {
	$seats = (int) $item['seats'];
	if ( $seats <= 0 ) {
		return 0;
	}

	$now = current_time( 'Y-m-d' );

	if ( ! empty( $item['date_end'] ) && substr( $item['date_end'], 0, 10 ) < $now ) {
		$pct = 0.70 + ( $index % 6 ) * 0.05;
		return min( $seats, (int) round( $seats * $pct ) );
	}

	if ( ! empty( $item['reg_open'] ) && $item['reg_open'] > $now ) {
		return 0;
	}

	if ( ! empty( $item['reg_close'] ) && $item['reg_close'] < $now ) {
		$pct = 0.50 + ( $index % 5 ) * 0.07;
		return min( $seats, (int) round( $seats * $pct ) );
	}

	if ( $index % 7 === 0 ) {
		return $seats;
	}

	$pct = 0.20 + ( $index % 5 ) * 0.085;
	return (int) round( $seats * $pct );
}

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

// ---------------------------------------------------------------------------
// Create / Delete
// ---------------------------------------------------------------------------

function cw_demo_create_events() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$locale = get_locale();
	$items  = strncmp( $locale, 'ru', 2 ) === 0
		? cw_demo_get_events_data()
		: cw_demo_get_events_data_en();

	$created = 0;
	$errors  = [];
	$now     = current_time( 'Y-m-d' );

	foreach ( $items as $e ) {
		$post_id = wp_insert_post( [
			'post_type'    => 'events',
			'post_title'   => sanitize_text_field( $e['title'] ),
			'post_content' => sprintf(
				'<p>%s — this is an opportunity to discover the latest trends, exchange experience with peers and build valuable professional connections.</p><p>The programme includes talks by leading experts, hands-on workshops and networking sessions. Places are limited — register early.</p>',
				esc_html( $e['title'] )
			),
			'post_excerpt' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
		], true );

		if ( is_wp_error( $post_id ) ) {
			$errors[] = sprintf( 'Error creating "%s": %s', $e['title'], $post_id->get_error_message() );
			continue;
		}

		// Core date / location meta
		update_post_meta( $post_id, '_event_date_start', $e['date_start'] );
		update_post_meta( $post_id, '_event_date_end',   $e['date_end'] );
		if ( $e['reg_open'] ) {
			update_post_meta( $post_id, '_event_registration_open', $e['reg_open'] );
		}
		if ( $e['reg_close'] ) {
			update_post_meta( $post_id, '_event_registration_close', $e['reg_close'] );
		}
		update_post_meta( $post_id, '_event_location',  sanitize_text_field( $e['location'] ) );
		update_post_meta( $post_id, '_event_address',   sanitize_text_field( $e['address'] ) );
		update_post_meta( $post_id, '_event_organizer', sanitize_text_field( $e['organizer'] ) );
		update_post_meta( $post_id, '_event_price',     sanitize_text_field( $e['price'] ) );
		if ( $e['seats'] > 0 ) {
			update_post_meta( $post_id, '_event_max_participants', absint( $e['seats'] ) );
		}

		// Registration enabled: off for past events, on for upcoming
		$is_past = ! empty( $e['date_end'] ) && substr( $e['date_end'], 0, 10 ) < $now;
		update_post_meta( $post_id, '_event_registration_enabled', $is_past ? '0' : '1' );

		// Registration / meeting URL (webinars, online events)
		if ( ! empty( $e['reg_url'] ) ) {
			update_post_meta( $post_id, '_event_registration_url', esc_url_raw( $e['reg_url'] ) );
		}

		// Report text for past events
		if ( ! empty( $e['report_text'] ) ) {
			update_post_meta( $post_id, '_event_report_text', wp_kses_post( $e['report_text'] ) );
		}

		// Hide "Add to Calendar" button
		if ( ! empty( $e['hide_cal'] ) ) {
			update_post_meta( $post_id, '_event_hide_add_to_calendar', '1' );
		}

		// Fake registered count for realistic seat counter display
		$fake_registered = cw_demo_compute_fake_registered( $e, $created );
		if ( $fake_registered > 0 ) {
			update_post_meta( $post_id, '_event_fake_registered', $fake_registered );
		}

		// Demo marker for bulk deletion
		update_post_meta( $post_id, '_cw_demo_events', '1' );

		// Taxonomies
		$cat_id = cw_demo_get_or_create_event_term( $e['category'], 'event_category' );
		if ( $cat_id ) {
			wp_set_post_terms( $post_id, [ $cat_id ], 'event_category' );
		}
		$fmt_id = cw_demo_get_or_create_event_term( $e['format'], 'event_format' );
		if ( $fmt_id ) {
			wp_set_post_terms( $post_id, [ $fmt_id ], 'event_format' );
		}

		// Featured image
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
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			wp_delete_attachment( $thumbnail_id, true );
		}

		$result = wp_delete_post( $post_id, true );
		if ( $result ) {
			$deleted++;
		} else {
			$errors[] = sprintf( 'Failed to delete post #%d', $post_id );
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
