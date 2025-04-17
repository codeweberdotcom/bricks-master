<?php

namespace Codeweber\Functions\Fetch;

function exampleFunction($params)
{
   $name = $params['name'] ?? 'Гость';
   $age = $params['age'] ?? 0;

   return [
      'status' => 'success',
      'data' => [
         'message' => "Привет, $name! Тебе $age лет.",
      ],
   ];
}
