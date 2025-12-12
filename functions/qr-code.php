<?php
/**
 * QR Code Generation for Staff
 * 
 * Функции для генерации QR кодов с контактными данными сотрудников
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Проверить и загрузить библиотеку phpqrcode
 */
function codeweber_load_qrcode_library() {
    $qrcode_path = get_template_directory() . '/functions/lib/phpqrcode/phpqrcode.php';
    
    if (!file_exists($qrcode_path)) {
        return false;
    }
    
    require_once $qrcode_path;
    return true;
}

/**
 * Кодировать строку в quoted-printable для vCard
 * Правильно обрабатывает все UTF-8 символы, включая кириллицу
 * 
 * @param string $text Текст для кодирования
 * @return string Закодированный текст
 */
function codeweber_vcard_quoted_printable($text) {
    if (empty($text)) {
        return '';
    }
    
    // Убеждаемся, что строка в UTF-8
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
    }
    
    // Используем стандартную функцию PHP для quoted-printable кодирования
    // Это обеспечит правильную кодировку для Windows
    $encoded = quoted_printable_encode($text);
    
    // Разбиваем длинные строки (максимум 75 символов на строку)
    // ВАЖНО: не разрываем hex коды (=XX) при переносе
    $lines = [];
    $line = '';
    $max_length = 75;
    $i = 0;
    
    while ($i < strlen($encoded)) {
        // Проверяем, начинается ли с = (это hex код)
        if ($encoded[$i] === '=' && $i + 2 < strlen($encoded)) {
            // Это hex код =XX, берем все 3 символа целиком
            $hex_code = substr($encoded, $i, 3);
            
            // Если добавление hex кода превысит лимит, переносим строку
            if (strlen($line) + 3 > $max_length && strlen($line) > 0) {
                // Сохраняем текущую строку с мягким переносом и начинаем новую с hex кода
                $lines[] = $line . '=';
                $line = $hex_code;
            } else {
                $line .= $hex_code;
            }
            $i += 3;
        } else {
            // Обычный символ
            $char = $encoded[$i];
            
            // Если добавление символа превысит лимит, переносим строку
            if (strlen($line) + 1 > $max_length && strlen($line) > 0) {
                // Пробуем найти последний пробел (=20) или обычный пробел для переноса
                // Ищем пробел не слишком близко к началу строки (минимум 50 символов)
                $min_space_pos = max(50, $max_length - 25);
                $last_space = false;
                
                // Ищем закодированный пробел =20
                $pos = strlen($line) - 1;
                while ($pos >= $min_space_pos) {
                    if (substr($line, $pos, 3) === '=20') {
                        $last_space = $pos;
                        break;
                    }
                    $pos--;
                }
                
                // Если не нашли закодированный пробел, ищем обычный
                if ($last_space === false) {
                    $last_space = strrpos($line, ' ', -($max_length - $min_space_pos));
                }
                
                if ($last_space !== false && $last_space >= $min_space_pos) {
                    // Переносим после пробела, чтобы не разрывать слова
                    // ВАЖНО: удаляем пробел из строки, чтобы избежать лишних пробелов при декодировании
                    if (substr($line, $last_space, 3) === '=20') {
                        // Это закодированный пробел - удаляем его из строки
                        $lines[] = substr($line, 0, $last_space) . '=';
                        $line = substr($line, $last_space + 3) . $char;
                    } else {
                        // Обычный пробел - удаляем его из строки
                        $lines[] = substr($line, 0, $last_space) . '=';
                        $line = substr($line, $last_space + 1) . $char;
                    }
                } else {
                    // Если пробела нет в подходящем месте, переносим как есть
                    $lines[] = $line . '=';
                    $line = $char;
                }
            } else {
                $line .= $char;
            }
            $i++;
        }
    }
    
    // Добавляем последнюю строку, если она не пустая
    if (!empty($line)) {
        $lines[] = $line;
    }
    
    // Объединяем строки с правильным переносом для vCard
    // Каждая строка (кроме последней) должна заканчиваться на = для мягкого переноса
    // Перенос строки в vCard: \r\n + пробел (пробел не учитывается в длине строки)
    $result = '';
    for ($j = 0; $j < count($lines); $j++) {
        if ($j > 0) {
            $result .= "\r\n ";
        }
        $result .= $lines[$j];
    }
    
    return $result;
}

/**
 * Создать vCard строку из данных сотрудника
 * Использует vCard 3.0 с quoted-printable кодированием для кириллицы
 * 
 * @param int $post_id ID записи staff
 * @param bool $for_qrcode Если true, использует UTF-8 для QR кода, иначе Windows-1251 для Windows Contacts
 * @return string vCard строка
 */
