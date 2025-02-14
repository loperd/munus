<?php

declare(strict_types=1);

namespace Munus\Collection;

use Munus\Collection\Iterator\ArrayIterator;
use Munus\Collection\Iterator\EmptyIterator;
use Munus\Collection\Iterator\SingletonIterator;
use Munus\Exception\NoSuchElementException;

/**
 * @template T
 */
class Iterator implements \Iterator
{
    private Traversable $traversable;

    private Traversable $current;

    private int $index;

    /**
     * @param Traversable<T> $traversable
     */
    public function __construct(Traversable $traversable)
    {
        $this->traversable = $this->current = $traversable;
        $this->index = 0;
    }

    /**
     * @template U
     *
     * @param U ...$elements
     *
     * @return self<U>
     */
    public static function of(...$elements): self
    {
        if (count($elements) === 1) {
            return new SingletonIterator(current($elements));
        }

        return new ArrayIterator($elements);
    }

    public static function empty(): self
    {
        return EmptyIterator::instance();
    }

    public static function fromIterable(iterable $elements): self
    {
        if ($elements instanceof self) {
            return $elements;
        }

        if ($elements instanceof Traversable) {
            return $elements->getIterator();
        }

        if (is_array($elements)) {
            $elements = [...$elements];
        }

        return new ArrayIterator($elements);
    }

    public function hasNext(): bool
    {
        return !$this->current->isEmpty();
    }

    /**
     * @return T
     */
    #[\ReturnTypeWillChange]
    public function next(): mixed
    {
        $result = $this->current->head();
        $this->current = $this->current->tail();
        ++$this->index;

        return $result;
    }

    /**
     * @return array<T>
     */
    public function toArray(): array
    {
        $elements = [];
        while ($this->hasNext()) {
            $elements[] = $this->next();
        }

        return $elements;
    }

    /**
     * @return T
     */
    public function current(): mixed
    {
        return $this->current->head();
    }

    /**
     * @return int
     */
    public function key(): mixed
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->hasNext();
    }

    public function rewind(): void
    {
        $this->current = $this->traversable;
    }

    /**
     * @param callable(T,T):T $operation
     *
     * @return T
     */
    public function reduce(callable $operation)
    {
        if (!$this->hasNext()) {
            throw new NoSuchElementException('reduce on empty Iterator');
        }

        $accumulator = $this->next();
        while ($this->hasNext()) { // @phpstan-ignore-line
            $accumulator = $operation($accumulator, $this->next());
        }

        return $accumulator;
    }

    /**
     * @template U
     *
     * @param U $zero
     *
     * @return U
     */
    public function fold($zero, callable $combine)
    {
        while ($this->hasNext()) {
            $zero = $combine($zero, $this->next());
        }

        return $zero;
    }
}
