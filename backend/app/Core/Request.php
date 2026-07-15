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
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * URI
     */
    public static function uri(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Base path of your application
    $basePath = '/allstag-insight-hub-main/allstag-insight-hub-main/backend/public';
    // $basePath = '/allstag/backend/public';

    // Remove base path
    if (str_starts_with($uri, $basePath)) {
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
        $input = file_get_contents("php://input");

        if (!$input) {
            return [];
        }

        return json_decode($input, true) ?? [];
    }

    /**
     * Query Parameters
     */
    public static function query(?string $key = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? null;
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