function codeweber_staff_generate_vcard($post_id, $for_qrcode = false) {
    $name = get_post_meta($post_id, '_staff_name', true);
    $surname = get_post_meta($post_id, '_staff_surname', true);
    $position = get_post_meta($post_id, '_staff_position', true);
    $company = get_post_meta($post_id, '_staff_company', true);
    $email = get_post_meta($post_id, '_staff_email', true);
    $phone = get_post_meta($post_id, '_staff_phone', true);
    $job_phone = get_post_meta($post_id, '_staff_job_phone', true);
    $street = get_post_meta($post_id, '_staff_street', true);
    $city = get_post_meta($post_id, '_staff_city', true);
    $region = get_post_meta($post_id, '_staff_region', true);
    $postal_code = get_post_meta($post_id, '_staff_postal_code', true);
    $country = get_post_meta($post_id, '_staff_country', true);
    $website = get_post_meta($post_id, '_staff_website', true);
    
    // Функция для нормализации URL (добавление http:// если отсутствует)
    $normalize_url = function($url) {
        if (empty($url)) {
            return '';
        }
        // Убираем пробелы
        $url = trim($url);
        // Если URL не начинается с http:// или https://, добавляем https://
        if (!preg_match('/^https?:\/\//i', $url)) {
            // Для Skype используем skype: протокол, для остальных - https://
            if (preg_match('/^skype:/i', $url)) {
                return $url; // Skype уже имеет протокол
            }
            // Для телефонных номеров (tel:) оставляем как есть
            if (preg_match('/^tel:/i', $url)) {
                return $url;
            }
            // Для остальных добавляем https://
            $url = 'https://' . $url;
        }
        return $url;
    };
    
    // Нормализуем website
    if (!empty($website)) {
        $website = $normalize_url($website);
    }
    
    // Получаем соцсети
    $social_fields = ['facebook', 'twitter', 'linkedin', 'instagram', 'telegram', 'vk', 'whatsapp', 'skype'];
    $socials = [];
    foreach ($social_fields as $social_key) {
        $url = get_post_meta($post_id, '_staff_' . $social_key, true);
        if (!empty($url)) {
            // Для Skype не добавляем префикс, если это username
            if ($social_key === 'skype' && !preg_match('/^(skype:|https?:\/\/)/i', $url)) {
                $socials[$social_key] = 'skype:' . $url;
            } else {
                $socials[$social_key] = $normalize_url($url);
            }
        }
    }
    
    // Убеждаемся, что все строки в UTF-8
    // Используем более надежный метод конвертации
    $convert_to_utf8 = function($text) {
        if (empty($text)) {
            return '';
        }
        // Проверяем, является ли строка валидной UTF-8
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        // Пробуем определить кодировку и конвертировать
        $detected = mb_detect_encoding($text, ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'CP1251'], true);
        if ($detected && $detected !== 'UTF-8') {
            $converted = mb_convert_encoding($text, 'UTF-8', $detected);
            // Проверяем результат
            if (mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }
        // Если не удалось определить, пробуем принудительно конвертировать из Windows-1251 (часто используется для кириллицы)
        $converted = @mb_convert_encoding($text, 'UTF-8', 'Windows-1251');
        if (mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
        // В крайнем случае возвращаем как есть
        return $text;
    };
    
    $name = $convert_to_utf8($name);
    $surname = $convert_to_utf8($surname);
    $position = $convert_to_utf8($position);
    $company = $convert_to_utf8($company);
    $street = $convert_to_utf8($street);
    $city = $convert_to_utf8($city);
    $region = $convert_to_utf8($region);
    $country = $convert_to_utf8($country);
    
    // Формируем полное имя
    $full_name = trim($name . ' ' . $surname);
    if (empty($full_name)) {
        $title = get_the_title($post_id);
        $full_name = $convert_to_utf8($title);
    }
    
    // Используем разные версии для разных случаев
    // vCard 4.0 для QR кода (лучше работает при сканировании)
    // vCard 3.0 для VCF файла (лучшая совместимость с Windows и Android)
    
    // Позволяем переопределить версию через фильтр для тестирования
    if ($for_qrcode) {
        $vcard_version = apply_filters('codeweber_vcard_version', '4.0', $for_qrcode);
    } else {
        $vcard_version = apply_filters('codeweber_vcard_version', '3.0', $for_qrcode);
    }
    
    $vcard = "BEGIN:VCARD\r\n";
    $vcard .= "VERSION:" . $vcard_version . "\r\n";
    
    // Для vCard 3.0 НЕ добавляем CHARSET в заголовок (не поддерживается стандартом)
    // CHARSET указывается в каждом поле через ENCODING=QUOTED-PRINTABLE
    
    // Функция для кодирования текста в зависимости от версии vCard
    $encode_text = function($text) use ($for_qrcode, $vcard_version) {
        if (empty($text)) {
            return '';
        }
        // Убеждаемся, что строка в UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
        }
        
        // Убираем лишние пробелы в начале и конце
        $text = trim($text);
        
        if ($vcard_version === '4.0') {
            // Для vCard 4.0 экранируем только специальные символы
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(',', '\\,', $text);
            $text = str_replace(';', '\\;', $text);
            $text = str_replace("\n", '\\n', $text);
            $text = str_replace("\r", '', $text);
            return $text;
        } else {
            // Для vCard 3.0 используем quoted-printable БЕЗ экранирования символов
            $text = trim($text);
            $encoded = quoted_printable_encode($text);
            
            // Разбиваем на строки по 75 символов
            // ЗАПРЕЩАЕМ разрывы слов - переносим ТОЛЬКО после пробелов
            // Если пробела нет, строка может быть длиннее 75 символов, но слово не разорвется
            $result = '';
            $max_length = 75;
            $pos = 0;
            $len = strlen($encoded);
            
            while ($pos < $len) {
                $remaining = $len - $pos;
                
                if ($remaining <= $max_length) {
                    // Остаток помещается в одну строку
                    $line = substr($encoded, $pos);
                    if ($pos > 0) {
                        $result .= "\r\n ";
                    }
                    $result .= $line;
                    break;
                }
                
                // Ищем последний пробел в пределах допустимой длины
                // Ищем в широком диапазоне, чтобы гарантированно найти пробел
                $search_len = min($max_length + 50, $remaining); // Ищем в пределах max_length + 50
                $search_area = substr($encoded, $pos, $search_len);
                $last_space = strrpos($search_area, '=20');
                
                if ($last_space !== false && $last_space > 0) {
                    // Нашли пробел, переносим после него
                    // Пробел НЕ включаем в строку (удаляем его), чтобы избежать двойных пробелов
                    $line_len = $last_space;
                    $line = substr($encoded, $pos, $line_len);
                    
                    if ($pos > 0) {
                        $result .= "\r\n ";
                    }
                    $result .= $line;
                    // Добавляем = только если это не последняя строка поля
                    // Проверяем, что после этой строки еще есть данные
                    if ($pos + $line_len + 3 < $len) {
                        $result .= '=';
                    }
                    $pos += $line_len + 3; // +3 для пропуска =20 (пробел)
                } else {
                    // Пробела нет - НЕ разрываем слово!
                    // Оставляем строку длиннее 75 символов, но слово не будет разорвано
                    // Проверяем только, что не разрываем hex код
                    $line_len = $max_length;
                    
                    // Проверяем, не разрываем ли hex код в конце
                    if ($pos + $line_len < $len) {
                        if ($encoded[$pos + $line_len - 1] === '=') {
                            // Последний символ =, это может быть начало hex кода
                            $line_len = $line_len - 1;
                        } else if ($pos + $line_len - 2 >= 0 && $encoded[$pos + $line_len - 2] === '=') {
                            // Предпоследний символ =, не разрываем
                            $line_len = $line_len - 2;
                        }
                    }
                    
                    // Если все еще нет пробела, ищем дальше - до следующего пробела
                    // Но не разрываем слово
                    $next_space = strpos($encoded, '=20', $pos + $line_len);
                    if ($next_space !== false) {
                        // Нашли следующий пробел, берем до него
                        $line_len = $next_space - $pos;
                    } else {
                        // Пробела больше нет, берем до конца
                        $line_len = $remaining;
                    }
                    
                    $line = substr($encoded, $pos, $line_len);
                    if ($pos > 0) {
                        $result .= "\r\n ";
                    }
                    $result .= $line;
                    // Добавляем = только если это не последняя строка
                    if ($pos + $line_len < $len) {
                        $result .= '=';
                    }
                    $pos += $line_len;
                }
            }
            
            // Убеждаемся, что результат правильно заканчивается
            // Убираем только пробелы и табуляции в конце
            // НЕ удаляем = и переносы строк - они нужны для структуры
            // ВАЖНО: не удаляем = в конце, так как это может быть частью hex кода
            $result = rtrim($result, " \t");
            
            return $result;
        }
    };
    
    // Используем разные версии для разных случаев
    if ($vcard_version === '4.0') {
        // vCard 4.0 - без ENCODING, прямое UTF-8
        $vcard .= "FN:" . $encode_text($full_name) . "\r\n";
    } else {
        // vCard 3.0 - с ENCODING=QUOTED-PRINTABLE
        $vcard .= "FN;ENCODING=QUOTED-PRINTABLE:" . $encode_text($full_name) . "\r\n";
    }
    
    if (!empty($name) || !empty($surname)) {
        $n_value = $encode_text($surname) . ";" . $encode_text($name) . ";;;";
        if ($vcard_version === '4.0') {
            $vcard .= "N:" . $n_value . "\r\n";
        } else {
            $vcard .= "N;ENCODING=QUOTED-PRINTABLE:" . $n_value . "\r\n";
        }
    }
    
    if (!empty($position)) {
        if ($vcard_version === '4.0') {
            $vcard .= "TITLE:" . $encode_text($position) . "\r\n";
        } else {
            $vcard .= "TITLE;ENCODING=QUOTED-PRINTABLE:" . $encode_text($position) . "\r\n";
        }
    }
    
    if (!empty($company)) {
        if ($vcard_version === '4.0') {
            $vcard .= "ORG:" . $encode_text($company) . "\r\n";
        } else {
            $vcard .= "ORG;ENCODING=QUOTED-PRINTABLE:" . $encode_text($company) . "\r\n";
        }
    }
    
    if (!empty($email)) {
        $vcard .= "EMAIL;TYPE=WORK:" . $email . "\r\n";
    }
    
    if (!empty($phone)) {
        $phone_clean = preg_replace('/[^0-9+]/', '', $phone);
        $vcard .= "TEL;TYPE=CELL:" . $phone_clean . "\r\n";
    }
    
    if (!empty($job_phone)) {
        $job_phone_clean = preg_replace('/[^0-9+]/', '', $job_phone);
        $vcard .= "TEL;TYPE=WORK:" . $job_phone_clean . "\r\n";
    }
    
    // Адрес
    if (!empty($street) || !empty($city) || !empty($region) || !empty($postal_code) || !empty($country)) {
        // Убеждаемся, что все части адреса в UTF-8 перед кодированием
        if (!empty($street) && !mb_check_encoding($street, 'UTF-8')) {
            $street = mb_convert_encoding($street, 'UTF-8', mb_detect_encoding($street));
        }
        if (!empty($city) && !mb_check_encoding($city, 'UTF-8')) {
            $city = mb_convert_encoding($city, 'UTF-8', mb_detect_encoding($city));
        }
        if (!empty($region) && !mb_check_encoding($region, 'UTF-8')) {
            $region = mb_convert_encoding($region, 'UTF-8', mb_detect_encoding($region));
        }
        if (!empty($country) && !mb_check_encoding($country, 'UTF-8')) {
            $country = mb_convert_encoding($country, 'UTF-8', mb_detect_encoding($country));
        }
        
        // vCard 4.0 - кодируем каждую часть отдельно
        $address_parts = [
            '', // PO Box
            '', // Extended Address
            $encode_text($street), // Street
            $encode_text($city), // City
            $encode_text($region), // Region/State
            $encode_text($postal_code), // Postal Code
            $encode_text($country) // Country
        ];
        $address_line = implode(';', $address_parts);
        if ($vcard_version === '4.0') {
            $vcard .= "ADR;TYPE=WORK:" . $address_line . "\r\n";
        } else {
            // Для vCard 3.0 кодируем весь адрес целиком
            $address_parts_raw = [
                '', // PO Box
                '', // Extended Address
                $street, // Street
                $city, // City
                $region, // Region/State
                $postal_code, // Postal Code
                $country // Country
            ];
            $address_line_raw = implode(';', $address_parts_raw);
            $encoded_address = $encode_text($address_line_raw);
            $vcard .= "ADR;TYPE=WORK;ENCODING=QUOTED-PRINTABLE:" . $encoded_address . "\r\n";
        }
    }
    
    // Website
    if (!empty($website)) {
        $vcard .= "URL;TYPE=WORK:" . $website . "\r\n";
    }
    
    // Соцсети
    $social_map = [
        'facebook' => 'facebook',
        'twitter' => 'twitter',
        'linkedin' => 'linkedin',
        'instagram' => 'instagram',
        'telegram' => 'telegram',
        'vk' => 'vk',
        'whatsapp' => 'whatsapp',
        'skype' => 'skype'
    ];
    
    foreach ($socials as $social_key => $url) {
        $social_type = isset($social_map[$social_key]) ? $social_map[$social_key] : $social_key;
        
        // Для vCard 4.0 используем X-SOCIALPROFILE с TYPE
        // Для vCard 3.0 используем X- поля
        if ($vcard_version === '4.0') {
            $vcard .= "X-SOCIALPROFILE;TYPE=" . strtoupper($social_type) . ":" . $url . "\r\n";
        } else {
            $vcard .= "X-" . strtoupper($social_type) . ":" . $url . "\r\n";
        }
    }
    
    // Убеждаемся, что END:VCARD добавлен правильно с переносом строки
    $vcard .= "END:VCARD\r\n";
    
    return $vcard;
}

