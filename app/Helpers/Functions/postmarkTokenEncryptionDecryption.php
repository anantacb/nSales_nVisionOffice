<?php


use Illuminate\Encryption\Encrypter;

function encryptPostmarkToken($token): string
{
    $encryptionKey = env("POSTMARK_SERVER_API_TOKEN_ENCRYPTION_KEY");
    $encryptor = new Encrypter($encryptionKey, config('app.cipher'));
    return $encryptor->encrypt($token);
}

function decryptPostmarkToken($token): string
{
    $encryptionKey = env("POSTMARK_SERVER_API_TOKEN_ENCRYPTION_KEY");
    $encryptor = new Encrypter($encryptionKey, config('app.cipher'));
    return $encryptor->decrypt($token);
}
