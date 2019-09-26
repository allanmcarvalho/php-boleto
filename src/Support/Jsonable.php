<?php

namespace PhpBoleto\Support;

/**
 * Interface Jsonable
 * @package PhpBoleto\Support
 */
interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0);
}
