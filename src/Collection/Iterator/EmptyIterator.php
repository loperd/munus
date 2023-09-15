<?php

declare(strict_types=1);

namespace Munus\Collection\Iterator;

use Munus\Collection\Iterator;
use Munus\Exception\NoSuchElementException;

final class EmptyIterator extends Iterator
{
    private function __construct()
    {
    }

    public static function instance(): self
    {
        return new self();
    }

    public function hasNext(): bool
    {
        return false;
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next(): mixed
    {
        throw new NoSuchElementException();
    }
}
