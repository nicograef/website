<?php

function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    return strpos($header, 'de') !== false ? 'de' : 'en';
}
