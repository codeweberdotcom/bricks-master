<?php
/**
 * Demo данные для WooCommerce Products
 *
 * Создание/удаление demo товаров: 50 товаров, 10 категорий, 10 тегов,
 * атрибуты (Цвет, Размер, Материал), простые и вариативные товары.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Данные demo товаров.
 *
 * @return array
 */
function cw_demo_get_products_data() {
	$locale     = get_locale();
	$is_russian = ( strpos( $locale, 'ru' ) === 0 );

	// Изображения: sh1.jpg – sh9.jpg (циклически)
	$imgs = array( 'sh1.jpg','sh2.jpg','sh3.jpg','sh4.jpg','sh5.jpg','sh6.jpg','sh7.jpg','sh8.jpg','sh9.jpg' );
	$img  = function ( $i ) use ( $imgs ) { return $imgs[ $i % count( $imgs ) ]; };

	if ( $is_russian ) {
		return array(
			'categories' => array(
				// 3 уровня: Одежда → Женская одежда → Платья
				array( 'name' => 'Одежда',             'parent' => '' ),
				array( 'name' => 'Женская одежда',     'parent' => 'Одежда' ),
				array( 'name' => 'Платья',             'parent' => 'Женская одежда' ),
				array( 'name' => 'Верхняя одежда',     'parent' => 'Одежда' ),
				// 2 уровня: Обувь → Спортивная / Классическая
				array( 'name' => 'Обувь',              'parent' => '' ),
				array( 'name' => 'Спортивная обувь',   'parent' => 'Обувь' ),
				array( 'name' => 'Классическая обувь', 'parent' => 'Обувь' ),
				// 2 уровня: Дом и сад → Текстиль / Декор
				array( 'name' => 'Дом и сад',          'parent' => '' ),
				array( 'name' => 'Текстиль',           'parent' => 'Дом и сад' ),
				array( 'name' => 'Декор',              'parent' => 'Дом и сад' ),
				// 2 уровня: Детское → Детская одежда
				array( 'name' => 'Детское',            'parent' => '' ),
				array( 'name' => 'Детская одежда',     'parent' => 'Детское' ),
				// Одноуровневые
				array( 'name' => 'Аксессуары',         'parent' => '' ),
				array( 'name' => 'Косметика',          'parent' => '' ),
				array( 'name' => 'Электроника',        'parent' => '' ),
				array( 'name' => 'Спорт',              'parent' => '' ),
				array( 'name' => 'Украшения',          'parent' => '' ),
				array( 'name' => 'Книги',              'parent' => '' ),
			),
			'tags' => array(
				'новинка', 'популярное', 'распродажа', 'хит', 'премиум',
				'спорт', 'подарок', 'лимитед', 'тренд', 'сезонное',
			),
			'attributes' => array(
				'color' => array(
					'label'  => 'Цвет',
					'type'   => 'color',
					'values' => array( 'Красный','Синий','Чёрный','Белый','Зелёный','Серый','Коричневый','Бежевый' ),
					'meta'   => array(
						'Красный'    => array( 'color' => '#e74c3c' ),
						'Синий'      => array( 'color' => '#3498db' ),
						'Чёрный'     => array( 'color' => '#1a1a1a' ),
						'Белый'      => array( 'color' => '#f8f9fa' ),
						'Зелёный'    => array( 'color' => '#2ecc71' ),
						'Серый'      => array( 'color' => '#7f8c8d' ),
						'Коричневый' => array( 'color' => '#8b4513' ),
						'Бежевый'    => array( 'color' => '#d2b48c' ),
					),
				),
				'size' => array(
					'label'  => 'Размер',
					'type'   => 'button',
					'values' => array( 'XS','S','M','L','XL','XXL' ),
				),
				'material' => array(
					'label'  => 'Материал',
					'type'   => 'image',
					'values' => array( 'Хлопок','Шерсть','Полиэстер','Кожа','Лён' ),
					'meta'   => array(
						'Хлопок'         => array( 'image' => 'sh5.jpg' ),
						'Шерсть'         => array( 'image' => 'sh6.jpg' ),
						'Полиэстер'      => array( 'image' => 'sh7.jpg' ),
						'Кожа'           => array( 'image' => 'sh8.jpg' ),
						'Лён'            => array( 'image' => 'sh9.jpg' ),
						'Золото'         => array( 'image' => 'sh1.jpg' ),
						'Серебро'        => array( 'image' => 'sh2.jpg' ),
						'Розовое золото' => array( 'image' => 'sh3.jpg' ),
					),
				),
				'print' => array(
					'label'  => 'Принт',
					'type'   => 'select',
					'values' => array( 'Полоска','Клетка','Цветочный','Абстракция' ),
				),
			),
			'brands' => array(
				array( 'name' => 'УрбанФит',     'logo' => 'c1.png' ),
				array( 'name' => 'Нордик',        'logo' => 'c2.png' ),
				array( 'name' => 'НатурВэр',      'logo' => 'c3.png' ),
				array( 'name' => 'СпортЛайн',     'logo' => 'c4.png' ),
				array( 'name' => 'ЮвелирАрт',     'logo' => 'c5.png' ),
				array( 'name' => 'БьютиЭссенс',   'logo' => 'c6.png' ),
				array( 'name' => 'ДомДекор',       'logo' => 'c7.png' ),
				array( 'name' => 'ТехПульс',       'logo' => 'c8.png' ),
				array( 'name' => 'ДетскийМир',     'logo' => 'c9.png' ),
				array( 'name' => 'КнижныйДом',     'logo' => 'c10.png' ),
			),
			'items' => array(
				// ── Одежда (5) ──────────────────────────────────────────
				array(
					'title' => 'Футболка базовая', 'desc' => 'Классическая футболка из 100% хлопка.',
					'image' => $img(0), 'category' => 'Женская одежда', 'tags' => array('популярное','новинка'),
					'regular_price' => '1290', 'sale_price' => '', 'sku_base' => 'DEMO-TSHIRT',
				'brand' => 'УрбанФит',
					'type' => 'variable',
					'attributes' => array(
						'color'  => array('Белый','Чёрный','Синий'),
						'size'   => array('S','M','L','XL'),
						'print'  => array('Полоска','Абстракция'),
					),
				),
				array(
					'title' => 'Джинсы классические', 'desc' => 'Прямые джинсы из плотного денима.',
					'image' => $img(1), 'category' => 'Женская одежда', 'tags' => array('хит','популярное'),
					'regular_price' => '3990', 'sale_price' => '2990', 'sku_base' => 'DEMO-JEANS',
				'brand' => 'Нордик',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Синий','Чёрный'),
						'size'  => array('S','M','L','XL','XXL'),
					),
				),
				array(
					'title' => 'Платье летнее', 'desc' => 'Лёгкое платье из льна для тёплых дней.',
					'image' => $img(2), 'category' => 'Платья', 'tags' => array('новинка','тренд'),
					'regular_price' => '4500', 'sale_price' => '3200', 'sku_base' => 'DEMO-DRESS',
				'brand' => 'НатурВэр',
					'type' => 'variable',
					'attributes' => array(
						'color'    => array('Белый','Зелёный','Красный'),
						'material' => array('Лён','Хлопок'),
						'print'    => array('Цветочный','Клетка'),
					),
				),
				array(
					'title' => 'Куртка демисезонная', 'desc' => 'Водоотталкивающая куртка для весны и осени.',
					'image' => $img(3), 'category' => 'Верхняя одежда', 'tags' => array('хит','сезонное'),
					'regular_price' => '12500', 'sale_price' => '', 'sku_base' => 'DEMO-JACKET',
				'brand' => 'УрбанФит',
					'type' => 'simple', 'featured' => true,
				),
				array(
					'title' => 'Свитер шерстяной', 'desc' => 'Тёплый свитер из натуральной шерсти.',
					'image' => $img(4), 'category' => 'Женская одежда', 'tags' => array('премиум','сезонное'),
					'regular_price' => '5990', 'sale_price' => '', 'sku_base' => 'DEMO-SWEATER',
				'brand' => 'Нордик',
					'type' => 'simple',
				),
				// ── Обувь (5) ───────────────────────────────────────────
				array(
					'title' => 'Кроссовки беговые', 'desc' => 'Лёгкие беговые кроссовки с амортизирующей подошвой.',
					'image' => $img(5), 'category' => 'Спортивная обувь', 'tags' => array('спорт','популярное'),
					'regular_price' => '7500', 'sale_price' => '5900', 'sku_base' => 'DEMO-RUN',
				'brand' => 'СпортЛайн',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Белый','Чёрный','Синий'),
						'size'  => array('38','39','40','41','42','43'),
					),
				),
				array(
					'title' => 'Кеды повседневные', 'desc' => 'Удобные кеды на резиновой подошве.',
					'image' => $img(6), 'category' => 'Спортивная обувь', 'tags' => array('популярное','распродажа'),
					'regular_price' => '4900', 'sale_price' => '3500', 'sku_base' => 'DEMO-SNEAK',
				'brand' => 'СпортЛайн',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Белый','Чёрный','Красный'),
						'size'  => array('37','38','39','40','41'),
					),
				),
				array(
					'title' => 'Ботинки кожаные', 'desc' => 'Классические ботинки из натуральной кожи.',
					'image' => $img(7), 'category' => 'Классическая обувь', 'tags' => array('премиум','хит'),
					'regular_price' => '9900', 'sale_price' => '', 'sku_base' => 'DEMO-BOOT',
				'brand' => 'НатурВэр',
					'type' => 'simple', 'featured' => true,
				),
				array(
					'title' => 'Сандалии летние', 'desc' => 'Открытые сандалии для пляжа и города.',
					'image' => $img(8), 'category' => 'Классическая обувь', 'tags' => array('новинка','сезонное'),
					'regular_price' => '2990', 'sale_price' => '1990', 'sku_base' => 'DEMO-SAND',
				'brand' => 'СпортЛайн',
					'type' => 'simple',
				),
				array(
					'title' => 'Мокасины замшевые', 'desc' => 'Мягкие мокасины из замши на каждый день.',
					'image' => $img(0), 'category' => 'Классическая обувь', 'tags' => array('тренд'),
					'regular_price' => '6500', 'sale_price' => '', 'sku_base' => 'DEMO-MOC',
				'brand' => 'НатурВэр',
					'type' => 'simple',
				),
				// ── Аксессуары (5) ──────────────────────────────────────
				array(
					'title' => 'Кожаная сумка', 'desc' => 'Стильная сумка ручной работы из кожи.',
					'image' => $img(1), 'category' => 'Аксессуары', 'tags' => array('премиум','хит'),
					'regular_price' => '9900', 'sale_price' => '', 'sku_base' => 'DEMO-BAG',
				'brand' => 'УрбанФит',
					'type' => 'variable',
					'attributes' => array(
						'color'    => array('Чёрный','Коричневый','Бежевый'),
						'material' => array('Кожа'),
					),
					'featured' => true,
				),
				array(
					'title' => 'Солнцезащитные очки', 'desc' => 'Очки с UV400 защитой в металлической оправе.',
					'image' => $img(2), 'category' => 'Аксессуары', 'tags' => array('тренд','лимитед'),
					'regular_price' => '4500', 'sale_price' => '', 'sku_base' => 'DEMO-GLASS',
				'brand' => 'УрбанФит',
					'type' => 'simple',
				),
				array(
					'title' => 'Наручные часы', 'desc' => 'Элегантные часы с кожаным ремешком.',
					'image' => $img(3), 'category' => 'Аксессуары', 'tags' => array('премиум','подарок'),
					'regular_price' => '15900', 'sale_price' => '12900', 'sku_base' => 'DEMO-WATCH',
				'brand' => 'ЮвелирАрт',
					'type' => 'simple', 'featured' => true,
				),
				array(
					'title' => 'Шарф кашемировый', 'desc' => 'Мягкий шарф из кашемира 100%.',
					'image' => $img(4), 'category' => 'Аксессуары', 'tags' => array('премиум','сезонное'),
					'regular_price' => '3990', 'sale_price' => '', 'sku_base' => 'DEMO-SCARF',
				'brand' => 'Нордик',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Серый','Синий','Бежевый'),
					),
				),
				array(
					'title' => 'Ремень кожаный', 'desc' => 'Классический кожаный ремень с металлической пряжкой.',
					'image' => $img(5), 'category' => 'Аксессуары', 'tags' => array('хит'),
					'regular_price' => '2490', 'sale_price' => '', 'sku_base' => 'DEMO-BELT',
				'brand' => 'НатурВэр',
					'type' => 'simple',
				),
				// ── Косметика (5) ───────────────────────────────────────
				array(
					'title' => 'Парфюм женский', 'desc' => 'Аромат с нотами бергамота и белых цветов.',
					'image' => $img(6), 'category' => 'Косметика', 'tags' => array('премиум','подарок'),
					'regular_price' => '4990', 'sale_price' => '', 'sku_base' => 'DEMO-PERF-W',
				'brand' => 'БьютиЭссенс',
					'type' => 'simple', 'featured' => true,
				),
				array(
					'title' => 'Набор по уходу за кожей', 'desc' => 'Крем, сыворотка и тоник в подарочной коробке.',
					'image' => $img(7), 'category' => 'Косметика', 'tags' => array('подарок','хит','новинка'),
					'regular_price' => '6900', 'sale_price' => '', 'sku_base' => 'DEMO-COSM',
				'brand' => 'БьютиЭссенс',
					'type' => 'simple',
				),
				array(
					'title' => 'Крем увлажняющий SPF30', 'desc' => 'Дневной крем с защитой от солнца.',
					'image' => $img(8), 'category' => 'Косметика', 'tags' => array('популярное'),
					'regular_price' => '1990', 'sale_price' => '1590', 'sku_base' => 'DEMO-CREAM',
				'brand' => 'БьютиЭссенс',
					'type' => 'simple',
				),
				array(
					'title' => 'Тушь для ресниц', 'desc' => 'Объёмная тушь с эффектом накладных ресниц.',
					'image' => $img(0), 'category' => 'Косметика', 'tags' => array('тренд','новинка'),
					'regular_price' => '990', 'sale_price' => '', 'sku_base' => 'DEMO-MASCARA',
				'brand' => 'БьютиЭссенс',
					'type' => 'simple',
				),
				array(
					'title' => 'Помада стойкая', 'desc' => 'Стойкая помада на 24 часа без пересыхания.',
					'image' => $img(1), 'category' => 'Косметика', 'tags' => array('хит','распродажа'),
					'regular_price' => '790', 'sale_price' => '590', 'sku_base' => 'DEMO-LIPSTICK',
				'brand' => 'БьютиЭссенс',
					'type' => 'simple',
				),
				// ── Дом и сад (5) ───────────────────────────────────────
				array(
					'title' => 'Плед флисовый', 'desc' => 'Мягкий флисовый плед 150×200 см.',
					'image' => $img(2), 'category' => 'Текстиль', 'tags' => array('популярное','сезонное'),
					'regular_price' => '2490', 'sale_price' => '', 'sku_base' => 'DEMO-BLANKET',
				'brand' => 'ДомДекор',
					'type' => 'variable',
					'attributes' => array(
						'color'    => array('Серый','Бежевый','Синий'),
						'material' => array('Полиэстер','Хлопок'),
					),
				),
				array(
					'title' => 'Набор кухонных полотенец', 'desc' => '6 полотенец из хлопка в подарочной упаковке.',
					'image' => $img(3), 'category' => 'Текстиль', 'tags' => array('подарок','новинка'),
					'regular_price' => '1290', 'sale_price' => '', 'sku_base' => 'DEMO-TOWEL',
				'brand' => 'ДомДекор',
					'type' => 'simple',
				),
				array(
					'title' => 'Ваза декоративная', 'desc' => 'Керамическая ваза ручной работы.',
					'image' => $img(4), 'category' => 'Декор', 'tags' => array('тренд','лимитед'),
					'regular_price' => '3490', 'sale_price' => '', 'sku_base' => 'DEMO-VASE',
				'brand' => 'ДомДекор',
					'type' => 'simple',
				),
				array(
					'title' => 'Ароматические свечи (набор)', 'desc' => 'Набор из 3 свечей с разными ароматами.',
					'image' => $img(5), 'category' => 'Декор', 'tags' => array('подарок','хит'),
					'regular_price' => '1890', 'sale_price' => '1490', 'sku_base' => 'DEMO-CANDLE',
				'brand' => 'ДомДекор',
					'type' => 'simple',
				),
				array(
					'title' => 'Рамка для фото', 'desc' => 'Деревянная рамка 20×30 в скандинавском стиле.',
					'image' => $img(6), 'category' => 'Декор', 'tags' => array('новинка'),
					'regular_price' => '990', 'sale_price' => '', 'sku_base' => 'DEMO-FRAME',
				'brand' => 'ДомДекор',
					'type' => 'simple',
				),
				// ── Электроника (5) ─────────────────────────────────────
				array(
					'title' => 'Беспроводные наушники', 'desc' => 'Накладные наушники Bluetooth 5.0, 30 ч работы.',
					'image' => $img(7), 'category' => 'Электроника', 'tags' => array('хит','популярное'),
					'regular_price' => '7990', 'sale_price' => '5990', 'sku_base' => 'DEMO-HEADP',
				'brand' => 'ТехПульс',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Чёрный','Белый'),
					),
					'featured' => true,
				),
				array(
					'title' => 'Умные часы', 'desc' => 'Смарт-часы с мониторингом здоровья и GPS.',
					'image' => $img(8), 'category' => 'Электроника', 'tags' => array('новинка','тренд'),
					'regular_price' => '14990', 'sale_price' => '', 'sku_base' => 'DEMO-SMART',
				'brand' => 'ТехПульс',
					'type' => 'simple', 'featured' => true,
				),
				array(
					'title' => 'Портативная колонка', 'desc' => 'Bluetooth-колонка с защитой от воды IPX5.',
					'image' => $img(0), 'category' => 'Электроника', 'tags' => array('популярное','спорт'),
					'regular_price' => '4490', 'sale_price' => '', 'sku_base' => 'DEMO-SPEAK',
				'brand' => 'ТехПульс',
					'type' => 'simple',
				),
				array(
					'title' => 'Power Bank 20000 мАч', 'desc' => 'Ёмкий аккумулятор с быстрой зарядкой.',
					'image' => $img(1), 'category' => 'Электроника', 'tags' => array('хит'),
					'regular_price' => '3490', 'sale_price' => '2790', 'sku_base' => 'DEMO-POWER',
				'brand' => 'ТехПульс',
					'type' => 'simple',
				),
				array(
					'title' => 'Кабель USB-C 2м', 'desc' => 'Нейлоновый кабель с зарядкой 100 Вт.',
					'image' => $img(2), 'category' => 'Электроника', 'tags' => array('популярное'),
					'regular_price' => '790', 'sale_price' => '', 'sku_base' => 'DEMO-CABLE',
				'brand' => 'ТехПульс',
					'type' => 'simple',
				),
				// ── Спорт (5) ───────────────────────────────────────────
				array(
					'title' => 'Леггинсы спортивные', 'desc' => 'Компрессионные леггинсы для фитнеса.',
					'image' => $img(3), 'category' => 'Спорт', 'tags' => array('спорт','популярное'),
					'regular_price' => '2490', 'sale_price' => '', 'sku_base' => 'DEMO-LEGG',
				'brand' => 'СпортЛайн',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Чёрный','Синий','Серый'),
						'size'  => array('XS','S','M','L','XL'),
					),
				),
				array(
					'title' => 'Спортивная футболка Dry-Fit', 'desc' => 'Дышащая футболка для тренировок.',
					'image' => $img(4), 'category' => 'Спорт', 'tags' => array('спорт','новинка'),
					'regular_price' => '1590', 'sale_price' => '', 'sku_base' => 'DEMO-DRY',
				'brand' => 'СпортЛайн',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Белый','Синий','Красный'),
						'size'  => array('S','M','L','XL','XXL'),
					),
				),
				array(
					'title' => 'Бутылка для воды 750 мл', 'desc' => 'Термобутылка из нержавеющей стали.',
					'image' => $img(5), 'category' => 'Спорт', 'tags' => array('спорт','хит'),
					'regular_price' => '1290', 'sale_price' => '', 'sku_base' => 'DEMO-BOTTLE',
				'brand' => 'СпортЛайн',
					'type' => 'simple',
				),
				array(
					'title' => 'Коврик для йоги', 'desc' => 'Нескользящий коврик 180×60 см, толщина 6 мм.',
					'image' => $img(6), 'category' => 'Спорт', 'tags' => array('спорт','популярное'),
					'regular_price' => '1990', 'sale_price' => '1590', 'sku_base' => 'DEMO-YOGA',
				'brand' => 'СпортЛайн',
					'type' => 'simple',
				),
				array(
					'title' => 'Гантели разборные (пара)', 'desc' => 'Регулируемые гантели 2–10 кг.',
					'image' => $img(7), 'category' => 'Спорт', 'tags' => array('спорт','хит'),
					'regular_price' => '5990', 'sale_price' => '', 'sku_base' => 'DEMO-DUMB',
				'brand' => 'СпортЛайн',
					'type' => 'simple',
				),
				// ── Детское (5) ─────────────────────────────────────────
				array(
					'title' => 'Костюм детский спортивный', 'desc' => 'Комфортный костюм для активных детей.',
					'image' => $img(8), 'category' => 'Детская одежда', 'tags' => array('новинка','популярное'),
					'regular_price' => '2990', 'sale_price' => '', 'sku_base' => 'DEMO-KSUIT',
				'brand' => 'ДетскийМир',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Синий','Красный','Зелёный'),
						'size'  => array('92','98','104','110','116'),
					),
				),
				array(
					'title' => 'Кроссовки детские', 'desc' => 'Лёгкие кроссовки с застёжкой-липучкой.',
					'image' => $img(0), 'category' => 'Детская одежда', 'tags' => array('хит','новинка'),
					'regular_price' => '2490', 'sale_price' => '1990', 'sku_base' => 'DEMO-KSHOE',
				'brand' => 'ДетскийМир',
					'type' => 'variable',
					'attributes' => array(
						'color' => array('Белый','Синий'),
						'size'  => array('25','26','27','28','29','30'),
					),
				),
				array(
					'title' => 'Рюкзак школьный', 'desc' => 'Ортопедический рюкзак с USB-разъёмом.',
					'image' => $img(1), 'category' => 'Детское', 'tags' => array('популярное','подарок'),
					'regular_price' => '3490', 'sale_price' => '', 'sku_base' => 'DEMO-KBAG',
				'brand' => 'ДетскийМир',
					'type' => 'simple',
				),
				array(
					'title' => 'Набор пластилина (12 цветов)', 'desc' => 'Безопасный пластилин для лепки от 3 лет.',
					'image' => $img(2), 'category' => 'Детское', 'tags' => array('подарок'),
					'regular_price' => '490', 'sale_price' => '', 'sku_base' => 'DEMO-CLAY',
				'brand' => 'ДетскийМир',
					'type' => 'simple',
				),
				array(
					'title' => 'Конструктор деревянный', 'desc' => 'Набор из 100 деревянных деталей.',
					'image' => $img(3), 'category' => 'Детское', 'tags' => array('подарок','хит'),
					'regular_price' => '2190', 'sale_price' => '', 'sku_base' => 'DEMO-BUILD',
				'brand' => 'ДетскийМир',
					'type' => 'simple',
				),
				// ── Украшения (5) ───────────────────────────────────────
				array(
					'title' => 'Серьги-кольца золотые', 'desc' => 'Серьги-кольца из позолоченного серебра.',
					'image' => $img(4), 'category' => 'Украшения', 'tags' => array('премиум','подарок'),
					'regular_price' => '2990', 'sale_price' => '', 'sku_base' => 'DEMO-EARR',
				'brand' => 'ЮвелирАрт',
					'type' => 'variable',
					'attributes' => array(
						'material' => array('Золото','Серебро','Розовое золото'),
					),
					'featured' => true,
				),
				array(
					'title' => 'Браслет плетёный', 'desc' => 'Ручная работа, натуральный камень.',
					'image' => $img(5), 'category' => 'Украшения', 'tags' => array('лимитед','тренд'),
					'regular_price' => '1490', 'sale_price' => '', 'sku_base' => 'DEMO-BRAC',
				'brand' => 'ЮвелирАрт',
					'type' => 'variable',
					'attributes' => array(
						'color'    => array('Чёрный','Коричневый','Бежевый'),
						'material' => array('Кожа','Хлопок'),
					),
				),
				array(
					'title' => 'Кулон-сердце', 'desc' => 'Подвеска в форме сердца на цепочке 45 см.',
					'image' => $img(6), 'category' => 'Украшения', 'tags' => array('подарок','тренд'),
					'regular_price' => '1990', 'sale_price' => '1590', 'sku_base' => 'DEMO-PEND',
				'brand' => 'ЮвелирАрт',
					'type' => 'simple',
				),
				array(
					'title' => 'Кольцо с камнем', 'desc' => 'Серебряное кольцо с голубым топазом.',
					'image' => $img(7), 'category' => 'Украшения', 'tags' => array('премиум','лимитед'),
					'regular_price' => '5990', 'sale_price' => '', 'sku_base' => 'DEMO-RING',
				'brand' => 'ЮвелирАрт',
					'type' => 'simple',
				),
				array(
					'title' => 'Чокер бархатный', 'desc' => 'Чокер из бархата с металлической подвеской.',
					'image' => $img(8), 'category' => 'Украшения', 'tags' => array('новинка','тренд'),
					'regular_price' => '890', 'sale_price' => '', 'sku_base' => 'DEMO-CHOK',
				'brand' => 'ЮвелирАрт',
					'type' => 'simple',
				),
				// ── Книги (5) ───────────────────────────────────────────
				array(
					'title' => 'Маркетинг без бюджета', 'desc' => 'Практическое руководство по продвижению.',
					'image' => $img(0), 'category' => 'Книги', 'tags' => array('хит','популярное'),
					'regular_price' => '990', 'sale_price' => '790', 'sku_base' => 'DEMO-BOOK1',
				'brand' => 'КнижныйДом',
					'type' => 'simple',
				),
				array(
					'title' => 'Психология влияния', 'desc' => 'Роберт Чалдини. Классика о механизмах убеждения.',
					'image' => $img(1), 'category' => 'Книги', 'tags' => array('премиум','хит'),
					'regular_price' => '1190', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK2',
					'brand' => 'КнижныйДом',
					'type' => 'simple',
				),
				array(
					'title' => 'Дизайн привычных вещей', 'desc' => 'Норман Дон. Об интуитивно понятном дизайне.',
					'image' => $img(2), 'category' => 'Книги', 'tags' => array('новинка','тренд'),
					'regular_price' => '1490', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK3',
					'brand' => 'КнижныйДом',
					'type' => 'simple',
				),
				array(
					'title' => 'Чистый код', 'desc' => 'Роберт Мартин. Создание, анализ и рефакторинг.',
					'image' => $img(3), 'category' => 'Книги', 'tags' => array('популярное'),
					'regular_price' => '1590', 'sale_price' => '1290', 'sku_base' => 'DEMO-BOOK4',
					'brand' => 'КнижныйДом',
					'type' => 'simple',
				),
				array(
					'title' => 'Атомные привычки', 'desc' => 'Джеймс Клир. Маленькие изменения — большие результаты.',
					'image' => $img(4), 'category' => 'Книги', 'tags' => array('хит','популярное'),
					'regular_price' => '990', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK5',
					'brand' => 'КнижныйДом',
					'type' => 'simple',
				),
			),
		);
	}

	// ── English locale ──────────────────────────────────────────────────────
	return array(
		'categories' => array(
			// 3 levels: Clothing → Women's Clothing → Dresses
			array( 'name' => 'Clothing',           'parent' => '' ),
			array( 'name' => "Women's Clothing",   'parent' => 'Clothing' ),
			array( 'name' => 'Dresses',            'parent' => "Women's Clothing" ),
			array( 'name' => 'Outerwear',          'parent' => 'Clothing' ),
			// 2 levels: Footwear → Athletic / Classic
			array( 'name' => 'Footwear',           'parent' => '' ),
			array( 'name' => 'Athletic Footwear',  'parent' => 'Footwear' ),
			array( 'name' => 'Classic Footwear',   'parent' => 'Footwear' ),
			// 2 levels: Home & Garden → Textiles / Décor
			array( 'name' => 'Home & Garden',      'parent' => '' ),
			array( 'name' => 'Textiles',           'parent' => 'Home & Garden' ),
			array( 'name' => 'Décor',              'parent' => 'Home & Garden' ),
			// 2 levels: Kids → Kids' Clothing
			array( 'name' => 'Kids',               'parent' => '' ),
			array( 'name' => "Kids' Clothing",     'parent' => 'Kids' ),
			// Flat
			array( 'name' => 'Accessories',        'parent' => '' ),
			array( 'name' => 'Cosmetics',          'parent' => '' ),
			array( 'name' => 'Electronics',        'parent' => '' ),
			array( 'name' => 'Sports',             'parent' => '' ),
			array( 'name' => 'Jewelry',            'parent' => '' ),
			array( 'name' => 'Books',              'parent' => '' ),
		),
		'tags' => array(
			'new-arrival', 'popular', 'sale', 'bestseller', 'premium',
			'sport', 'gift-idea', 'limited', 'trending', 'seasonal',
		),
		'attributes' => array(
			'color' => array(
				'label'  => 'Color',
				'type'   => 'color',
				'values' => array( 'Red','Blue','Black','White','Green','Grey','Brown','Beige' ),
				'meta'   => array(
					'Red'   => array( 'color' => '#e74c3c' ),
					'Blue'  => array( 'color' => '#3498db' ),
					'Black' => array( 'color' => '#1a1a1a' ),
					'White' => array( 'color' => '#f8f9fa' ),
					'Green' => array( 'color' => '#2ecc71' ),
					'Grey'  => array( 'color' => '#7f8c8d' ),
					'Brown' => array( 'color' => '#8b4513' ),
					'Beige' => array( 'color' => '#d2b48c' ),
				),
			),
			'size' => array(
				'label'  => 'Size',
				'type'   => 'button',
				'values' => array( 'XS','S','M','L','XL','XXL' ),
			),
			'material' => array(
				'label'  => 'Material',
				'type'   => 'image',
				'values' => array( 'Cotton','Wool','Polyester','Leather','Linen' ),
				'meta'   => array(
					'Cotton'     => array( 'image' => 'sh5.jpg' ),
					'Wool'       => array( 'image' => 'sh6.jpg' ),
					'Polyester'  => array( 'image' => 'sh7.jpg' ),
					'Leather'    => array( 'image' => 'sh8.jpg' ),
					'Linen'      => array( 'image' => 'sh9.jpg' ),
					'Gold'       => array( 'image' => 'sh1.jpg' ),
					'Silver'     => array( 'image' => 'sh2.jpg' ),
					'Rose Gold'  => array( 'image' => 'sh3.jpg' ),
				),
			),
			'print' => array(
				'label'  => 'Print',
				'type'   => 'select',
				'values' => array( 'Stripes','Plaid','Floral','Abstract' ),
			),
		),
		'brands' => array(
			array( 'name' => 'UrbanFit',        'logo' => 'c1.png' ),
			array( 'name' => 'Nordic',          'logo' => 'c2.png' ),
			array( 'name' => 'NaturWear',       'logo' => 'c3.png' ),
			array( 'name' => 'SportLine',       'logo' => 'c4.png' ),
			array( 'name' => 'JewelArt',        'logo' => 'c5.png' ),
			array( 'name' => 'BeautyEssence',   'logo' => 'c6.png' ),
			array( 'name' => 'HomeDecor',       'logo' => 'c7.png' ),
			array( 'name' => 'TechPulse',       'logo' => 'c8.png' ),
			array( 'name' => 'KidsWorld',       'logo' => 'c9.png' ),
			array( 'name' => 'BookHouse',       'logo' => 'c10.png' ),
		),
		'items' => array(
			array(
				'title' => 'Basic T-Shirt', 'desc' => 'Classic 100% cotton t-shirt.',
				'image' => $img(0), 'category' => "Women's Clothing", 'tags' => array('popular','new-arrival'),
				'regular_price' => '19.99', 'sale_price' => '', 'sku_base' => 'DEMO-TSHIRT',
				'brand' => 'UrbanFit',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('White','Black','Blue'),
					'size'  => array('S','M','L','XL'),
					'print' => array('Stripes','Abstract'),
				),
			),
			array(
				'title' => 'Classic Jeans', 'desc' => 'Straight-cut jeans from heavy denim.',
				'image' => $img(1), 'category' => "Women's Clothing", 'tags' => array('bestseller','popular'),
				'regular_price' => '59.99', 'sale_price' => '44.99', 'sku_base' => 'DEMO-JEANS',
				'brand' => 'Nordic',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('Blue','Black'),
					'size'  => array('S','M','L','XL','XXL'),
				),
			),
			array(
				'title' => 'Summer Dress', 'desc' => 'Light linen dress for warm days.',
				'image' => $img(2), 'category' => 'Dresses', 'tags' => array('new-arrival','trending'),
				'regular_price' => '69.99', 'sale_price' => '49.99', 'sku_base' => 'DEMO-DRESS',
				'brand' => 'NaturWear',
				'type' => 'variable',
				'attributes' => array(
					'color'    => array('White','Green','Red'),
					'material' => array('Linen','Cotton'),
					'print'    => array('Floral','Plaid'),
				),
			),
			array(
				'title' => 'Autumn Jacket', 'desc' => 'Water-repellent jacket for spring and autumn.',
				'image' => $img(3), 'category' => 'Outerwear', 'tags' => array('bestseller','seasonal'),
				'regular_price' => '189.00', 'sale_price' => '', 'sku_base' => 'DEMO-JACKET',
				'brand' => 'UrbanFit',
				'type' => 'simple', 'featured' => true,
			),
			array(
				'title' => 'Wool Sweater', 'desc' => 'Warm sweater from natural wool.',
				'image' => $img(4), 'category' => "Women's Clothing", 'tags' => array('premium','seasonal'),
				'regular_price' => '89.00', 'sale_price' => '', 'sku_base' => 'DEMO-SWEATER',
				'brand' => 'Nordic',
				'type' => 'simple',
			),
			array(
				'title' => 'Running Shoes', 'desc' => 'Lightweight running shoes with cushioned sole.',
				'image' => $img(5), 'category' => 'Athletic Footwear', 'tags' => array('sport','popular'),
				'regular_price' => '120.00', 'sale_price' => '89.00', 'sku_base' => 'DEMO-RUN',
				'brand' => 'SportLine',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('White','Black','Blue'),
					'size'  => array('38','39','40','41','42','43'),
				),
			),
			array(
				'title' => 'Casual Sneakers', 'desc' => 'Comfortable everyday sneakers.',
				'image' => $img(6), 'category' => 'Athletic Footwear', 'tags' => array('popular','sale'),
				'regular_price' => '79.00', 'sale_price' => '59.00', 'sku_base' => 'DEMO-SNEAK',
				'brand' => 'SportLine',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('White','Black','Red'),
					'size'  => array('37','38','39','40','41'),
				),
			),
			array(
				'title' => 'Leather Boots', 'desc' => 'Classic boots from genuine leather.',
				'image' => $img(7), 'category' => 'Classic Footwear', 'tags' => array('premium','bestseller'),
				'regular_price' => '149.00', 'sale_price' => '', 'sku_base' => 'DEMO-BOOT',
				'brand' => 'NaturWear',
				'type' => 'simple', 'featured' => true,
			),
			array(
				'title' => 'Summer Sandals', 'desc' => 'Open sandals for beach and city.',
				'image' => $img(8), 'category' => 'Classic Footwear', 'tags' => array('new-arrival','seasonal'),
				'regular_price' => '49.00', 'sale_price' => '35.00', 'sku_base' => 'DEMO-SAND',
				'brand' => 'SportLine',
				'type' => 'simple',
			),
			array(
				'title' => 'Suede Moccasins', 'desc' => 'Soft suede moccasins for everyday wear.',
				'image' => $img(0), 'category' => 'Classic Footwear', 'tags' => array('trending'),
				'regular_price' => '99.00', 'sale_price' => '', 'sku_base' => 'DEMO-MOC',
				'brand' => 'NaturWear',
				'type' => 'simple',
			),
			array(
				'title' => 'Leather Bag', 'desc' => 'Stylish handcrafted leather bag.',
				'image' => $img(1), 'category' => 'Accessories', 'tags' => array('premium','bestseller'),
				'regular_price' => '149.99', 'sale_price' => '', 'sku_base' => 'DEMO-BAG',
				'brand' => 'UrbanFit',
				'type' => 'variable',
				'attributes' => array(
					'color'    => array('Black','Brown','Beige'),
					'material' => array('Leather'),
				),
				'featured' => true,
			),
			array(
				'title' => 'Sunglasses', 'desc' => 'UV400 sunglasses in metal frame.',
				'image' => $img(2), 'category' => 'Accessories', 'tags' => array('trending','limited'),
				'regular_price' => '79.00', 'sale_price' => '', 'sku_base' => 'DEMO-GLASS',
				'brand' => 'UrbanFit',
				'type' => 'simple',
			),
			array(
				'title' => 'Wrist Watch', 'desc' => 'Elegant watch with leather strap.',
				'image' => $img(3), 'category' => 'Accessories', 'tags' => array('premium','gift-idea'),
				'regular_price' => '249.00', 'sale_price' => '199.00', 'sku_base' => 'DEMO-WATCH',
				'brand' => 'JewelArt',
				'type' => 'simple', 'featured' => true,
			),
			array(
				'title' => 'Cashmere Scarf', 'desc' => '100% cashmere scarf.',
				'image' => $img(4), 'category' => 'Accessories', 'tags' => array('premium','seasonal'),
				'regular_price' => '59.00', 'sale_price' => '', 'sku_base' => 'DEMO-SCARF',
				'brand' => 'Nordic',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('Grey','Blue','Beige'),
				),
			),
			array(
				'title' => 'Leather Belt', 'desc' => 'Classic leather belt with metal buckle.',
				'image' => $img(5), 'category' => 'Accessories', 'tags' => array('bestseller'),
				'regular_price' => '39.00', 'sale_price' => '', 'sku_base' => 'DEMO-BELT',
				'brand' => 'NaturWear',
				'type' => 'simple',
			),
			array(
				'title' => 'Women\'s Perfume', 'desc' => 'Fragrance with notes of bergamot and white flowers.',
				'image' => $img(6), 'category' => 'Cosmetics', 'tags' => array('premium','gift-idea'),
				'regular_price' => '75.00', 'sale_price' => '', 'sku_base' => 'DEMO-PERF-W',
				'brand' => 'BeautyEssence',
				'type' => 'simple', 'featured' => true,
			),
			array(
				'title' => 'Skincare Gift Set', 'desc' => 'Cream, serum and toner in gift box.',
				'image' => $img(7), 'category' => 'Cosmetics', 'tags' => array('gift-idea','bestseller','new-arrival'),
				'regular_price' => '99.00', 'sale_price' => '', 'sku_base' => 'DEMO-COSM',
				'brand' => 'BeautyEssence',
				'type' => 'simple',
			),
			array(
				'title' => 'SPF30 Moisturiser', 'desc' => 'Day cream with UV protection.',
				'image' => $img(8), 'category' => 'Cosmetics', 'tags' => array('popular'),
				'regular_price' => '29.99', 'sale_price' => '24.99', 'sku_base' => 'DEMO-CREAM',
				'brand' => 'BeautyEssence',
				'type' => 'simple',
			),
			array(
				'title' => 'Volumising Mascara', 'desc' => 'Mascara with false-lash effect.',
				'image' => $img(0), 'category' => 'Cosmetics', 'tags' => array('trending','new-arrival'),
				'regular_price' => '14.99', 'sale_price' => '', 'sku_base' => 'DEMO-MASCARA',
				'brand' => 'BeautyEssence',
				'type' => 'simple',
			),
			array(
				'title' => 'Long-Lasting Lipstick', 'desc' => '24-hour wear without drying.',
				'image' => $img(1), 'category' => 'Cosmetics', 'tags' => array('bestseller','sale'),
				'regular_price' => '12.99', 'sale_price' => '9.99', 'sku_base' => 'DEMO-LIPSTICK',
				'brand' => 'BeautyEssence',
				'type' => 'simple',
			),
			array(
				'title' => 'Fleece Blanket', 'desc' => 'Soft fleece blanket 150×200 cm.',
				'image' => $img(2), 'category' => 'Textiles', 'tags' => array('popular','seasonal'),
				'regular_price' => '39.00', 'sale_price' => '', 'sku_base' => 'DEMO-BLANKET',
				'brand' => 'HomeDecor',
				'type' => 'variable',
				'attributes' => array(
					'color'    => array('Grey','Beige','Blue'),
					'material' => array('Polyester','Cotton'),
				),
			),
			array(
				'title' => 'Kitchen Towel Set', 'desc' => '6 cotton towels in gift wrapping.',
				'image' => $img(3), 'category' => 'Textiles', 'tags' => array('gift-idea','new-arrival'),
				'regular_price' => '19.99', 'sale_price' => '', 'sku_base' => 'DEMO-TOWEL',
				'brand' => 'HomeDecor',
				'type' => 'simple',
			),
			array(
				'title' => 'Decorative Vase', 'desc' => 'Handmade ceramic vase.',
				'image' => $img(4), 'category' => 'Decor', 'tags' => array('trending','limited'),
				'regular_price' => '54.00', 'sale_price' => '', 'sku_base' => 'DEMO-VASE',
				'brand' => 'HomeDecor',
				'type' => 'simple',
			),
			array(
				'title' => 'Scented Candle Set', 'desc' => 'Set of 3 candles with different scents.',
				'image' => $img(5), 'category' => 'Decor', 'tags' => array('gift-idea','bestseller'),
				'regular_price' => '29.00', 'sale_price' => '24.00', 'sku_base' => 'DEMO-CANDLE',
				'brand' => 'HomeDecor',
				'type' => 'simple',
			),
			array(
				'title' => 'Photo Frame', 'desc' => 'Wooden frame 20×30 in Scandinavian style.',
				'image' => $img(6), 'category' => 'Decor', 'tags' => array('new-arrival'),
				'regular_price' => '15.99', 'sale_price' => '', 'sku_base' => 'DEMO-FRAME',
				'brand' => 'HomeDecor',
				'type' => 'simple',
			),
			array(
				'title' => 'Wireless Headphones', 'desc' => 'Over-ear Bluetooth 5.0 headphones, 30h battery.',
				'image' => $img(7), 'category' => 'Electronics', 'tags' => array('bestseller','popular'),
				'regular_price' => '119.00', 'sale_price' => '89.00', 'sku_base' => 'DEMO-HEADP',
				'brand' => 'TechPulse',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('Black','White'),
				),
				'featured' => true,
			),
			array(
				'title' => 'Smart Watch', 'desc' => 'Smartwatch with health monitoring and GPS.',
				'image' => $img(8), 'category' => 'Electronics', 'tags' => array('new-arrival','trending'),
				'regular_price' => '229.00', 'sale_price' => '', 'sku_base' => 'DEMO-SMART',
				'brand' => 'TechPulse',
				'type' => 'simple', 'featured' => true,
			),
			array(
				'title' => 'Portable Speaker', 'desc' => 'Bluetooth speaker with IPX5 water resistance.',
				'image' => $img(0), 'category' => 'Electronics', 'tags' => array('popular','sport'),
				'regular_price' => '69.00', 'sale_price' => '', 'sku_base' => 'DEMO-SPEAK',
				'brand' => 'TechPulse',
				'type' => 'simple',
			),
			array(
				'title' => 'Power Bank 20000 mAh', 'desc' => 'High-capacity battery with fast charging.',
				'image' => $img(1), 'category' => 'Electronics', 'tags' => array('bestseller'),
				'regular_price' => '54.00', 'sale_price' => '44.00', 'sku_base' => 'DEMO-POWER',
				'brand' => 'TechPulse',
				'type' => 'simple',
			),
			array(
				'title' => 'USB-C Cable 2m', 'desc' => 'Braided cable with 100W fast charging.',
				'image' => $img(2), 'category' => 'Electronics', 'tags' => array('popular'),
				'regular_price' => '12.99', 'sale_price' => '', 'sku_base' => 'DEMO-CABLE',
				'brand' => 'TechPulse',
				'type' => 'simple',
			),
			array(
				'title' => 'Sports Leggings', 'desc' => 'Compression leggings for fitness.',
				'image' => $img(3), 'category' => 'Sports', 'tags' => array('sport','popular'),
				'regular_price' => '39.00', 'sale_price' => '', 'sku_base' => 'DEMO-LEGG',
				'brand' => 'SportLine',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('Black','Blue','Grey'),
					'size'  => array('XS','S','M','L','XL'),
				),
			),
			array(
				'title' => 'Dry-Fit T-Shirt', 'desc' => 'Breathable training t-shirt.',
				'image' => $img(4), 'category' => 'Sports', 'tags' => array('sport','new-arrival'),
				'regular_price' => '24.99', 'sale_price' => '', 'sku_base' => 'DEMO-DRY',
				'brand' => 'SportLine',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('White','Blue','Red'),
					'size'  => array('S','M','L','XL','XXL'),
				),
			),
			array(
				'title' => 'Water Bottle 750ml', 'desc' => 'Stainless steel insulated bottle.',
				'image' => $img(5), 'category' => 'Sports', 'tags' => array('sport','bestseller'),
				'regular_price' => '19.99', 'sale_price' => '', 'sku_base' => 'DEMO-BOTTLE',
				'brand' => 'SportLine',
				'type' => 'simple',
			),
			array(
				'title' => 'Yoga Mat', 'desc' => 'Non-slip mat 180×60 cm, 6mm thick.',
				'image' => $img(6), 'category' => 'Sports', 'tags' => array('sport','popular'),
				'regular_price' => '29.99', 'sale_price' => '24.99', 'sku_base' => 'DEMO-YOGA',
				'brand' => 'SportLine',
				'type' => 'simple',
			),
			array(
				'title' => 'Adjustable Dumbbells (pair)', 'desc' => 'Adjustable dumbbells 2–10 kg.',
				'image' => $img(7), 'category' => 'Sports', 'tags' => array('sport','bestseller'),
				'regular_price' => '89.00', 'sale_price' => '', 'sku_base' => 'DEMO-DUMB',
				'brand' => 'SportLine',
				'type' => 'simple',
			),
			array(
				'title' => 'Kids Tracksuit', 'desc' => 'Comfortable tracksuit for active kids.',
				'image' => $img(8), 'category' => "Kids' Clothing", 'tags' => array('new-arrival','popular'),
				'regular_price' => '44.99', 'sale_price' => '', 'sku_base' => 'DEMO-KSUIT',
				'brand' => 'KidsWorld',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('Blue','Red','Green'),
					'size'  => array('92','98','104','110','116'),
				),
			),
			array(
				'title' => 'Kids Sneakers', 'desc' => 'Lightweight sneakers with velcro strap.',
				'image' => $img(0), 'category' => "Kids' Clothing", 'tags' => array('bestseller','new-arrival'),
				'regular_price' => '39.00', 'sale_price' => '32.00', 'sku_base' => 'DEMO-KSHOE',
				'brand' => 'KidsWorld',
				'type' => 'variable',
				'attributes' => array(
					'color' => array('White','Blue'),
					'size'  => array('25','26','27','28','29','30'),
				),
			),
			array(
				'title' => 'School Backpack', 'desc' => 'Ergonomic backpack with USB port.',
				'image' => $img(1), 'category' => 'Kids', 'tags' => array('popular','gift-idea'),
				'regular_price' => '54.00', 'sale_price' => '', 'sku_base' => 'DEMO-KBAG',
				'brand' => 'KidsWorld',
				'type' => 'simple',
			),
			array(
				'title' => 'Plasticine Set (12 colours)', 'desc' => 'Safe modelling clay for ages 3+.',
				'image' => $img(2), 'category' => 'Kids', 'tags' => array('gift-idea'),
				'regular_price' => '7.99', 'sale_price' => '', 'sku_base' => 'DEMO-CLAY',
				'brand' => 'KidsWorld',
				'type' => 'simple',
			),
			array(
				'title' => 'Wooden Building Set', 'desc' => 'Set of 100 wooden pieces.',
				'image' => $img(3), 'category' => 'Kids', 'tags' => array('gift-idea','bestseller'),
				'regular_price' => '34.99', 'sale_price' => '', 'sku_base' => 'DEMO-BUILD',
				'brand' => 'KidsWorld',
				'type' => 'simple',
			),
			array(
				'title' => 'Gold Hoop Earrings', 'desc' => 'Gold-plated silver hoop earrings.',
				'image' => $img(4), 'category' => 'Jewelry', 'tags' => array('premium','gift-idea'),
				'regular_price' => '44.99', 'sale_price' => '', 'sku_base' => 'DEMO-EARR',
				'brand' => 'JewelArt',
				'type' => 'variable',
				'attributes' => array(
					'material' => array('Gold','Silver','Rose Gold'),
				),
				'featured' => true,
			),
			array(
				'title' => 'Woven Bracelet', 'desc' => 'Handmade bracelet with natural stone.',
				'image' => $img(5), 'category' => 'Jewelry', 'tags' => array('limited','trending'),
				'regular_price' => '22.99', 'sale_price' => '', 'sku_base' => 'DEMO-BRAC',
				'brand' => 'JewelArt',
				'type' => 'variable',
				'attributes' => array(
					'color'    => array('Black','Brown','Beige'),
					'material' => array('Leather','Cotton'),
				),
			),
			array(
				'title' => 'Heart Pendant', 'desc' => 'Heart pendant on a 45 cm chain.',
				'image' => $img(6), 'category' => 'Jewelry', 'tags' => array('gift-idea','trending'),
				'regular_price' => '29.99', 'sale_price' => '24.99', 'sku_base' => 'DEMO-PEND',
				'brand' => 'JewelArt',
				'type' => 'simple',
			),
			array(
				'title' => 'Gemstone Ring', 'desc' => 'Silver ring with blue topaz.',
				'image' => $img(7), 'category' => 'Jewelry', 'tags' => array('premium','limited'),
				'regular_price' => '89.00', 'sale_price' => '', 'sku_base' => 'DEMO-RING',
				'brand' => 'JewelArt',
				'type' => 'simple',
			),
			array(
				'title' => 'Velvet Choker', 'desc' => 'Velvet choker with metal charm.',
				'image' => $img(8), 'category' => 'Jewelry', 'tags' => array('new-arrival','trending'),
				'regular_price' => '13.99', 'sale_price' => '', 'sku_base' => 'DEMO-CHOK',
				'brand' => 'JewelArt',
				'type' => 'simple',
			),
			array(
				'title' => 'Marketing on a Budget', 'desc' => 'Practical guide to zero-budget promotion.',
				'image' => $img(0), 'category' => 'Books', 'tags' => array('bestseller','popular'),
				'regular_price' => '14.99', 'sale_price' => '11.99', 'sku_base' => 'DEMO-BOOK1',
				'brand' => 'BookHouse',
				'type' => 'simple',
			),
			array(
				'title' => 'Influence', 'desc' => 'Robert Cialdini. The psychology of persuasion.',
				'image' => $img(1), 'category' => 'Books', 'tags' => array('premium','bestseller'),
				'regular_price' => '17.99', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK2',
				'brand' => 'BookHouse',
				'type' => 'simple',
			),
			array(
				'title' => 'The Design of Everyday Things', 'desc' => 'Don Norman on intuitive design.',
				'image' => $img(2), 'category' => 'Books', 'tags' => array('new-arrival','trending'),
				'regular_price' => '22.99', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK3',
				'brand' => 'BookHouse',
				'type' => 'simple',
			),
			array(
				'title' => 'Clean Code', 'desc' => 'Robert Martin. Creating, analysing, refactoring.',
				'image' => $img(3), 'category' => 'Books', 'tags' => array('popular'),
				'regular_price' => '24.99', 'sale_price' => '19.99', 'sku_base' => 'DEMO-BOOK4',
				'brand' => 'BookHouse',
				'type' => 'simple',
			),
			array(
				'title' => 'Atomic Habits', 'desc' => 'James Clear. Small changes, remarkable results.',
				'image' => $img(4), 'category' => 'Books', 'tags' => array('bestseller','popular'),
				'regular_price' => '14.99', 'sale_price' => '', 'sku_base' => 'DEMO-BOOK5',
				'brand' => 'BookHouse',
				'type' => 'simple',
			),
		),
	);
}

