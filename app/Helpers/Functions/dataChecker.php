<?php

/**
 * @param $string
 * @return bool
 */
function isJSON($string): bool
{
    return is_string($string) && is_array(json_decode($string, true));
}

function isBase64Encoded($data): bool
{
    // Check if the data is valid Base64
    if (base64_encode(base64_decode($data, true)) === $data) {
        return true;
    }
    return false;
}

function isBinaryEncoded($data): bool
{
    return !ctype_print($data);
}
