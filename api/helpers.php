<?php

use Pecee\SimpleRouter\SimpleRouter as Router;
use Pecee\Http\Url;
use Pecee\Http\Response;
use Pecee\Http\Request;

/**
 * Get url for a route by using either name/alias, class or method name.
 *
 * The name parameter supports the following values:
 * - Route name
 * - Controller/resource name (with or without method)
 * - Controller class name
 *
 * When searching for controller/resource by name, you can use this syntax "route.name@method".
 * You can also use the same syntax when searching for a specific controller-class "MyController@home".
 * If no arguments is specified, it will return the url for the current loaded route.
 *
 * @param string|null $name
 * @param string|array<string>|null $parameters
 * @param array<string>|null $getParams
 * @return \Pecee\Http\Url
 * @throws \InvalidArgumentException
 */
function url(?string $name = null, $parameters = null, ?array $getParams = null): Url
{
    return Router::getUrl($name, $parameters, $getParams);
}

/**
 * @return \Pecee\Http\Response
 */
function response(): Response
{
    return Router::response();
}

/**
 * @return \Pecee\Http\Request
 */
function request(): Request
{
    return Router::request();
}

/**
 * Get input class
 * @param string $index Parameter index name
 * @param string|null $defaultValue Default return value
 * @return string|null
 */
function input($index, $defaultValue = null)
{
    $result = request()->getInputHandler()->value($index, $defaultValue);
    // If array, return first value
    if (is_array($result)) {
        return reset($result);
    } else {
        return $result;
    }
}

/**
 * @param string $url
 * @param int|null $code
 */
function redirect(string $url, ?int $code = null): void
{
    if ($code !== null) {
        response()->httpCode($code);
    }

    response()->redirect($url);
}


/**
 * @param string $message       Message that may be displayed to the user
 * @param string $code          Reponse code to send to the client
 * @param mixed $data           Data to send to the client
 * @return bool
 */
function success(string $message, string $code, $data = null)
{
    Router::response()
        ->httpCode(200)
        ->json([
            'status' => 'success',
            'message' => $message,
            'code' => $code,
            'data' => $data
        ]);
    return true;
}

/**
 * @param string $message       Message that may be displayed to the user
 * @param string $code          Reponse code to send to the client
 * @param int $status           HTTP status code
 * @param mixed $errors         Errors to send to the client
 * @return bool
 */
function error(string $message, string $code, int $status = 400, $errors = null)
{
    Router::response()
        ->httpCode($status)
        ->json([
            'status' => 'error',
            'message' => $message,
            'code' => $code,
            'errors' => $errors
        ]);
    return false;
}

/**
 * @param string $message
 * @return void
 */
function logError($message)
{
    // Log to file
    $date = date('Y-m-d H:i:s');
    $file = fopen(__DIR__ . '/../errors.log', 'a');
    if ($file) {
        fwrite($file, "[$date] $message" . PHP_EOL);
        fclose($file);
    }
    error_log("\e[0;31;41m ERROR \e[0m " . $message);
}

/**
 * @param string $message
 * @return void
 */
function logSuccess($message)
{
    error_log("\e[1;37;42m SUCCESS \e[0m " . $message);
}

/**
 * @param string $message
 * @param bool $toFile
 * @return void
 */
function logInfo($message, $toFile = false)
{
    if ($toFile) {
        $date = date('Y-m-d H:i:s');
        $file = fopen(__DIR__ . '/../info.log', 'a');
        if ($file) {
            fwrite($file, "[$date] $message" . PHP_EOL);
            fclose($file);
        }
    }
    error_log("\e[1;37;44m INFO \e[0m " . $message);
}
