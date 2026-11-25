<?php

namespace App\Repositories;

use App\Models\Instructor;
use App\Services\UploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class InstructorRepository
{
    public static function model()
    {
        return Instructor::class;
    }

    public static function findById(int $id): ?Instructor
    {
        return static::model()::query()->find($id);
    }

    public static function findByIdOrFail(int $id): Instructor
    {
        return static::model()::query()->findOrFail($id);
    }

    /** Active instructors, newest first (old InstructorController::getActiveInstructors). */
    public static function activeOrdered()
    {
        return static::model()::query()->active()->orderBy('id', 'desc')->get();
    }

    /**
     * Active legacy `instructors`-table rows for the homepage, with the
     * `job_title` alias the views expect (the column is `designation`).
     * Unlike activeOrdered(), no ordering: the old homepage code had none.
     */
    public static function active()
    {
        return static::model()::query()->where('status', 1)->get()->map(function ($item) {
            $item->job_title = $item->designation;

            return $item;
        });
    }

    public static function findActiveById(int $id): ?Instructor
    {
        return static::model()::query()->where('id', $id)->where('status', 1)->first();
    }

    public static function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return static::model()::query()->paginate($perPage);
    }

    public static function storeByRequest(array $data, ?UploadedFile $image = null): Instructor
    {
        // As the old controller did: $request->status == '1' ? 1 : 0.
        $data['status'] = ($data['status'] ?? null) == '1' ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        if ($image) {
            $data['image'] = static::storeImage($image);
        }

        $record = static::model()::query()->create($data);

        return $record->fresh();
    }

    public static function updateByRequest(Instructor $record, array $data, ?UploadedFile $image = null): bool
    {
        // As the old controller did: $request->has('status') ? 1 : 0.
        $data['status'] = isset($data['status']) && $data['status'] !== '' ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        if ($image) {
            UploadService::delete($record->image);
            $data['image'] = static::storeImage($image);
        }

        $updated = $record->update($data);

        return $updated;
    }

    public static function delete(Instructor $record): bool
    {
        UploadService::delete($record->image);

        return (bool) $record->delete();
    }

    public static function updateStatus(Instructor $record, mixed $status): bool
    {
        return $record->update(['status' => $status]);
    }

    /**
     * The old controller's manual upload: instructor_<time>.<ext>
     * under public/uploads/instructors/.
     */
    protected static function storeImage(UploadedFile $image): string
    {
        $imageName = 'instructor_'.time().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('uploads/instructors/'), $imageName);

        return 'uploads/instructors/'.$imageName;
    }
}