/**
 * Создать или получить глобальный атрибут WooCommerce.
 * Возвращает название таксономии (например, 'pa_color').
 *
 * @param string $label Отображаемое название.
 * @param string $slug  Slug атрибута (без префикса pa_).
 * @return string|false
 */
function cw_demo_ensure_wc_attribute( $label, $slug, $type = 'select' ) {
	$allowed_types = array( 'select', 'button', 'color', 'image' );
	$type          = in_array( $type, $allowed_types, true ) ? $type : 'select';

	if ( ! function_exists( 'wc_create_attribute' ) ) {
		return false;
	}

	$taxonomy = 'pa_' . $slug;

	// Уже существует?
	$existing = wc_get_attribute_taxonomies();
	foreach ( $existing as $attr ) {
		if ( $attr->attribute_name === $slug ) {
			// Обновляем тип если он отличается
			if ( $attr->attribute_type !== $type && function_exists( 'wc_update_attribute' ) ) {
				wc_update_attribute( $attr->attribute_id, array(
					'name'         => $attr->attribute_label,
					'slug'         => $slug,
					'type'         => $type,
					'order_by'     => $attr->attribute_orderby,
					'has_archives' => (bool) $attr->attribute_public,
				) );
				delete_transient( 'wc_attribute_taxonomies' );
			}
			return $taxonomy;
		}
	}

	// Создаём новый атрибут
	$id = wc_create_attribute( array(
		'name'         => $label,
		'slug'         => $slug,
		'type'         => $type,
		'order_by'     => 'menu_order',
		'has_archives' => false,
	) );

	if ( is_wp_error( $id ) ) {
		return false;
	}

	// Регистрируем таксономию в текущем запросе
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			array( 'product', 'product_variation' ),
			array(
				'hierarchical'      => false,
				'show_ui'           => false,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $slug ),
			)
		);
	}

	delete_transient( 'wc_attribute_taxonomies' );

	return $taxonomy;
}

