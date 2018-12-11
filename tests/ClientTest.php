<?php

/*
 * This file is part of the jimchen/bazhuoyu-api-client.
 *
 * (c) JimChen <18219111672@163.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Test;

use Bazhuayu\Client;
use Bazhuayu\Exception\ClientException;
use Bazhuayu\Exception\ServerException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ClientTest extends TestCase
{
    public function testInitCache()
    {
        $client = new Client([
            'cache' => new FilesystemCache(),
        ]);

        $this->assertInstanceOf(FilesystemCache::class, $client->getCache());

        $client = new Client([
            'class'           => FilesystemCache::class,
            'namespace'       => 'Test',
            'defaultLifetime' => 10,
            'directory'       => __DIR__ . '/runtime',
        ]);

        $this->assertInstanceOf(FilesystemCache::class, $client->getCache());
    }

    public function testGetToken()
    {
        $mock = \Mockery::mock(Client::class.'[getToken]', [
            [
                'username' => 'test',
                'password' => 'pwd',
            ]
        ]);
        $mock->shouldReceive('getToken')
            ->once()
            ->andReturn([
                'access_token' => 'abcdefg'
            ]);


        $this->assertEquals($mock->token(), [
            'access_token' => 'abcdefg'
        ]);

        $mock->shouldReceive('getToken')
            ->twice()
            ->andThrowExceptions([
                new ClientException('ClientException'),
                new ServerException('ServerException'),
            ]);

        $this->setExpectedException(ClientException::class, 'ClientException');
        $mock->token();

        $this->setExpectedException(ServerException::class, 'ServerException');
        $mock->token();
    }
}
