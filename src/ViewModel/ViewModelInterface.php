<?php
declare(strict_types=1);

namespace CakePages\ViewModel;

/**
 * ViewModel Interface
 *
 * Contract for all ViewModel classes in the CakePages plugin.
 * ViewModels are data containers that prepare data for views.
 */
interface ViewModelInterface extends \JsonSerializable
{
    /**
     * Convert the ViewModel to an array
     *
     * @return array<string, mixed> Array representation of the ViewModel
     */
    public function toArray(): array;
}

