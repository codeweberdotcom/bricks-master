# Создание архивных страниц и шаблонов

Это руководство описывает процесс создания архивных страниц и шаблонов для Custom Post Types на примере Staff.

## ⚠️ Важно: Необходимые файлы

Для работы архивной страницы CPT **обязательно** нужны два типа файлов:

1. **Главный архивный файл** - `archive-{post_type}.php` в корне темы
   - Пример: `archive-staff.php`, `archive-vacancies.php`
   - Этот файл определяет структуру архивной страницы
   - Без него WordPress не будет использовать кастомные шаблоны

2. **Шаблоны карточек** - файлы в `templates/archives/{post_type}/`
   - Пример: `templates/archives/staff/staff_1.php`
   - Эти файлы определяют отображение каждой записи в архиве

## 📁 Структура файлов

Архивные шаблоны организованы следующим образом:

```
wp-content/themes/codeweber/
├── archive-{post_type}.php          # Главный архивный шаблон
└── templates/
    └── archives/
        └── {post_type}/
            ├── {template_name}_1.php
            ├── {template_name}_2.php
            └── ...
```

**Пример для Staff:**
- `archive-staff.php` - Главный шаблон архива
- `templates/archives/staff/staff_1.php` - Шаблон карточки записи
- `templates/archives/staff/staff_2.php` - Альтернативный шаблон

## 🔧 Создание архивного шаблона

### Шаг 1: Создание главного файла архива (ОБЯЗАТЕЛЬНО!)

**Важно:** Без этого файла архивная страница не будет работать правильно!

Создайте файл `archive-{post_type}.php` в **корне темы** (на том же уровне, что и `functions.php`, `style.css`).

**Пример:** `archive-staff.php` для CPT с типом `staff`

**Расположение файла:**
```
wp-content/themes/codeweber/
├── archive-staff.php        ← Здесь, в корне темы!
├── functions.php
├── style.css
└── templates/
    └── archives/
        └── staff/
            └── staff_1.php
```

```php
<?php
/**
 * Template for Staff Archive Page
 * 
 * @package Codeweber
 */

get_header(); 
get_pageheader();
?>

<?php if (have_posts()) : ?>
<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
      <?php 
      // Получаем выбранный шаблон из настроек Redux
      $post_type = 'staff';
      global $opt_name;
      $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
      
      // Если шаблон не выбран, используем по умолчанию
      if (empty($templateloop)) {
          $templateloop = 'staff_1';
      }
      $template_file = "templates/archives/staff/{$templateloop}.php";
      
      // Получаем позицию сайдбара
      $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
      $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
      
      // Определяем структуру отображения
      $use_row_cols = ($templateloop === 'staff_3' || $templateloop === 'staff_4' || $templateloop === 'staff_5');
      ?>
      
      <div class="row">
          <?php get_sidebar('left'); ?>
          
          <div class="<?php echo esc_attr($content_class); ?>">
      
      <?php if ($use_row_cols) : ?>
          <!-- Структура с row-cols для некоторых шаблонов -->
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
              <?php while (have_posts()) : 
                the_post();
                
                if (locate_template($template_file)) {
                    get_template_part("templates/archives/staff/{$templateloop}");
                }
              endwhile; ?>
          </div>
          <!-- /.row -->
      <?php else : ?>
          <!-- Структура с grid/isotope для остальных шаблонов -->
          <div class="grid mb-5">
              <div class="row isotope g-3">
                  <?php 
                  while (have_posts()) : 
                    the_post();
                    
                    if (!empty($templateloop) && locate_template($template_file)) {
                        get_template_part("templates/archives/staff/{$templateloop}");
                    } else {
                        // Fallback: используем шаблон по умолчанию
                        if (locate_template("templates/archives/staff/staff_1.php")) {
                            get_template_part("templates/archives/staff/staff_1");
                        }
                    }
                  endwhile; ?>
              </div>
              <!-- /.row -->
          </div>
          <!-- /.grid -->
      <?php endif; ?>
      
      <?php 
      // Pagination
      codeweber_posts_pagination();
      ?>
          </div>
          <!-- /column -->
          
          <?php get_sidebar('right'); ?>
      </div>
      <!-- /.row -->
  </div>
  <!-- /.container -->
</section>
<!-- /section -->
<?php endif; ?>

<?php get_footer(); ?>
```

### Шаг 2: Создание шаблонов карточек записей

Создайте папку `templates/archives/{post_type}/` и добавьте шаблоны карточек.

**Пример:** `templates/archives/staff/staff_1.php`