/**
 * Получить или создать термин в таксономии атрибута и задать мета-данные (цвет / изображение).
 *
 * @param string $value     Значение (название термина).
 * @param string $taxonomy  Таксономия (pa_*).
 * @param string $attr_type Тип атрибута: 'color', 'image', 'button', 'select'.
 * @param array  $meta      Мета-данные: ['color' => '#hex'] или ['image' => 'sh1.jpg'].
 * @return string slug термина или пустая строка при ошибке.
 */
function cw_demo_ensure_attribute_term( $value, $taxonomy, $attr_type = 'select', $meta = array() ) {
	$slug = sanitize_title( $value );
	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( $term ) {
		$term_id = $term->term_id;
	} else {
		$result = wp_insert_term( $value, $taxonomy, array( 'slug' => $slug ) );
		if ( is_wp_error( $result ) ) {
			return '';
		}
		$term_id = $result['term_id'];
	}

	// Цвет атрибута — устанавливаем всегда (перезаписываем демо-данными)
	if ( 'color' === $attr_type ) {
		$hex = $meta['color'] ?? cw_demo_get_color_hex( $value );
		if ( $hex ) {
			update_term_meta( $term_id, 'product_attribute_color', $hex );
		}
	}

	// Изображение атрибута — устанавливаем если ещё не задано
	if ( ! empty( $meta['image'] ) && ! get_term_meta( $term_id, 'thumbnail_id', true ) ) {
		cw_demo_set_category_image( $meta['image'], $term_id );
	}

	return $slug;
}

