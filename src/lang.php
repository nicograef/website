<?php

function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    return str_contains($header, 'de') ? 'de' : 'en';
}
