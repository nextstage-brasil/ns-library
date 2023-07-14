<?php

namespace NsLibrary\Controller;

use Closure;
use Exception;
use NsLibrary\Exceptions\UniqueException;

/**
 * The ThrowExceptionBySQLError class handles throwing exceptions based on SQL errors.
 */
class ThrowExceptionBySQLError
{
    /**
     * An array that maps SQL error codes to exception classes and friendly error messages.
     *
     * @var array
     */
    private static $mappedErrors = [
        '[23505]' => ['exception' => UniqueException::class, 'message' => null, 'closure' => null]
    ];

    /**
     * Adds a new mapping for SQL error code to exception class and optional friendly error message.
     *
     * @param string $key                  The SQL error code to map.
     * @param string $exceptionClassname   The fully qualified name of the exception class to throw.
     * @param string|null $friendlyMessage The friendly error message to use, or null to use the original exception message.
     * @param Closure|null $fn             The function to execute on assert message.
     *
     * @throws Exception If the provided exception class does not exist.
     */
    public static function addMappedErrors(
        string $key,
        string $exceptionClassname,
        ?string $friendlyMessage = null,
        ?Closure $fn = null
    ) {
        if (!class_exists($exceptionClassname)) {
            throw new Exception("Classname $exceptionClassname not found for ThrowExceptionBySQLError function");
        }
        self::$mappedErrors[$key] = ['exception' => $exceptionClassname, 'message' => $friendlyMessage, 'closure' => $fn];
    }

    /**
     * Handles the given exception by checking if it matches any of the mapped SQL error codes,
     * and throws the corresponding exception with the provided friendly error message if applicable.
     *
     * @param Exception $exc The exception to handle.
     * @param Closure|null $fn An optional closure to execute before throwing the exception.
     *
     * @throws Exception If a mapped SQL error code is found and a corresponding exception is thrown.
     */
    public static function handle(Exception $exc): void
    {
        foreach (self::$mappedErrors as $chave => $item) {
            if (stripos($exc->getMessage(), $chave) !== false) {

                if (null !== $item['closure'] && is_callable($item['closure'])) {
                    call_user_func($item['closure'], $exc);
                }

                throw new $item['exception']($item['message'] ?? $exc->getMessage());
            }
        }
    }
}
