<?php

namespace Enadstack\CountryData\Support;

use Enadstack\CountryData\Enums\AreaType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Every query on Area returns this collection, so any set of areas can be
 * narrowed or shaped without another query:
 *
 *   Areas::in('JO', 'Amman')->neighborhoods();
 *   $city->areas->districts();
 *   Areas::in('JO', 'Amman')->tree();
 *
 * @extends Collection<int, \Enadstack\CountryData\Models\Area>
 */
class AreaCollection extends Collection
{
    public function ofType(AreaType|string $type): static
    {
        $type = $type instanceof AreaType ? $type->value : $type;

        return $this->filter(fn ($area) => $area->type === $type)->values();
    }

    public function districts(): static
    {
        return $this->ofType(AreaType::District);
    }

    public function neighborhoods(): static
    {
        return $this->ofType(AreaType::Neighborhood);
    }

    public function streets(): static
    {
        return $this->ofType(AreaType::Street);
    }

    public function zones(): static
    {
        return $this->ofType(AreaType::Zone);
    }

    /** Areas whose parent is not in this collection (districts, zones, standalone areas). */
    public function roots(): static
    {
        $ids = $this->modelKeys();

        return $this->filter(fn ($area) => $area->parent_id === null || ! in_array($area->parent_id, $ids, true))->values();
    }

    /**
     * The two-level tree built from this collection alone: its roots, each with
     * a `children` relation holding its areas from the same collection. Works on
     * copies, so shared (cached) models are never modified.
     */
    public function tree(): static
    {
        $byParent = $this->groupBy('parent_id');

        return $this->roots()->map(function ($root) use ($byParent) {
            $root = clone $root;

            return $root->setRelation('children', new static(($byParent[$root->getKey()] ?? new static)->values()->all()));
        });
    }
}
