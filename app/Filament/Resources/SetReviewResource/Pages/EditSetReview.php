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

        // Plain string/markdown fields
        $data['seo_introduction']             = $ct['seo_introduction'] ?? '';
        $data['customization_guidance']       = $ct['customization_guidance'] ?? '';
        $data['customization_cautions']       = $ct['customization_cautions'] ?? '';
        $data['config_strengths_limitations'] = $ct['config_strengths_limitations'] ?? '';
        $data['final_verdict']                = $ct['final_verdict'] ?? '';
        $data['risk_disclaimer']              = $ct['risk_disclaimer'] ?? '';

        // who_should_use: AI may send an array of strings — join to one string per line
        $wsu = $ct['who_should_use'] ?? '';
        $data['who_should_use'] = is_array($wsu)
            ? implode("\n", array_filter(array_map('strval', $wsu)))
            : (string) $wsu;

        // Array repeater fields
        $data['set_files']      = $ct['set_files']      ?? [];
        $data['load_set_steps'] = $ct['load_set_steps'] ?? [];
        $data['faqs']           = $ct['faqs']           ?? [];

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

        $stringFields = [
            'seo_introduction', 'customization_guidance', 'customization_cautions',
            'config_strengths_limitations', 'who_should_use', 'final_verdict', 'risk_disclaimer',
        ];

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $data)) {
                $ct[$field] = $data[$field] ?: null;
                unset($data[$field]);
            }
        }

        $arrayFields = ['set_files', 'load_set_steps', 'faqs'];
        foreach ($arrayFields as $field) {
            if (array_key_exists($field, $data)) {
                $ct[$field] = $data[$field] ?: [];
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
