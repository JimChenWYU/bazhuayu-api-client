<?php

/*
 * This file is part of the jimchen/bazhuoyu-api-client.
 *
 * (c) JimChen <18219111672@163.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use Bazhuayu\Exception\ClientException;
use Bazhuayu\Exception\ServerException;

function handleBadResponseException(callable $func)
{
    try {
        return $func();
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        throw new ClientException($e->getMessage(), $e->getCode());
    } catch (\GuzzleHttp\Exception\ServerException $e) {
        throw new ServerException($e->getMessage(), $e->getCode());
    }
}