/**
 * Сгенерировать QR код для сотрудника
 * 
 * @param int $post_id ID записи staff
 * @return int|false ID attachment или false при ошибке
 */
function codeweber_staff_generate_qrcode($post_id) {
    if (!codeweber_load_qrcode_library()) {
        return false;
    }
    
    // Проверяем, что класс QRcode доступен
    if (!class_exists('QRcode')) {
        return false;
    }
    
    // Получаем vCard данные для QR кода (используем UTF-8)
    $vcard_data = codeweber_staff_generate_vcard($post_id, true);
    
    if (empty($vcard_data)) {
        return false;
    }
    
    // Убеждаемся, что данные в UTF-8 и правильно закодированы
    if (!mb_check_encoding($vcard_data, 'UTF-8')) {
        $vcard_data = mb_convert_encoding($vcard_data, 'UTF-8', mb_detect_encoding($vcard_data));
    }
    
    // Создаем временную директорию для QR кодов
    $upload_dir = wp_upload_dir();
    $qrcode_dir = $upload_dir['basedir'] . '/staff-qrcodes';
    
    // Нормализуем путь (для Windows)
    $qrcode_dir = str_replace('\\', '/', $qrcode_dir);
    $qrcode_dir = str_replace('//', '/', $qrcode_dir);
    
    if (!file_exists($qrcode_dir)) {
        wp_mkdir_p($qrcode_dir);
    }
    
    // Проверяем, что директория создана и доступна для записи
    if (!is_dir($qrcode_dir) || !is_writable($qrcode_dir)) {
        @chmod($qrcode_dir, 0755);
        if (!is_writable($qrcode_dir)) {
            return false;
        }
    }
    
    // Генерируем уникальное имя файла
    $filename = 'staff-' . $post_id . '-qrcode.png';
    
    // Используем абсолютный путь (нормализованный)
    $filepath = trailingslashit($qrcode_dir) . $filename;
    
        // Проверяем, что GD библиотека установлена
        if (!function_exists('imagecreate') || !function_exists('imagepng')) {
            return false;
        }
        
        // Проверяем, что GD может создавать изображения
        $test_image = @imagecreate(10, 10);
        if ($test_image === false) {
            return false;
        }
        imagedestroy($test_image);
    
        // Генерируем QR код
        // Используем временный файл в системной временной директории
        $old_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
            return false;
        });
        
        // Определяем параметры QR кода в зависимости от размера данных
        // Для больших данных используем более низкий уровень коррекции ошибок
        // и меньший размер модуля
        $data_size = strlen($vcard_data);
        
        if ($data_size > 2000) {
            // Очень большие данные - используем низкий уровень коррекции и маленький размер
            $error_level = QR_ECLEVEL_L; // Низкий уровень коррекции
            $size = 6;
        } elseif ($data_size > 1500) {
            // Большие данные - используем средний уровень коррекции
            $error_level = QR_ECLEVEL_M; // Средний уровень коррекции
            $size = 8;
        } elseif ($data_size > 1000) {
            // Средние данные - используем квартетный уровень коррекции
            $error_level = QR_ECLEVEL_Q; // Квартетный уровень коррекции
            $size = 10;
        } else {
            // Маленькие данные - используем высокий уровень коррекции
            $error_level = QR_ECLEVEL_H; // Высокий уровень коррекции
            $size = 10;
        }
        
        // Создаем временный файл с расширением .png
        $temp_dir = sys_get_temp_dir();
        $temp_file = $temp_dir . DIRECTORY_SEPARATOR . 'qrcode_' . uniqid() . '.png';
        
        // Удаляем файл, если он существует
        if (file_exists($temp_file)) {
            @unlink($temp_file);
        }
        
        $qr_error = null;
        
        try {
            // Пробуем создать файл напрямую в целевой директории
            // Если не получится, используем временный файл
            $direct_save = false;
            
            // Пробуем сохранить напрямую
            QRcode::png($vcard_data, $filepath, $error_level, $size, 2, false, 0xFFFFFF, 0x000000);
            
            // Небольшая задержка
            usleep(50000); // 0.05 секунды
            
            if (file_exists($filepath)) {
                $file_size = filesize($filepath);
                if ($file_size !== false && $file_size > 0) {
                    $direct_save = true;
                } else {
                    @unlink($filepath);
                }
            }
            
            // Если прямая запись не удалась, пробуем через временный файл
            if (!$direct_save) {
                // Проверяем, что временная директория доступна
                if (!is_writable($temp_dir)) {
                    $qr_error = 'Temp dir not writable';
                } else {
                    // Пробуем создать тестовый файл в временной директории
                    $test_file = $temp_dir . DIRECTORY_SEPARATOR . 'test_' . uniqid() . '.txt';
                    if (@file_put_contents($test_file, 'test')) {
                        @unlink($test_file);
                    } else {
                        $qr_error = 'Temp dir write test failed';
                    }
                }
                
                if (!$qr_error) {
                    // Генерируем QR код во временный файл
                    // Используем более агрессивный перехват ошибок
                    $error_occurred = false;
                    $error_message = '';
                    
                    // Перехватываем все возможные ошибки
                    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error_occurred, &$error_message) {
                        $error_occurred = true;
                        $error_message = $errstr . ' in ' . $errfile . ':' . $errline;
                        return false; // Продолжаем выполнение
                    }, E_ALL);
                    
                    try {
                        // Пробуем создать файл вручную перед вызовом QRcode::png
                        // Это может помочь библиотеке
                        $handle = @fopen($temp_file, 'w');
                        if ($handle) {
                            fclose($handle);
                            @unlink($temp_file); // Удаляем пустой файл
                        }
                        
                        // Пробуем альтернативный подход: генерируем в память, затем сохраняем
                        // Сначала пробуем прямой вызов с определенными параметрами
                        QRcode::png($vcard_data, $temp_file, $error_level, $size, 2, false, 0xFFFFFF, 0x000000);
                        
                        // Если файл не создан, пробуем через output buffer
                        if (!file_exists($temp_file)) {
                            // Генерируем в память
                            // Отключаем вывод ошибок для чистого output buffer
                            $old_error_reporting = error_reporting(0);
                            ob_start();
                            
                            try {
                                // Пробуем сгенерировать QR код с определенными параметрами
                                QRcode::png($vcard_data, false, $error_level, $size, 2, false, 0xFFFFFF, 0x000000);
                                
                            } catch (Throwable $e) {
                                ob_end_clean();
                                error_reporting($old_error_reporting);
                                throw $e;
                            }
                            
                            $image_data = ob_get_clean();
                            error_reporting($old_error_reporting);
                            
                            // Проверяем, что данные получены
                            if (!empty($image_data)) {
                                // Проверяем, что это действительно PNG изображение
                                if (substr($image_data, 0, 8) === "\x89PNG\r\n\x1a\n") {
                                    // Сохраняем в файл
                                    @file_put_contents($temp_file, $image_data, LOCK_EX);
                                } else {
                                    // Пробуем использовать низкоуровневый API библиотеки
                                    try {
                                    $enc = QRencode::factory($error_level, $size, 2, 0xFFFFFF, 0x000000);
                                    $tab = $enc->encode($vcard_data);
                                    
                                    if (!empty($tab)) {
                                        // Создаем изображение вручную через GD
                                        $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($tab) + 2 * 2));
                                        $pixelPerPoint = min(max(1, $size), $maxSize);
                                        $outerFrame = 2;
                                        
                                        // Вычисляем размер изображения
                                        $image_size = count($tab) + 2 * $outerFrame;
                                        $image_width = $image_size * $pixelPerPoint;
                                        
                                        // Создаем изображение
                                        $image = imagecreate($image_width, $image_width);
                                        imagecolorallocate($image, 255, 255, 255); // Белый фон
                                        $foreground = imagecolorallocate($image, 0, 0, 0); // Черный цвет
                                        
                                        // Рисуем QR код
                                        for ($y = 0; $y < count($tab); $y++) {
                                            for ($x = 0; $x < count($tab[$y]); $x++) {
                                                if ($tab[$y][$x]) {
                                                    imagefilledrectangle(
                                                        $image,
                                                        ($x + $outerFrame) * $pixelPerPoint,
                                                        ($y + $outerFrame) * $pixelPerPoint,
                                                        ($x + $outerFrame + 1) * $pixelPerPoint - 1,
                                                        ($y + $outerFrame + 1) * $pixelPerPoint - 1,
                                                        $foreground
                                                    );
                                                }
                                            }
                                        }
                                        
                                        // Сохраняем изображение
                                        imagepng($image, $temp_file);
                                        imagedestroy($image);
                                    }
                                } catch (Throwable $e) {
                                    // Ошибка при использовании низкоуровневого API
                                }
                                }
                            }
                        }
                    } catch (Throwable $e) {
                        $qr_error = $e->getMessage();
                    } finally {
                        restore_error_handler();
                    }
                    
                    // Небольшая задержка
                    usleep(200000); // 0.2 секунды - увеличиваем задержку
                    
                    // Проверяем, что временный файл создан
                    if (!file_exists($temp_file)) {
                        $qr_error = 'Temp file not created';
                    } else {
                        $temp_size = filesize($temp_file);
                        if ($temp_size === false || $temp_size == 0) {
                            $qr_error = 'Temp file is empty';
                        } else {
                            // Копируем временный файл в целевую директорию
                            $copy_result = @copy($temp_file, $filepath);
                            @unlink($temp_file); // Удаляем временный файл
                            
                            if (!$copy_result) {
                                $qr_error = 'Copy failed';
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $qr_error = $e->getMessage();
        } catch (Error $e) {
            $qr_error = $e->getMessage();
        }
        
        // Восстанавливаем обработчик ошибок
        if ($old_error_handler !== null) {
            set_error_handler($old_error_handler);
        } else {
            restore_error_handler();
        }
        
        // Очищаем временный файл, если он остался
        if (file_exists($temp_file)) {
            @unlink($temp_file);
        }
        
        if ($qr_error || !file_exists($filepath)) {
            return false;
        }
        
        // Проверяем размер файла
        $file_size = filesize($filepath);
        if ($file_size === false || $file_size == 0) {
            @unlink($filepath);
            return false;
        }
    
    // Загружаем файл в медиабиблиотеку
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $file_array = array(
        'name' => $filename,
        'tmp_name' => $filepath,
    );
    
    $attachment_id = media_handle_sideload($file_array, $post_id);
    
    // Удаляем временный файл
    if (file_exists($filepath)) {
        @unlink($filepath);
    }
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    // Устанавливаем родителя
    wp_update_post(array(
        'ID' => $attachment_id,
        'post_parent' => $post_id
    ));
    
    // Сохраняем ID QR кода в метаполе
    update_post_meta($post_id, '_staff_qrcode_id', $attachment_id);
    
    return $attachment_id;
}

/**
 * AJAX обработчик для генерации QR кода
 */
function codeweber_ajax_generate_staff_qrcode() {
    // Проверка прав
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'generate_staff_qrcode')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности', 'codeweber')
        ));
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(array(
            'message' => __('Неверный ID записи', 'codeweber')
        ));
    }
    
    // Удаляем старый QR код, если есть
    $old_qrcode_id = get_post_meta($post_id, '_staff_qrcode_id', true);
    if ($old_qrcode_id) {
        wp_delete_attachment($old_qrcode_id, true);
    }
    
    // Генерируем новый QR код
    $qrcode_id = codeweber_staff_generate_qrcode($post_id);
    
    if ($qrcode_id) {
        $qrcode_url = wp_get_attachment_image_url($qrcode_id, 'full');
        wp_send_json_success(array(
            'message' => __('QR код успешно сгенерирован', 'codeweber'),
            'qrcode_id' => $qrcode_id,
            'qrcode_url' => $qrcode_url
        ));
    } else {
        // Получаем последнюю ошибку из логов для отладки
        $error_message = __('Не удалось сгенерировать QR код', 'codeweber');
        
        // Проверяем, что библиотека загружена
        if (!codeweber_load_qrcode_library()) {
            $error_message = __('Библиотека QR кода не найдена', 'codeweber');
        } else {
            // Проверяем, что vCard данные генерируются
            $test_vcard = codeweber_staff_generate_vcard($post_id, true);
            if (empty($test_vcard)) {
                $error_message = __('Не удалось создать vCard данные', 'codeweber');
            }
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
    }
}
add_action('wp_ajax_generate_staff_qrcode', 'codeweber_ajax_generate_staff_qrcode');

