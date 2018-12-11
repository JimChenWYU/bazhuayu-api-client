<?php

/*
 * This file is part of the jimchen/bazhuoyu-api-client.
 *
 * (c) JimChen <18219111672@163.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Bazhuayu;

use Bazhuayu\Exception\ClientException;
use Bazhuayu\Exception\ServerException;
use Bazhuayu\Support\AccessToken;
use Bazhuayu\Support\Config;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionException;

class Client
{
    use AccessToken;

    const DATA_API_BASE_URI = 'https://dataapi.bazhuayu.com/';

    /**
     * @var Config
     */
    protected $config;

    /**
     * Client constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);

        $this->initCache();
    }

    /**
     * Initialize Cache.
     *
     * @throws ReflectionException
     */
    protected function initCache()
    {
        $cache = $this->config->get('cache');

        if ($cache instanceof CacheInterface) {
            $this->setCache($cache);
        } elseif (isset($cache['class'])) {
            $concrete = $cache['class'];
            unset($cache['class']);

            $reflector = new ReflectionClass($concrete);

            if (!$reflector->isInstantiable()) {
                $message = "Target [$concrete] is not instantiable.";

                throw new ReflectionException($message);
            }

            if (!$reflector->implementsInterface(CacheInterface::class)) {
                $interface = CacheInterface::class;
                $message = "Target [$concrete] is not implementing {$interface}.";

                throw new ReflectionException($message);
            }

            $constructor = $reflector->getConstructor();

            /*
             * @var CacheInterface $instance
             */
            if (is_null($constructor)) {
                $instance = new $concrete();
            } else {
                $arguments = [];

                foreach ($constructor->getParameters() as $parameter) {
                    if (isset($cache[$parameterName = $parameter->getName()])) {
                        $arguments[$parameterName] = $cache[$parameterName];
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $arguments[$parameterName] = $parameter->getDefaultValue();
                    } else {
                        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

                        throw new ReflectionException($message);
                    }
                }

                $instance = $reflector->newInstanceArgs($arguments);
            }

            $this->setCache($instance);
        }
    }

    /**
     * 获取Token.
     *
     * @param bool $refresh
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function token($refresh = false)
    {
        return handleBadResponseException(function () use ($refresh) {
            return $this->getToken($refresh);
        });
    }

    /**
     * 得到该用户所有的任务组.
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function taskGroup()
    {
        return handleBadResponseException(function () {
            return $this->get('api/TaskGroup', [], $this->getBaseHeaders());
        });
    }

    /**
     * 获取任务组中的任务
     *
     * @param string $taskGroupId
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function task($taskGroupId)
    {
        return handleBadResponseException(function () use ($taskGroupId) {
            return $this->get('api/Task', [
                'taskGroupId' => $taskGroupId,
            ], $this->getBaseHeaders());
        });
    }

    /**
     * 清空任务数据.
     *
     * @param string $taskId
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function removeDataByTaskId($taskId)
    {
        return handleBadResponseException(function () use ($taskId) {
            return $this->post('api/task/RemoveDataByTaskId?'.http_build_query([
                    'taskId' => $taskId,
                ]), [], $this->getBaseHeaders());
        });
    }

    /**
     * 导出一批任务数据.
     *
     * @param string $taskId
     * @param int    $size
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function exportData($taskId, $size = 100)
    {
        return handleBadResponseException(function () use ($taskId, $size) {
            return $this->get('api/notexportdata/gettop', [
                'taskId' => $taskId,
                'size' => $size,
            ], $this->getBaseHeaders());
        });
    }

    /**
     * 标记数据为已导出状态
     *
     * @param string $taskId
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function updateDataStatus($taskId)
    {
        return handleBadResponseException(function () use ($taskId) {
            return $this->post('api/notexportdata/update?'.http_build_query([
                    'taskId' => $taskId,
                ]), [], $this->getBaseHeaders());
        });
    }

    /**
     * 根据起始偏移量获取任务数据.
     *
     * @param string $taskId
     * @param int    $offset
     * @param int    $size
     *
     * @return array
     *
     * @throws ClientException
     * @throws ServerException
     */
    public function getDataOfTaskByOffset($taskId, $offset = 0, $size = 100)
    {
        return handleBadResponseException(function () use ($taskId, $offset, $size) {
            return $this->get('api/alldata/GetDataOfTaskByOffset', [
                'taskId' => $taskId,
                'offset' => $offset,
                'size' => $size,
            ], $this->getBaseHeaders());
        });
    }

    /**
     * Base uri.
     *
     * @return string
     */
    protected function getBaseUri()
    {
        return self::DATA_API_BASE_URI;
    }

    /**
     * @return array
     */
    protected function getBaseHeaders()
    {
        return [
            'Authorization' => 'bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    protected function getCredentials()
    {
        return [
            'username' => $this->config->get('username'),
            'password' => $this->config->get('password'),
            'grant_type' => 'password',
        ];
    }
}
