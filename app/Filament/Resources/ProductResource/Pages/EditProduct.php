<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // ─── Flatten JSON columns into virtual form fields ────────────────────────

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $ext = $data['extended'] ?? [];
        $sr  = $data['set_review_data'] ?? [];

        // Short Description tab — prefer top-level column, fall back to extended
        $data['short_description'] = $data['short_description']
            ?? $ext['short_description']
            ?? '';

        // Description tab
        $data['description_md'] = $ext['description_md'] ?? '';

        // Thumbnail tab — derive virtual field from stored URL
        // FileUpload expects just the filename relative to the disk directory
        if (! empty($data['thumbnail_url'])) {
            // Strip leading /images/eas/ to get disk-relative path, then get just filename
            $relative = ltrim($data['thumbnail_url'], '/');
            $relative = preg_replace('#^images/eas/#', '', $relative);
            $data['thumbnail_file'] = $relative;
        }

        // Gallery tab — map extended.images[] to repeater rows
        $data['gallery_images'] = collect($ext['images'] ?? [])
            ->map(function (array $img) {
                // Strip /images/eas/ prefix to get disk-relative path
                $relative = preg_replace('#^/images/eas/#', '', $img['url'] ?? '');
                return [
                    'file'     => $relative,
                    'alt_text' => $img['alt_text'] ?? '',
                    'section'  => $img['section'] ?? 'overview',
                ];
            })
            ->values()
            ->all();

        // Set Review tab
        $data['sr_seo_introduction']      = is_string($sr['seo_introduction'] ?? null)      ? $sr['seo_introduction']      : '';
        $data['sr_customization_guidance'] = is_string($sr['customization_guidance'] ?? null) ? $sr['customization_guidance'] : '';
        $data['sr_final_verdict']          = is_string($sr['final_verdict'] ?? null)          ? $sr['final_verdict']          : '';

        return $data;
    }

    // ─── Pack virtual form fields back into JSON columns ─────────────────────

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // ── Short Description ─────────────────────────────────────────────────
        // Write to both the top-level column and extended, so the API always finds it.
        $ext = $this->record->extended ?? [];
        $ext['short_description'] = $data['short_description'] ?? null;
        $data['extended'] = $ext;
        // top-level column is handled by Filament automatically (it's a real column)

        // ── Description ──────────────────────────────────────────────────────
        $ext['description_md'] = $data['description_md'] ?? $ext['description_md'] ?? null;
        $data['extended'] = $ext;

        // ── Thumbnail ────────────────────────────────────────────────────────
        $thumbFile = $data['thumbnail_file'] ?? null;
        if ($thumbFile) {
            // Filament stores the disk-relative path; prepend /images/eas/ for the URL
            $data['thumbnail_url'] = '/images/eas/' . ltrim($thumbFile, '/');
        }

        // ── Gallery ──────────────────────────────────────────────────────────
        $galleryRows = $data['gallery_images'] ?? [];
        $images = collect($galleryRows)
            ->filter(fn ($row) => ! empty($row['file']))
            ->map(function (array $row) {
                $url = '/images/eas/' . ltrim($row['file'], '/');
                return [
                    'url'      => $url,
                    'alt_text' => $row['alt_text'] ?? '',
                    'caption'  => $row['alt_text'] ?? '',
                    'section'  => $row['section'] ?? 'overview',
                ];
            })
            ->values()
            ->all();

        $ext = $data['extended'];
        $ext['images'] = $images;
        $data['extended'] = $ext;

        // ── Set Review ───────────────────────────────────────────────────────
        $sr = $this->record->set_review_data ?? [];
        if (isset($data['sr_seo_introduction']))       $sr['seo_introduction']       = $data['sr_seo_introduction'];
        if (isset($data['sr_customization_guidance'])) $sr['customization_guidance'] = $data['sr_customization_guidance'];
        if (isset($data['sr_final_verdict']))           $sr['final_verdict']          = $data['sr_final_verdict'];
        $data['set_review_data'] = $sr ?: null;

        // Remove virtual fields — not real columns (short_description IS a real column, keep it)
        unset($data['description_md'], $data['thumbnail_file'], $data['gallery_images'],
              $data['sr_seo_introduction'], $data['sr_customization_guidance'], $data['sr_final_verdict']);

        return $data;
    }
}
