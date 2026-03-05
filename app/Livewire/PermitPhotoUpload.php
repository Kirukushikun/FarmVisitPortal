<?php

namespace App\Livewire;

use App\Models\Permit;
use App\Models\PermitPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PermitPhotoUpload extends Component
{
    use WithFileUploads;

    public Permit $permit;

    public bool $canUpload = true;

    public array $photoUploads = [];

    public array $uploadedPhotoIds = [];

    public array $uploadedPhotoUrls = [];

    public function mount(Permit $permit, bool $canUpload = true): void
    {
        $this->permit = $permit;
        $this->canUpload = $canUpload;

        $photos = $permit->photos()->orderBy('id')->get();
        $this->uploadedPhotoIds['permit_photos'] = $photos->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->uploadedPhotoUrls['permit_photos'] = $photos
            ->map(fn (PermitPhoto $photo) => url(Storage::url($photo->path)))
            ->toArray();
    }

    public function updated($name, $value): void
    {
        if (!$this->canUpload) {
            return;
        }

        if (!is_string($name) || !str_starts_with($name, 'photoUploads.')) {
            return;
        }

        $photoKey = substr($name, strlen('photoUploads.'));
        $photoKey = explode('.', $photoKey)[0];

        $files = $this->photoUploads[$photoKey] ?? [];
        if (!is_array($files) || empty($files)) {
            return;
        }

        $this->handlePermitPhotoUpload($photoKey, $files);
    }

    protected function handlePermitPhotoUpload(string $photoKey, array $files): void
    {
        if (!$this->canUpload) {
            return;
        }

        $this->validateOnly("photoUploads.{$photoKey}.*", [
            "photoUploads.{$photoKey}.*" => ['image', 'max:15360'],
        ]);

        $timestamp = now()->format('Ymd_His');

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $uuid = Str::uuid()->getHex();
            $ext = method_exists($file, 'getClientOriginalExtension') ? $file->getClientOriginalExtension() : 'jpg';

            $originalName = method_exists($file, 'getClientOriginalName') ? (string) $file->getClientOriginalName() : 'photo';
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $safePhotoName = Str::slug($baseName);
            if ($safePhotoName === '') {
                $safePhotoName = 'photo';
            }

            $filename = "permit_{$this->permit->id}_{$timestamp}_{$photoKey}_{$safePhotoName}-{$uuid}.{$ext}";
            $path = $file->storeAs('permit-photos', $filename, 'public');

            if (!$path) {
                continue;
            }

            $photo = PermitPhoto::create([
                'permit_id' => (int) $this->permit->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $originalName,
                'mime_type' => method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : null,
                'size' => method_exists($file, 'getSize') ? $file->getSize() : null,
            ]);

            $url = url(Storage::url($path));

            $this->uploadedPhotoIds[$photoKey][] = (int) $photo->id;
            $this->uploadedPhotoUrls[$photoKey][] = $url;

            $this->dispatch('photoStored', photoKey: $photoKey, photoId: (int) $photo->id, url: $url);
        }

        $this->photoUploads[$photoKey] = [];
    }

    public function deleteUploadedPhoto(string $photoKey, int $photoId): void
    {
        if (!$this->canUpload) {
            return;
        }

        $ids = $this->uploadedPhotoIds[$photoKey] ?? [];
        if (!in_array($photoId, $ids, true)) {
            return;
        }

        $photo = PermitPhoto::query()
            ->where('id', $photoId)
            ->where('permit_id', $this->permit->id)
            ->first();

        if (!$photo) {
            return;
        }

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        $this->uploadedPhotoIds[$photoKey] = array_values(array_filter(
            $this->uploadedPhotoIds[$photoKey] ?? [],
            fn ($id) => (int) $id !== $photoId
        ));

        $photoUrl = url(Storage::url($photo->path));
        $this->uploadedPhotoUrls[$photoKey] = array_values(array_filter(
            $this->uploadedPhotoUrls[$photoKey] ?? [],
            fn ($url) => (string) $url !== (string) $photoUrl
        ));
    }

    public function render()
    {
        return view('livewire.permit-photo-upload');
    }
}