/**
 * Fallback hex colour for a named colour term (covers RU + EN names).
 *
 * @param string $name Term label.
 * @return string Hex colour or empty string if unknown.
 */
function cw_demo_get_color_hex( $name ) {
	$map = array(
		'красный'    => '#e74c3c', 'red'    => '#e74c3c',
		'синий'      => '#3498db', 'blue'   => '#3498db',
		'чёрный'     => '#1a1a1a', 'black'  => '#1a1a1a',
		'белый'      => '#f8f9fa', 'white'  => '#f8f9fa',
		'зелёный'    => '#2ecc71', 'green'  => '#2ecc71',
		'серый'      => '#7f8c8d', 'grey'   => '#7f8c8d', 'gray' => '#7f8c8d',
		'коричневый' => '#8b4513', 'brown'  => '#8b4513',
		'бежевый'    => '#d2b48c', 'beige'  => '#d2b48c',
		'розовый'    => '#ff69b4', 'pink'   => '#ff69b4',
		'фиолетовый' => '#9b59b6', 'purple' => '#9b59b6',
		'оранжевый'  => '#e67e22', 'orange' => '#e67e22',
		'жёлтый'     => '#f1c40f', 'yellow' => '#f1c40f',
	);
	return $map[ mb_strtolower( $name ) ] ?? '';
}

