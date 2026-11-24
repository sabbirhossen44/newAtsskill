<?php

namespace App\Repositories;

use App\Models\CertificateBuilder;
use App\Models\CertificateBuilderItem;

class CertificateBuilderRepository
{
    public static function model()
    {
        return CertificateBuilder::class;
    }

    public static function findById(int $id): ?CertificateBuilder
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(array $data): CertificateBuilder
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $builder = static::model()::query()->create($data);

        static::syncItems($items);

        return $builder->fresh();
    }

    public static function updateByRequest(CertificateBuilder $builder, array $data): bool
    {
        $items = $data['items'] ?? null;
        unset($data['items']);

        $updated = $builder->update($data);

        if (is_array($items)) {
            static::syncItems($items);
        }

        return $updated;
    }

    protected static function syncItems(array $items): void
    {
        // certificate_builder_items has no builder FK in the dump;
        // items are global positioned elements, replaced wholesale.
        CertificateBuilderItem::query()->delete();

        foreach ($items as $item) {
            CertificateBuilderItem::query()->create($item);
        }
    }
}