```php
<?php
/**
 * Archive Template: Staff Card 1
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем метаполя
$position = get_post_meta(get_the_ID(), '_staff_position', true);
$name = get_post_meta(get_the_ID(), '_staff_name', true);
$surname = get_post_meta(get_the_ID(), '_staff_surname', true);
$email = get_post_meta(get_the_ID(), '_staff_email', true);

// Получаем отдел из таксономии
$departments = get_the_terms(get_the_ID(), 'departments');
$department_name = '';
if ($departments && !is_wp_error($departments) && !empty($departments)) {
    $department_name = $departments[0]->name;
}

$thumbnail_id = get_post_thumbnail_id();
$card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
?>

<div class="item col-md-6 col-xl-4">
    <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <?php if ($thumbnail_id) : ?>
            <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail('codeweber_medium', array('class' => 'img-fluid')); ?>
                </a>
            </figure>
        <?php endif; ?>
        
        <div class="card-body">
            <?php if (!empty($name) || !empty($surname)) : ?>
                <h3 class="mb-1">
                    <a href="<?php the_permalink(); ?>" class="link-dark">
                        <?php 
                        $full_name = trim($name . ' ' . $surname);
                        echo esc_html(!empty($full_name) ? $full_name : get_the_title());
                        ?>
                    </a>
                </h3>
            <?php else : ?>
                <h3 class="mb-1">
                    <a href="<?php the_permalink(); ?>" class="link-dark">
                        <?php the_title(); ?>
                    </a>
                </h3>
            <?php endif; ?>
            
            <?php if (!empty($position)) : ?>
                <p class="text-muted mb-3"><?php echo esc_html($position); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($department_name)) : ?>
                <p class="mb-3">
                    <span class="badge bg-soft-primary"><?php echo esc_html($department_name); ?></span>
                </p>
            <?php endif; ?>
            
            <?php if (has_excerpt()) : ?>
                <p class="mb-3"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($email)) : ?>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-sm btn-primary">
                    <?php echo esc_html__('Contact', 'codeweber'); ?>
                </a>
            <?php endif; ?>
        </div>
        <!--/.card-body -->
    </div>
    <!--/.card -->
</div>
<!--/.item -->
```

## 🎨 Различные структуры отображения

### Структура 1: Grid/Isotope (для шаблонов staff_1, staff_2)

```php
<div class="grid mb-5">
    <div class="row isotope g-3">
        <?php while (have_posts()) : the_post(); ?>
            <div class="item col-md-6 col-xl-4">
                <!-- Карточка записи -->
            </div>
        <?php endwhile; ?>
    </div>
</div>
```

### Структура 2: Row-cols (для шаблонов staff_3, staff_4, staff_5)

```php
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
    <?php while (have_posts()) : the_post(); ?>
        <div class="col">
            <!-- Карточка записи -->
        </div>
    <?php endwhile; ?>
</div>
```

## ⚙️ Интеграция с Redux Framework

### Настройка шаблона архива

В Redux Framework доступны следующие настройки:

1. **Выбор шаблона:** `archive_template_select_{post_type}`
   - Значения: `staff_1`, `staff_2`, `staff_3`, и т.д.

2. **Позиция сайдбара:** `sidebar_position_archive_{post_type}`
   - Значения: `left`, `right`, `none`

### Пример получения настроек:

```php
global $opt_name;
$templateloop = Redux::get_option($opt_name, 'archive_template_select_staff');
$sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_staff');
```

## 📝 Примеры существующих архивов

### Staff Archive

- **Главный файл:** `archive-staff.php` (в корне темы)
- **Шаблоны карточек:** `templates/archives/staff/staff_1.php` - `staff_5.php`
- **Особенности:**
  - Поддержка разных шаблонов карточек
  - Автоматическое определение структуры (grid/row-cols)
  - Интеграция с сайдбарами
- **Важно:** Файл `archive-staff.php` обязателен для работы архива!

### Vacancies Archive

- **Главный файл:** `archive-vacancies.php` (в корне темы)
- **Шаблоны карточек:** `templates/archives/vacancies/vacancies_1.php`
- **Особенности:**
  - Встроенные фильтры AJAX
  - Специальная разметка для вакансий
- **Важно:** Файл `archive-vacancies.php` обязателен для работы архива!

## 🔍 Работа с пагинацией

Используйте функцию `codeweber_posts_pagination()` для вывода пагинации:

```php
<?php codeweber_posts_pagination(); ?>
```

## 🎯 Рекомендации

1. **Используйте префиксы** - Называйте шаблоны как `{post_type}_1`, `{post_type}_2`, и т.д.
2. **Проверяйте существование** - Используйте `locate_template()` перед загрузкой шаблона
3. **Fallback шаблоны** - Всегда предусматривайте fallback на шаблон по умолчанию
4. **Экранирование данных** - Используйте `esc_html()`, `esc_attr()`, `esc_url()` для всех выводимых данных
5. **Поддержка сайдбаров** - Учитывайте разные позиции сайдбаров при определении классов контента

## ✅ Проверка работы

1. **Убедитесь, что создан главный файл** `archive-{post_type}.php` в корне темы
2. Создайте несколько записей вашего CPT
3. Перейдите на архивную страницу: `yoursite.com/{post_type}/`
4. Проверьте отображение карточек
5. Проверьте работу пагинации
6. Проверьте работу сайдбаров (если включены)
7. Проверьте выбор разных шаблонов через Redux

**Если архив не работает:**
- Проверьте, что файл `archive-{post_type}.php` находится в корне темы (не в подпапках)
- Проверьте, что имя файла точно соответствует типу записи: `archive-{post_type}.php`
- Убедитесь, что в настройках CPT включен `has_archive => true`

## 🔗 Связанные документы

- [CPT_CREATION.md](CPT_CREATION.md) - Создание новых CPT
- [SIDEBARS.md](SIDEBARS.md) - Добавление сайдбаров
- [SINGLE_TEMPLATES.md](SINGLE_TEMPLATES.md) - Создание single шаблонов

---

**Последнее обновление:** 2024-12-13