/**
 * Создать бренды из массива данных и вернуть map[name => term_id].
 *
 * @param array $brands Массив array('name' => ..., 'logo' => ...).
 * @return array
 */
function cw_demo_create_brands( $brands ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$name_to_id = array();

	foreach ( $brands as $brand ) {
		$term_id = cw_demo_get_or_create_product_term( $brand['name'], 'product_brand' );
		if ( $term_id ) {
			$name_to_id[ $brand['name'] ] = $term_id;

			// Логотип бренда из src/assets/img/brands/
			if ( ! empty( $brand['logo'] ) && ! get_term_meta( $term_id, 'thumbnail_id', true ) ) {
				cw_demo_set_brand_image( $brand['logo'], $term_id );
			}
		}
	}

	return $name_to_id;
}

/**
 * Импортировать логотип бренда в медиабиблиотеку и присвоить термину.
 *
 * @param string $logo_filename Имя файла из src/assets/img/brands/.
 * @param int    $term_id       ID термина бренда.
 * @return int|false ID attachment или false при ошибке.
 */
function cw_demo_set_brand_image( $logo_filename, $term_id ) {
	$source_path = get_template_directory() . '/src/assets/img/brands/' . $logo_filename;
	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	$file_type = wp_check_filetype( basename( $source_path ), null );
	if ( ! $file_type['type'] ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	$dest_path  = $upload_dir['path'] . '/brand-' . $term_id . '-' . basename( $source_path );

	if ( ! copy( $source_path, $dest_path ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		array( 'name' => basename( $source_path ), 'tmp_name' => $dest_path ),
		0
	);

	if ( file_exists( $dest_path ) ) {
		@unlink( $dest_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	if ( is_wp_error( $attachment_id ) ) {
		return false;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) ) );
	update_term_meta( $term_id, 'thumbnail_id', $attachment_id );

	return $attachment_id;
}

/**
 * Импортировать изображение товара в медиабиблиотеку.
 *
 * @param string $image_filename Имя файла из src/assets/img/photos/.
 * @param int    $post_id        ID записи товара.
 * @return int|false ID attachment или false при ошибке.
 */
function cw_demo_import_product_image( $image_filename, $post_id ) {
	$source_path = get_template_directory() . '/src/assets/img/photos/' . $image_filename;

	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	$file_type = wp_check_filetype( basename( $source_path ), null );
	if ( ! $file_type['type'] ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	$dest_path  = $upload_dir['path'] . '/' . basename( $source_path );

	if ( ! copy( $source_path, $dest_path ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		array( 'name' => basename( $source_path ), 'tmp_name' => $dest_path ),
		$post_id
	);

	if ( file_exists( $dest_path ) ) {
		@unlink( $dest_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	if ( is_wp_error( $attachment_id ) ) {
		return false;
	}

	wp_update_post( array( 'ID' => $attachment_id, 'post_parent' => $post_id ) );
	set_post_thumbnail( $post_id, $attachment_id );
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) ) );

	return $attachment_id;
}

/**
 * Получить или создать термин таксономии.
 *
 * @param string $name      Название.
 * @param string $taxonomy  Таксономия.
 * @param int    $parent_id ID родительского термина (0 = корень).
 * @return int|false
 */
function cw_demo_get_or_create_product_term( $name, $taxonomy, $parent_id = 0 ) {
	// Поиск с учётом родителя, чтобы не перепутать одноимённые категории разных уровней
	$search_args = array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 0 );
	if ( $parent_id ) {
		$search_args['parent'] = $parent_id;
	}
	$terms = get_terms( $search_args );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( $term->name === $name ) {
				return $term->term_id;
			}
		}
	}

	$insert_args = array( 'slug' => sanitize_title( $name ) );
	if ( $parent_id ) {
		$insert_args['parent'] = $parent_id;
	}

	$result = wp_insert_term( $name, $taxonomy, $insert_args );
	if ( is_wp_error( $result ) ) {
		return false;
	}

	return $result['term_id'];
}

