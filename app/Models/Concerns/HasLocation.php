<?php

namespace App\Models\Concerns;

/**
 * Kolom `location` berisi JSON {"lat": -5.28, "lng": 119.61}.
 */
trait HasLocation
{
    public function initializeHasLocation(): void
    {
        $this->mergeCasts(['location' => 'array']);
    }

    public function getLatAttribute(): ?float
    {
        return isset($this->location['lat']) ? (float) $this->location['lat'] : null;
    }

    public function getLngAttribute(): ?float
    {
        return isset($this->location['lng']) ? (float) $this->location['lng'] : null;
    }

    public function hasValidLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }
}
