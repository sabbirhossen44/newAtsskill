<?php

namespace App\Http\Controllers\Concerns;

class SessionCartItem
{
    public string $rowId;
    public $id;
    public string $name;
    public int $qty = 1;
    public $price;
    public int $weight = 0;
    public object $options;

    public function __construct(string $rowId, array $data)
    {
        $this->rowId = $rowId;
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->qty = (int) ($data['qty'] ?? 1);
        $this->price = $data['price'] ?? 0;
        $this->weight = (int) ($data['weight'] ?? 0);
        $this->options = (object) ($data['options'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'rowId' => $this->rowId,
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'weight' => $this->weight,
            'options' => (array) $this->options,
        ];
    }
}

