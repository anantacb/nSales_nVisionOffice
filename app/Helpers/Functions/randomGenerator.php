<?php

function generateRandomString($length = 10, $upperCaseOnly = false): string
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if (!$upperCaseOnly) {
        $characters = $characters . 'abcdefghijklmnopqrstuvwxyz';
    }
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}