/**
 * AJAX обработчик для удаления QR кода
 */
function codeweber_ajax_delete_staff_qrcode() {
    // Проверка прав
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array(
            'message' => __('Недостаточно прав', 'codeweber')
        ));
    }
    
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_staff_qrcode')) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности', 'codeweber')
        ));
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if (!$post_id) {
        wp_send_json_error(array(
            'message' => __('Неверный ID записи', 'codeweber')
        ));
    }
    
    // Получаем ID QR кода
    $qrcode_id = get_post_meta($post_id, '_staff_qrcode_id', true);
    
    if ($qrcode_id) {
        // Удаляем файл из медиабиблиотеки
        $deleted = wp_delete_attachment($qrcode_id, true);
        
        if ($deleted) {
            // Удаляем метаполе
            delete_post_meta($post_id, '_staff_qrcode_id');
            
            wp_send_json_success(array(
                'message' => __('QR код успешно удален', 'codeweber')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Не удалось удалить QR код', 'codeweber')
            ));
        }
    } else {
        wp_send_json_error(array(
            'message' => __('QR код не найден', 'codeweber')
        ));
    }
}
add_action('wp_ajax_delete_staff_qrcode', 'codeweber_ajax_delete_staff_qrcode');

/**
 * Регистрация REST API endpoint для скачивания VCF файла
 */
