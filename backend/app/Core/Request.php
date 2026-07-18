<?php

namespace App\Core;

class Request
{
    /**
     * Route parameters
     */
    private static array $params = [];

    /**
     * HTTP Method
     */
    public static function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $method;
    }

    /**
     * URI
     */
    public static function uri(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Base path of your application (dynamically resolved)
    $basePath = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }
    }

    // Remove base path
    if (!empty($basePath) && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath));
    }

    $uri = rtrim($uri, '/');

    return $uri ?: '/';
}

    /**
     * JSON Body
     */
    public static function body(): array
{
    if (
        isset($_SERVER['CONTENT_TYPE']) &&
        str_contains($_SERVER['CONTENT_TYPE'], 'multipart/form-data')
    ) {
        return $_POST;
    }

    $input = file_get_contents("php://input");

    if (!$input) {
        return [];
    }

    return json_decode($input, true) ?? [];
}

    /**
     * Query Parameters
     */
    public static function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * POST Data
     */
    public static function post(?string $key = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? null;
    }

    /**
     * Uploaded Files
     */
    public static function files(): array
    {
        return $_FILES;
    }

    /**
     * HTTP Header
     */
    public static function header(string $name): ?string
    {
        $headers = getallheaders();

        return $headers[$name] ?? null;
    }

    /**
     * Store Route Parameters
     */
    public static function setParams(array $params): void
    {
        self::$params = $params;
    }

    /**
     * Get All Route Parameters
     */
    public static function params(): array
    {
        return self::$params;
    }

    /**
     * Get Single Route Parameter
     */
    public static function param(string $key): mixed
    {
        return self::$params[$key] ?? null;
    }

    /**
     * Check if Request has JSON Body
     */
    public static function has(string $key): bool
    {
        $body = self::body();

        return array_key_exists($key, $body);
    }

    /**
     * Get Input Value
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        $body = self::body();

        return $body[$key] ?? $default;
    }
}