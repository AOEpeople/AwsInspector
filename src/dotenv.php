<?php

if (is_readable(getcwd() . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = new Dotenv\Dotenv(getcwd());
    $dotenv->load();
} elseif (is_readable(getcwd() . DIRECTORY_SEPARATOR . '.env.default')) {
    $dotenv = new Dotenv\Dotenv(getcwd(), '.env.default');
    $dotenv->load();
}