add_action('rest_api_init', function() {
    register_rest_route('codeweber/v1', '/staff/(?P<id>\d+)/vcf-url', [
        'methods' => 'GET',
        'callback' => 'codeweber_staff_get_vcf_url',
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
        ]
    ]);
});

/**
 * REST API callback для получения URL VCF файла
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function codeweber_staff_get_vcf_url($request) {
    $post_id = intval($request['id']);
    
    // Проверяем, что это запись типа staff
    if (get_post_type($post_id) !== 'staff') {
        return new WP_Error('invalid_post_type', 'Invalid post type', ['status' => 400]);
    }
    
    // Генерируем vCard 3.0 для VCF файла (лучшая совместимость с Windows и Android)
    $vcard_content = codeweber_staff_generate_vcard($post_id, false);
    
    if (empty($vcard_content)) {
        return new WP_Error('vcard_generation_failed', 'Failed to generate vCard', ['status' => 500]);
    }
    
    // Убеждаемся, что содержимое в UTF-8 (vCard 4.0 использует UTF-8)
    if (!mb_check_encoding($vcard_content, 'UTF-8')) {
        $vcard_content = mb_convert_encoding($vcard_content, 'UTF-8', mb_detect_encoding($vcard_content));
    }
    
    // Получаем имя сотрудника для имени файла
    $name = get_post_meta($post_id, '_staff_name', true);
    $surname = get_post_meta($post_id, '_staff_surname', true);
    $full_name = trim($name . ' ' . $surname);
    if (empty($full_name)) {
        $full_name = get_the_title($post_id);
    }
    
    // Очищаем имя для использования в имени файла
    $file_name = sanitize_file_name($full_name) . '.vcf';
    
    // Создаем временный файл
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/temp-vcf/';
    
    // Создаем директорию, если её нет
    if (!file_exists($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }
    
    // Генерируем уникальное имя файла
    $temp_file = $temp_dir . 'staff-' . $post_id . '-' . time() . '.vcf';
    
    // Сохраняем vCard 3.0 в файл
    // Простая версия - минимальная обработка, как было раньше когда работало
    
    // Просто сохраняем как есть - без лишних проверок и обработок
    $bytes_written = file_put_contents($temp_file, $vcard_content, LOCK_EX);
    
    // Проверяем, что файл создан и не пустой
    if ($bytes_written === false || !file_exists($temp_file) || filesize($temp_file) == 0) {
        return new WP_Error('vcf_file_creation_failed', 'Failed to create VCF file', ['status' => 500]);
    }
    
    // Получаем URL файла
    $file_url = $upload_dir['baseurl'] . '/temp-vcf/' . basename($temp_file);
    
    // Удаляем старые временные файлы (старше 1 часа)
    codeweber_cleanup_temp_vcf_files($temp_dir);
    
    return rest_ensure_response([
        'success' => true,
        'file_url' => $file_url,
        'file_name' => $file_name,
        'post_id' => $post_id
    ]);
}

/**
 * Очистка старых временных VCF файлов
 * 
 * @param string $dir Директория с временными файлами
 */
function codeweber_cleanup_temp_vcf_files($dir) {
    $files = glob($dir . 'staff-*.vcf');
    $now = time();
    
    foreach ($files as $file) {
        // Удаляем файлы старше 1 часа
        if (is_file($file) && ($now - filemtime($file)) > 3600) {
            @unlink($file);
        }
    }
}

