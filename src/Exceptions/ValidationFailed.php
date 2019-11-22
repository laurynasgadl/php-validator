<?php


namespace Luur\Validator\Exceptions;

use Exception;

class ValidationFailed extends Exception
{
    public function __construct(array $errorBag = []) {
        parent::__construct('Validation failed: ' . $this->formatErrorBag($errorBag));
    }

    /**
     * @param array $errorBag
     * @return string
     */
    protected function formatErrorBag(array $errorBag) {
        return implode(',', array_map(function ($v, $k) {
                return $k . '->' . implode('|', $v);
                }, $errorBag, array_keys($errorBag)
            )
        );
    }
}