/**
 * Создать иерархические категории товаров и вернуть map[name => term_id].
 *
 * @param array $categories Массив array('name' => ..., 'parent' => ...).
 * @return array
 */
function cw_demo_create_categories( $categories ) {
	$name_to_id = array();
	$shop_imgs   = array( 'sh1.jpg','sh2.jpg','sh3.jpg','sh4.jpg','sh5.jpg','sh6.jpg','sh7.jpg','sh8.jpg','sh9.jpg' );
	$img_index   = 0;

	foreach ( $categories as $cat ) {
		$parent_id = 0;
		if ( ! empty( $cat['parent'] ) && isset( $name_to_id[ $cat['parent'] ] ) ) {
			$parent_id = $name_to_id[ $cat['parent'] ];
		}

		$term_id = cw_demo_get_or_create_product_term( $cat['name'], 'product_cat', $parent_id );
		if ( $term_id ) {
			$name_to_id[ $cat['name'] ] = $term_id;

			// Назначить картинку если ещё не назначена
			$image_file = $cat['image'] ?? $shop_imgs[ $img_index % count( $shop_imgs ) ];
			cw_demo_set_category_image( $image_file, $term_id );
		}

		$img_index++;
	}

	return $name_to_id;
}

/**
 * Импортировать изображение в медиабиблиотеку и присвоить категории товара.
 *
 * @param string $image_filename Имя файла из src/assets/img/photos/.
 * @param int    $term_id        ID категории.
 * @return int|false ID attachment или false при ошибке.
 */
