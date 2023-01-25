<?php

/**
 * @param $string
 * @return bool
 */
function isJSON($string): bool
{
    return is_string($string) && is_array(json_decode($string, true));
}
