<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'EA Content';
    protected static ?string $modelLabel = 'EA';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Content')->tabs([

                Tabs\Tab::make('Short Description')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Short Description')
                            ->helperText('Appears on the product page below the "View on vendor site" link. Keep it to 2–4 sentences.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        MarkdownEditor::make('description_md')
                            ->label('Product Description (Markdown)')
                            ->helperText('Use the image button (📎) to upload images. They save to /images/eas/{slug}/content/ automatically.')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => $record?->slug . '/content')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Thumbnail')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('thumbnail_file')
                            ->label('Product Thumbnail')
                            ->helperText('Shown on the product card and hero area. Recommended size: 800×600px.')
                            ->image()
                            ->imagePreviewHeight('200')
                            ->disk('ea_images')
                            ->directory(fn ($record) => $record?->slug)
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Gallery')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Repeater::make('gallery_images')
                            ->label('Gallery Images')
                            ->helperText('These appear as a scrollable gallery on the product page.')
                            ->schema([
                                FileUpload::make('file')
                                    ->label('Image')
                                    ->image()
                                    ->imagePreviewHeight('120')
                                    ->disk('ea_images')
                                    ->directory(fn ($record) => ($record?->slug ?? 'unknown') . '/gallery')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->columnSpan(1),
                                TextInput::make('alt_text')
                                    ->label('Alt text / Caption')
                                    ->placeholder('e.g. Backtest results 2024')
                                    ->columnSpan(1),
                                Select::make('section')
                                    ->label('Section')
                                    ->options(['overview' => 'Overview', 'set_file' => 'Set Review'])
                                    ->default('overview')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add image')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Set Review')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        MarkdownEditor::make('sr_seo_introduction')
                            ->label('Introduction')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => $record?->slug . '/set-review')
                            ->columnSpanFull(),
                        MarkdownEditor::make('sr_customization_guidance')
                            ->label('Customization Guidance')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => $record?->slug . '/set-review')
                            ->columnSpanFull(),
                        MarkdownEditor::make('sr_final_verdict')
                            ->label('Final Verdict')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => $record?->slug . '/set-review')
                            ->columnSpanFull(),
                    ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Thumb')
                    ->width(60)->height(45)
                    ->defaultImageUrl('/images/placeholder.png'),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('slug')->searchable()->color('gray')->size('sm'),
                TextColumn::make('platform')->badge(),
                TextColumn::make('version')->color('gray')->size('sm'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit content'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit'  => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