function cw_demo_set_category_image( $image_filename, $term_id ) {
	// Пропустить если изображение уже назначено
	if ( get_term_meta( $term_id, 'thumbnail_id', true ) ) {
		return true;
	}

	$source_path = get_template_directory() . '/src/assets/img/photos/' . $image_filename;
	if ( ! file_exists( $source_path ) ) {
		return false;
	}

	$file_type = wp_check_filetype( basename( $source_path ), null );
	if ( ! $file_type['type'] ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	$dest_path  = $upload_dir['path'] . '/' . 'cat-' . $term_id . '-' . basename( $source_path );

	if ( ! copy( $source_path, $dest_path ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		array( 'name' => basename( $source_path ), 'tmp_name' => $dest_path ),
		0
	);

	if ( file_exists( $dest_path ) ) {
		@unlink( $dest_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	if ( is_wp_error( $attachment_id ) ) {
		return false;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) ) );
	update_term_meta( $term_id, 'thumbnail_id', $attachment_id );

	return $attachment_id;
}

/**
 * Создать один простой demo товар.
 *
 * @param array  $item    Данные товара.
 * @param int    $cat_id  ID категории.
 * @param array  $tag_ids Массив ID тегов.
 * @return int|false
 */
function cw_demo_create_simple_product( $item, $cat_id, $tag_ids ) {
	$product = new WC_Product_Simple();
	$product->set_name( sanitize_text_field( $item['title'] ) );
	$product->set_description( wp_kses_post( $item['desc'] ?? '' ) );
	$product->set_status( 'publish' );
	$product->set_regular_price( sanitize_text_field( $item['regular_price'] ?? '' ) );
	if ( ! empty( $item['sale_price'] ) ) {
		$product->set_sale_price( sanitize_text_field( $item['sale_price'] ) );
	}
	if ( ! empty( $item['sku_base'] ) ) {
		$product->set_sku( sanitize_text_field( $item['sku_base'] ) );
	}
	$product->set_stock_status( 'instock' );
	if ( ! empty( $item['featured'] ) ) {
		$product->set_featured( true );
	}
	if ( $cat_id ) {
		$product->set_category_ids( array( $cat_id ) );
	}
	if ( $tag_ids ) {
		$product->set_tag_ids( $tag_ids );
	}

	$post_id = $product->save();
	if ( ! $post_id ) {
		return false;
	}

	update_post_meta( $post_id, '_demo_created', true );

	if ( ! empty( $item['image'] ) ) {
		cw_demo_import_product_image( $item['image'], $post_id );
	}

	wc_delete_product_transients( $post_id );

	return $post_id;
}

/**
 * Создать вариативный demo товар с атрибутами и вариациями.
 *
 * @param array  $item        Данные товара.
 * @param int    $cat_id      ID категории.
 * @param array  $tag_ids     Массив ID тегов.
 * @param array  $attr_config Конфиг атрибутов (label, type, values, meta).
 * @return int|false
 */
function cw_demo_create_variable_product( $item, $cat_id, $tag_ids, $attr_config ) {
	$product = new WC_Product_Variable();
	$product->set_name( sanitize_text_field( $item['title'] ) );
	$product->set_description( wp_kses_post( $item['desc'] ?? '' ) );
	$product->set_status( 'publish' );
	if ( ! empty( $item['sku_base'] ) ) {
		$product->set_sku( sanitize_text_field( $item['sku_base'] ) );
	}
	$product->set_stock_status( 'instock' );
	if ( ! empty( $item['featured'] ) ) {
		$product->set_featured( true );
	}
	if ( $cat_id ) {
		$product->set_category_ids( array( $cat_id ) );
	}
	if ( $tag_ids ) {
		$product->set_tag_ids( $tag_ids );
	}

	// ── Атрибуты ────────────────────────────────────────────────────────
	$wc_attributes       = array();
	$variation_attr_data = array(); // taxonomy => [ slugs ]
	$position            = 0;

	foreach ( $item['attributes'] as $attr_slug => $values ) {
		$label     = $attr_config[ $attr_slug ]['label'] ?? ucfirst( $attr_slug );
		$attr_type = $attr_config[ $attr_slug ]['type']  ?? 'select';
		$attr_meta = $attr_config[ $attr_slug ]['meta']  ?? array();

		$taxonomy = cw_demo_ensure_wc_attribute( $label, $attr_slug, $attr_type );
		if ( ! $taxonomy ) {
			continue;
		}

		// ID глобального атрибута
		$attribute_id = 0;
		foreach ( wc_get_attribute_taxonomies() as $attr_tax ) {
			if ( 'pa_' . $attr_tax->attribute_name === $taxonomy ) {
				$attribute_id = (int) $attr_tax->attribute_id;
				break;
			}
		}

		// Создать/получить термины, собрать ID и slugs
		$term_ids   = array();
		$term_slugs = array();
		foreach ( $values as $value ) {
			$term_meta = $attr_meta[ $value ] ?? array();
			$slug      = cw_demo_ensure_attribute_term( $value, $taxonomy, $attr_type, $term_meta );
			if ( $slug ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( $term ) {
					$term_ids[]   = $term->term_id;
					$term_slugs[] = $slug;
				}
			}
		}

		$wc_attr = new WC_Product_Attribute();
		$wc_attr->set_id( $attribute_id );
		$wc_attr->set_name( $taxonomy );
		$wc_attr->set_options( $term_ids );
		$wc_attr->set_position( $position++ );
		$wc_attr->set_visible( true );
		$wc_attr->set_variation( true );
		$wc_attributes[]                  = $wc_attr;
		$variation_attr_data[ $taxonomy ] = $term_slugs;
	}

	$product->set_attributes( $wc_attributes );
	$post_id = $product->save();
	if ( ! $post_id ) {
		return false;
	}

	update_post_meta( $post_id, '_demo_created', true );

	// ── Вариации ─────────────────────────────────────────────────────────
	// Создаём вариации как произведение первых двух атрибутов
	$attr_keys     = array_keys( $variation_attr_data );
	$regular_price = sanitize_text_field( $item['regular_price'] ?? '' );
	$sale_price    = sanitize_text_field( $item['sale_price'] ?? '' );
	$sku_base      = sanitize_text_field( $item['sku_base'] ?? '' );
	$var_index     = 0;

	$first_attr    = $attr_keys[0] ?? null;
	$second_attr   = $attr_keys[1] ?? null;
	$first_values  = $first_attr ? $variation_attr_data[ $first_attr ] : array();
	$second_values = $second_attr ? $variation_attr_data[ $second_attr ] : array( '' );

	foreach ( $first_values as $val1 ) {
		foreach ( $second_values as $val2 ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $post_id );
			$variation->set_status( 'publish' );
			$variation->set_regular_price( $regular_price );
			if ( $sale_price !== '' ) {
				$variation->set_sale_price( $sale_price );
			}
			$variation->set_stock_status( 'instock' );
			$variation->set_manage_stock( false );
			if ( $sku_base ) {
				$variation->set_sku( $sku_base . '-VAR-' . ( $var_index + 1 ) );
			}

			// Атрибуты вариации: taxonomy => slug
			$var_attrs = array();
			if ( $first_attr ) {
				$var_attrs[ $first_attr ] = $val1;
			}
			if ( $second_attr && $val2 !== '' ) {
				$var_attrs[ $second_attr ] = $val2;
			}
			// Дополнительные атрибуты — «любое значение»
			foreach ( array_slice( $attr_keys, 2 ) as $extra_attr ) {
				$var_attrs[ $extra_attr ] = '';
			}
			$variation->set_attributes( $var_attrs );

			$variation_id = $variation->save();
			if ( $variation_id ) {
				update_post_meta( $variation_id, '_demo_created', true );
			}

			$var_index++;
		}
	}

	// Синхронизировать ценовой диапазон родительского товара
	WC_Product_Variable::sync( $post_id );

	// Изображение
	if ( ! empty( $item['image'] ) ) {
		cw_demo_import_product_image( $item['image'], $post_id );
	}

	wc_delete_product_transients( $post_id );

	return $post_id;
}

/**
 * Создать все demo товары.
 *
 * @return array
 */
function cw_demo_create_products() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array(
			'success' => false,
			'message' => __( 'WooCommerce is not active.', 'codeweber' ),
			'created' => 0,
			'total'   => 0,
			'errors'  => array(),
		);
	}

	$data = cw_demo_get_products_data();

	if ( empty( $data['items'] ) ) {
		return array(
			'success' => false,
			'message' => __( 'No product data found.', 'codeweber' ),
			'created' => 0,
			'total'   => 0,
			'errors'  => array(),
		);
	}

	$attr_config = $data['attributes'] ?? array();
	$created     = 0;
	$errors      = array();

	// Создаём иерархические категории и строим map[name => term_id]
	$cat_map = ! empty( $data['categories'] )
		? cw_demo_create_categories( $data['categories'] )
		: array();

	// Создаём бренды и строим map[name => term_id]
	$brand_map = ! empty( $data['brands'] )
		? cw_demo_create_brands( $data['brands'] )
		: array();

	foreach ( $data['items'] as $item ) {
		// Категория — ищем по map, чтобы учесть иерархию
		$cat_id = false;
		if ( ! empty( $item['category'] ) ) {
			if ( isset( $cat_map[ $item['category'] ] ) ) {
				$cat_id = $cat_map[ $item['category'] ];
			} else {
				// Запасной вариант: создать плоскую категорию
				$cat_id = cw_demo_get_or_create_product_term( $item['category'], 'product_cat' );
			}
		}

		// Теги
		$tag_ids = array();
		if ( ! empty( $item['tags'] ) ) {
			foreach ( $item['tags'] as $tag ) {
				$id = cw_demo_get_or_create_product_term( sanitize_text_field( $tag ), 'product_tag' );
				if ( $id ) {
					$tag_ids[] = $id;
				}
			}
		}

		$type    = $item['type'] ?? 'simple';
		$post_id = false;

		if ( $type === 'variable' && ! empty( $item['attributes'] ) ) {
			$post_id = cw_demo_create_variable_product( $item, $cat_id, $tag_ids, $attr_config );
		} else {
			$post_id = cw_demo_create_simple_product( $item, $cat_id, $tag_ids );
		}

		// Бренд товара
		if ( $post_id && ! empty( $item['brand'] ) && taxonomy_exists( 'product_brand' ) ) {
			$brand_name = sanitize_text_field( $item['brand'] );
			$brand_id   = $brand_map[ $brand_name ] ?? cw_demo_get_or_create_product_term( $brand_name, 'product_brand' );
			if ( $brand_id ) {
				wp_set_post_terms( $post_id, array( $brand_id ), 'product_brand' );
			}
		}

		if ( $post_id ) {
			$created++;
		} else {
			$errors[] = sprintf( __( 'Failed to create: %s', 'codeweber' ), $item['title'] ?? __( 'unknown', 'codeweber' ) );
		}
	}

	delete_transient( 'wc_products_onsale' );

	return array(
		'success' => true,
		'message' => sprintf( __( '%1$d of %2$d products created.', 'codeweber' ), $created, count( $data['items'] ) ),
		'created' => $created,
		'total'   => count( $data['items'] ),
		'errors'  => $errors,
	);
}

