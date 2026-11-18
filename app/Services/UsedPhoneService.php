<?php

namespace App\Services;

use App\Models\UsedPhone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsedPhoneService
{
    public function create(array $data, int $userId, ?string $imagePath): UsedPhone
    {
        unset($data['image'], $data['remove_image']);
        $data['image_path'] = $imagePath;
        $data['created_by'] = $userId;

        try {
            return DB::transaction(fn (): UsedPhone => UsedPhone::create($data));
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['imei' => 'رقم IMEI أو الرقم التسلسلي مسجل بالفعل.']);
            }
            throw $exception;
        }
    }

    public function update(UsedPhone $phone, array $data, ?string $newImagePath): array
    {
        return DB::transaction(function () use ($phone, $data, $newImagePath): array {
            $locked = UsedPhone::query()->lockForUpdate()->findOrFail($phone->id);
            $removeImage = (bool) ($data['remove_image'] ?? false);
            unset($data['image'], $data['remove_image']);
            $oldImage = $locked->image_path;
            if ($newImagePath !== null) {
                $data['image_path'] = $newImagePath;
            } elseif ($removeImage) {
                $data['image_path'] = null;
            } else {
                unset($data['image_path']);
            }
            $locked->update($data);

            return [$locked->fresh(), $newImagePath !== null || $removeImage ? $oldImage : null];
        });
    }

    public function delete(UsedPhone $phone): ?string
    {
        return DB::transaction(function () use ($phone): ?string {
            $locked = UsedPhone::query()->lockForUpdate()->findOrFail($phone->id);
            if ($locked->status === 'sold') {
                throw ValidationException::withMessages(['used_phone' => 'لا يمكن حذف جهاز مسجل كمباع. غيّر حالته أولاً إذا كان ذلك صحيحاً.']);
            }
            $path = $locked->image_path;
            $locked->delete();

            return $path;
        });
    }
}
