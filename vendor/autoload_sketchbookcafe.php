<?php
spl_autoload_register(function($class) {
    // Clean
    $clean  = str_replace('\\','/',$class) . '.php';
    $base   = basename($clean);

    // Lowercase folders but keep class name as OrIgiNaL
    $clean  = strtolower($clean);
    $file   = str_replace(strtolower($base),$base,$clean);

    require __DIR__ . '/' . $file;
});