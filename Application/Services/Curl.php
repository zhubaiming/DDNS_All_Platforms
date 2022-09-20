<?php

namespace App\Services;

class Curl
{
    public function __construct(string $baseUrl, array $headers = [], bool $is_https = false)
    {
        $this->baseUrl = $baseUrl;

        $this->is_https = $is_https;

        $this->header = array_merge_recursive([
            'Content-type:application/json; charset=utf-8'
        ], $headers);
    }

    public function get(string $uri, array $parameters = [])
    {
        $url = $this->joinUri($uri);

        if (!empty($parameters)) {
            $url .= '?' . $this->joinParameter($parameters);
        }

        return $this->sendAndClose($url, 'GET');
    }

    public function post(string $uri, array $parameters)
    {
        $url = $this->joinUri($uri);

        if (!empty($parameters['query'])) {
            $url .= '?' . $this->joinParameter($parameters['query']);
        }

        return $this->sendAndClose($url, 'POST', $parameters['body']);
    }

    public function put(string $uri, array $parameters)
    {
        $url = $this->joinUri($uri);

        if (!empty($parameters['query'])) {
            $url .= '?' . $this->joinParameter($parameters['query']);
        }

        return $this->sendAndClose($url, 'PUT', $parameters['body']);
    }

    private function joinUri($uri)
    {
        $uriBegin = substr($uri, 0, 1);

        if ('/' === substr($this->baseUrl, -1)) {
            if ('/' === $uriBegin) {
                return $this->baseUrl . substr($uri, 1);
            } else {
                return $this->baseUrl . $uri;
            }
        } else {
            if ('/' === $uriBegin) {
                return $this->baseUrl . $uri;
            } else {
                return $this->baseUrl . '/' . $uri;
            }
        }
    }

    private function joinParameter($parameters)
    {
        $result = '';

        foreach ($parameters as $key => $value) {
            $result .= $key . '=' . $value . '&';
        }

        return $result;
    }

    private function sendAndClose($url, $method, $body = [])
    {
        // 初始化一个 cURL 会话，可以设置参数，参数为 url，如果不设置，也可以手动使用 CURLOPT_URL 来设置
        $curl = curl_init($url);

        // 设置 cURL 传输选项
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true); // 在完成交互以后强迫断开连接，不能重用
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true); // 强制获取一个新的连接，替代缓存中的连接
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header); // 一个用来设置HTTP头字段的数组
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 返回原生的（Raw）输出
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if ($this->is_https) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ('GET' !== $method) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body, 312));
        }

        $response = curl_exec($curl);

        curl_close($curl);;

        return json_decode($response, true);
    }
}