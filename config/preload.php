<?php declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/var/cache/prod/srcApp_KernelProdContainer.preload.php')) {
    require dirname(__DIR__) . '/var/cache/prod/srcApp_KernelProdContainer.preload.php';
}

if (file_exists(dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';
}
