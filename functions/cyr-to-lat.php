<?php
add_filter('sanitize_title', 'my_cyr_to_lat_slug', 10, 3);

function my_cyr_to_lat_slug($title, $raw_title = '', $context = 'display')
{
   if (empty($raw_title)) {
      return $title;
   }

   if (! preg_match('/[а-яё]/iu', $raw_title)) {
      return $title;
   }

   $transliterated = cyr_to_lat($raw_title);

   return $transliterated;
}

function cyr_to_lat($text)
{
   $converter = [
      'а' => 'a',
      'б' => 'b',
      'в' => 'v',
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'ё' => 'yo',
      'ж' => 'zh',
      'з' => 'z',
      'и' => 'i',
      'й' => 'y',
      'к' => 'k',
      'л' => 'l',
      'м' => 'm',
      'н' => 'n',
      'о' => 'o',
      'п' => 'p',
      'р' => 'r',
      'с' => 's',
      'т' => 't',
      'у' => 'u',
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'ts',
      'ч' => 'ch',
      'ш' => 'sh',
      'щ' => 'shch',
      'ь' => '',
      'ы' => 'y',
      'ъ' => '',
      'э' => 'e',
      'ю' => 'yu',
      'я' => 'ya',
      'А' => 'A',
      'Б' => 'B',
      'В' => 'V',
      'Г' => 'G',
      'Д' => 'D',
      'Е' => 'E',
      'Ё' => 'Yo',
      'Ж' => 'Zh',
      'З' => 'Z',
      'И' => 'I',
      'Й' => 'Y',
      'К' => 'K',
      'Л' => 'L',
      'М' => 'M',
      'Н' => 'N',
      'О' => 'O',
      'П' => 'P',
      'Р' => 'R',
      'С' => 'S',
      'Т' => 'T',
      'У' => 'U',
      'Ф' => 'F',
      'Х' => 'H',
      'Ц' => 'Ts',
      'Ч' => 'Ch',
      'Ш' => 'Sh',
      'Щ' => 'Shch',
      'Ь' => '',
      'Ы' => 'Y',
      'Ъ' => '',
      'Э' => 'E',
      'Ю' => 'Yu',
      'Я' => 'Ya',
   ];

   $text = strtr($text, $converter);
   $text = mb_strtolower($text, 'UTF-8');
   $text = preg_replace('~[^-a-z0-9_]+~u', '-', $text);
   $text = preg_replace('~-+~', '-', $text);
   $text = trim($text, '-');

   return $text;
}
