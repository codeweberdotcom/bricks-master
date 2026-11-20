# Шаблоны страницы товара (выбор шаблона в товаре)

Модуль родительской темы codeweber. Позволяет выбрать для конкретного товара другой вид страницы вместо стандартного `woocommerce/single-product.php`.

## Как работает

1. В редакторе товара, в правой колонке, есть блок **«Шаблон товара»** (Product template): «Шаблон по умолчанию» и зарегистрированные шаблоны. Блок скрыт, если ни одного шаблона не зарегистрировано.
2. Выбор хранится в мете товара `_cw_product_template` (slug). Пустое значение = стандартный шаблон.
3. На витрине WooCommerce ищет файл страницы товара через фильтр `woocommerce_template_loader_files`; модуль ставит выбранный файл первым: `woocommerce/single-product-templates/{slug}.php` (сначала дочерняя тема, потом родитель). Если slug не зарегистрирован или файла нет — используется стандартный шаблон, ничего не ломается.
4. На `<body>` добавляется класс `cw-product-template-{slug}` (для стилей).

Файлы: `functions/woocommerce/product-template.php` (подключён в `functions/woocommerce/init.php`), шаблоны в `woocommerce/single-product-templates/`.

## Зарегистрированные шаблоны

В родителе встроенных шаблонов нет: блок «Шаблон товара» появляется в редакторе товара, когда активная (дочерняя) тема зарегистрировала хотя бы один. Шаблоны темы hoger — в её документации (`doc_claude/integrations/PRODUCT_TEMPLATES.md` в теме hoger).

## Как добавить шаблон

1. Создать файл `woocommerce/single-product-templates/{slug}.php` (проще всего — копия `woocommerce/single-product.php` с нужными изменениями; в нём `get_header()`, цикл, `get_footer()`). Можно в дочерней теме: файл дочерней темы с тем же slug перекрывает родительский.
2. Зарегистрировать slug и название (строка должна быть переводимой), например в дочерней теме:

```php
add_filter( 'codeweber_product_templates', function ( array $templates ): array {
	$templates['my-layout'] = __( 'My layout', 'hoger' );
	return $templates;
} );
```

Slug — только `a-z`, `0-9`, `-`, `_` (проходит `sanitize_key`).

## Важно

- Шаблоны — копии стандартного: когда меняется `single-product.php` (например, вывод заголовка через `universal_title()`), то же изменение нужно внести в файлы `single-product-templates/*.php`.
- Форма EPO, сводка выбранного, правила заказа работают в любом шаблоне, если он вызывает `woocommerce_template_single_add_to_cart()` (как стандартный).
- Быстрый просмотр (quick view) шаблон не меняет: он использует свой `content-quick-view.php`.

## API

- `codeweber_product_templates(): array` — slug => название; фильтр `codeweber_product_templates`.
- `codeweber_get_product_template( $product_id = 0 ): string` — действующий slug или `''`.
- `codeweber_product_template_file( $slug ): string` — путь к файлу шаблона или `''`.

## Безопасность

Сохранение блока: nonce `codeweber_product_template`, проверка `edit_post`, пропуск автосохранения; значение сверяется со списком зарегистрированных slug (иначе мета удаляется).