/**
 * Удалить все demo товары (включая вариации).
 *
 * @return array
 */
function cw_demo_delete_products() {
	// Удаляем и родительские товары, и вариации
	$posts = get_posts( array(
		'post_type'      => array( 'product', 'product_variation' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_demo_created',
				'value' => true,
			),
		),
		'fields' => 'ids',
	) );

	$deleted = 0;
	$errors  = array();

	foreach ( $posts as $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			wp_delete_attachment( $thumbnail_id, true );
		}

		$result = wp_delete_post( $post_id, true );
		if ( $result ) {
			$deleted++;
		} else {
			$errors[] = sprintf( __( 'Failed to delete record ID: %d', 'codeweber' ), $post_id );
		}
	}

	// Чистим пустые demo-термины (удаляем attachment картинки категорий и брендов)
	$cleanup_taxonomies = array( 'product_cat', 'product_tag' );
	if ( taxonomy_exists( 'product_brand' ) ) {
		$cleanup_taxonomies[] = 'product_brand';
	}

	foreach ( $cleanup_taxonomies as $taxonomy ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => 'all' ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 0 === $term->count ) {
					// Удалить attachment (картинка категории или логотип бренда)
					if ( in_array( $taxonomy, array( 'product_cat', 'product_brand' ), true ) ) {
						$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
						if ( $thumb_id ) {
							wp_delete_attachment( (int) $thumb_id, true );
						}
					}
					wp_delete_term( $term->term_id, $taxonomy );
				}
			}
		}
	}

	delete_transient( 'wc_products_onsale' );

	return array(
		'success' => true,
		'message' => sprintf( __( 'Deleted %d products.', 'codeweber' ), $deleted ),
		'deleted' => $deleted,
		'errors'  => $errors,
	);
}
