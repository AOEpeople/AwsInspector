<?php

echo "ERdsdsEsdsR";

if (is_readable(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
    echo "ERdsdsER";
    $dotenv = new Dotenv\Dotenv(getcwd());
    $dotenv->load();
} elseif (is_readable(getcwd() . DIRECTORY_SEPARATOR . '.env.default')) {
    echo "ERER";
    $dotenv = new Dotenv\Dotenv(getcwd(), '.env.default');
    $dotenv->load();
}