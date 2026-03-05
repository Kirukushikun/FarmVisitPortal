<div>
    @php
        $existing = [];
        $ids = $uploadedPhotoIds['permit_photos'] ?? [];
        $urls = $uploadedPhotoUrls['permit_photos'] ?? [];

        foreach ($ids as $i => $id) {
            $existing[] = [
                'id' => $id,
                'url' => $urls[$i] ?? null,
            ];
        }

        $existing = array_values(array_filter($existing, fn ($p) => !empty($p['url'])));
    @endphp

    <x-photo-attach
        label="Photos"
        name="permit_photos"
        :required="false"
        :existing-photos="$existing"
        :can-upload="$canUpload"
    />
</div>
