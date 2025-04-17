<?php

function createCustomNavbar($config, $wrapper)
{
	return "
        " . (isset($wrapper['navbar']['open']) ? $wrapper['navbar']['open'] : '') . "
            " . (isset($wrapper['container']['open']) ? $wrapper['container']['open'] : '') . "
                " . (isset($wrapper['navbarBrand']['open']) ? $wrapper['navbarBrand']['open'] : '') . "
                    " . (isset($config['logo']) ? $config['logo'] : '') . "
                " . (isset($wrapper['navbarBrand']['close']) ? $wrapper['navbarBrand']['close'] : '') . "
                " . (isset($wrapper['navbarCollapse']['open']) ? $wrapper['navbarCollapse']['open'] : '') . "
                    " . (isset($wrapper['offcanvasHeader']['open']) ? $wrapper['offcanvasHeader']['open'] : '') . "
                        " . (isset($config['offcanvasHeaderContent']) ? $config['offcanvasHeaderContent'] : '') . "
                    " . (isset($wrapper['offcanvasHeader']['close']) ? $wrapper['offcanvasHeader']['close'] : '') . "
                    " . (isset($wrapper['offcanvasBody']['open']) ? $wrapper['offcanvasBody']['open'] : '') . "
                        " . (isset($config['mainMenu']) ? $config['mainMenu'] : '') . "
                    " . (isset($wrapper['offcanvasBody']['close']) ? $wrapper['offcanvasBody']['close'] : '') . "
                " . (isset($wrapper['navbarCollapse']['close']) ? $wrapper['navbarCollapse']['close'] : '') . "
            " . (isset($wrapper['container']['close']) ? $wrapper['container']['close'] : '') . "
        " . (isset($wrapper['navbar']['close']) ? $wrapper['navbar']['close'] : '') . "
    ";
}

$config = [
	'logo' => '
        <a href="./index.html">
            <img src="https://bricksnew.test/wp-content/uploads/2025/01/logo-13.png" srcset="https://bricksnew.test/wp-content/uploads/2025/01/logo-13.png" alt="Logo" />
        </a>
    ',
	'offcanvasHeaderContent' => '
        <a href="/">
            <img src="https://bricksnew.test/wp-content/uploads/2025/01/logo-13.png" srcset="./assets/img/logo-light@2x.png 2x" alt="Logo Light" />
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    ',
	'mainMenu' => '
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
            <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Features</a>
                <ul class="dropdown-menu">
                    <li class="nav-item"><a class="dropdown-item" href="#">Feature 1</a></li>
                    <li class="nav-item"><a class="dropdown-item" href="#">Feature 2</a></li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
        </ul>
    ',
];

$wrapper = [
	'navbar' => [
		'open' => '<nav class="navbar navbar-expand-lg classic navbar-light navbar-bg-light">',
		'close' => '</nav>',
	],
	'container' => [
		'open' => '<div class="container flex-lg-row flex-nowrap align-items-center">',
		'close' => '</div>',
	],
	'navbarBrand' => [
		'open' => '<div class="navbar-brand w-100">',
		'close' => '</div>',
	],
	'navbarCollapse' => [
		'open' => '<div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start">',
		'close' => '</div>',
	],
	'offcanvasHeader' => [
		'open' => '<div class="offcanvas-header d-lg-none">',
		'close' => '</div>',
	],
	'offcanvasBody' => [
		'open' => '<div class="offcanvas-body ms-lg-auto d-flex flex-column h-100">',
		'close' => '</div>',
	],
];

echo createCustomNavbar($config, $wrapper);
