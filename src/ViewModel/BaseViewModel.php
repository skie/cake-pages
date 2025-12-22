<?php
declare(strict_types=1);

namespace CakePages\ViewModel;

use ReflectionClass;
use ReflectionProperty;

/**
 * Base ViewModel Class
 *
 * Abstract base class for all ViewModels in the CakePages plugin.
 * Provides common functionality for converting ViewModels to arrays and JSON.
 */
abstract class BaseViewModel implements ViewModelInterface
{
    /**
     * Convert the ViewModel to an array
     *
     * Uses reflection to extract all public properties from the ViewModel.
     *
     * @return array<string, mixed> Array representation of the ViewModel
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $result[$name] = $property->getValue($this);
        }

        return $result;
    }

    /**
     * Convert the ViewModel to JSON
     *
     * @return array<string, mixed> Array representation for JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

