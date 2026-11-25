<?php

namespace App\Repositories;

use App\Models\TeamMember;
use App\Services\UploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class TeamMemberRepository
{
    public static function model()
    {
        return TeamMember::class;
    }

    public static function findById(int $id): ?TeamMember
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(int $id): TeamMember
    {
        return static::model()::query()->findOrFail($id);
    }

    public static function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return static::model()::query()->orderBy('sort_order', 'asc')->paginate($perPage);
    }

    public static function storeByRequest(array $data, ?UploadedFile $image = null): TeamMember
    {
        // As the old controller did: $request->has('status') ? 1 : 0.
        $data['status'] = isset($data['status']) && $data['status'] !== '' ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        $teamMember = static::model()::query()->create($data);

        if ($image) {
            $teamMember->update([
                'image' => UploadService::store($image, 'uploads/team-members/'),
            ]);
        }

        return $teamMember->fresh();
    }

    public static function updateByRequest(TeamMember $teamMember, array $data, ?UploadedFile $image = null): bool
    {
        $data['status'] = isset($data['status']) && $data['status'] !== '' ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        $updated = $teamMember->update($data);

        if ($image) {
            $teamMember->update([
                'image' => UploadService::store($image, 'uploads/team-members/', $teamMember->image),
            ]);
        }

        return $updated;
    }

    public static function delete(TeamMember $teamMember): bool
    {
        UploadService::delete($teamMember->image);

        return (bool) $teamMember->delete();
    }

    public static function toggleStatus(TeamMember $teamMember): int
    {
        $status = $teamMember->status == 1 ? 0 : 1;

        $teamMember->update(['status' => $status]);

        return $status;
    }
}
