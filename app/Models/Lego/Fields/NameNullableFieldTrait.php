<?php

namespace App\Models\Lego\Fields;

trait NameNullableFieldTrait
{
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $value): void
    {
        $this->name = $value;
    }
}