<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Testify\OpenApi3\SchemaObject;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Spryker\Glue\Testify\OpenApi3\Property\PropertyDefinition;

class ObjectSpecification implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array<\Spryker\Glue\Testify\OpenApi3\Property\PropertyDefinition>
     */
    protected $properties = [];

    /**
     * @param string $key
     * @param \Spryker\Glue\Testify\OpenApi3\Property\PropertyDefinition $property
     *
     * @return static
     */
    public function setProperty(string $key, PropertyDefinition $property): self
    {
        if ($this->offsetExists($key)) {
            trigger_error(sprintf('Property is already added before: %s::%s', static::class, $key), E_USER_WARNING);

            return $this;
        }

        $this->properties[$key] = $property;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->properties);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->properties);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->properties);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->properties[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        trigger_error(sprintf('Trying to set readonly property: %s::%s', static::class, $offset), E_USER_WARNING);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        trigger_error(sprintf('Trying to unset readonly property: %s::%s', static::class, $offset), E_USER_WARNING);
    }
}
