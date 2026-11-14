<?php

namespace App\Filament\Resources\SetReviewResource\Pages;

use App\Filament\Resources\SetReviewResource;
use Filament\Resources\Pages\EditRecord;

class EditSetReview extends EditRecord
{
    protected static string $resource = SetReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // ─── Flatten content JSON into virtual form fields ────────────────────────

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $ct = $data['content'] ?? [];

        $data['seo_introduction']          = is_string($ct['seo_introduction'] ?? null)          ? $ct['seo_introduction']          : '';
        $data['customization_guidance']    = is_string($ct['customization_guidance'] ?? null)    ? $ct['customization_guidance']    : '';
        $data['customization_cautions']    = is_string($ct['customization_cautions'] ?? null)    ? $ct['customization_cautions']    : '';
        $data['config_strengths_limitations'] = is_string($ct['config_strengths_limitations'] ?? null) ? $ct['config_strengths_limitations'] : '';
        $data['who_should_use']            = is_string($ct['who_should_use'] ?? null)            ? $ct['who_should_use']            : '';
        $data['final_verdict']             = is_string($ct['final_verdict'] ?? null)             ? $ct['final_verdict']             : '';
        $data['risk_disclaimer']           = is_string($ct['risk_disclaimer'] ?? null)           ? $ct['risk_disclaimer']           : '';

        // Thumbnail — derive filename from stored URL
        if (! empty($data['thumbnail_url'])) {
            $data['thumbnail_file'] = preg_replace('#^/images/eas/#', '', $data['thumbnail_url']);
        }

        return $data;
    }

    // ─── Pack virtual fields back into content JSON ───────────────────────────

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $ct = $this->record->content ?? [];

        $contentFields = [
            'seo_introduction', 'customization_guidance', 'customization_cautions',
            'config_strengths_limitations', 'who_should_use', 'final_verdict', 'risk_disclaimer',
        ];

        foreach ($contentFields as $field) {
            if (array_key_exists($field, $data)) {
                $ct[$field] = $data[$field] ?: null;
                unset($data[$field]);
            }
        }

        $data['content'] = $ct ?: null;

        // Thumbnail
        if (! empty($data['thumbnail_file'])) {
            $data['thumbnail_url'] = '/images/eas/' . ltrim($data['thumbnail_file'], '/');
        }
        unset($data['thumbnail_file']);

        return $data;
    }
}
