<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetReviewResource\Pages;
use App\Models\SetReview;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SetReviewResource extends Resource
{
    protected static ?string $model = SetReview::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Setting Reviews';
    protected static ?string $modelLabel = 'Setting Review';
    protected static ?string $pluralModelLabel = 'Setting Reviews';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Set Review')->tabs([

                Tabs\Tab::make('SEO & Meta')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        TextInput::make('h1')
                            ->label('Page Heading (H1)')
                            ->helperText('The main title shown at the top of the page.')
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->helperText('e.g. hedgehog-mt5-settings-guide → /setting-reviews/hedgehog-mt5-settings-guide')
                            ->prefix('/setting-reviews/')
                            ->columnSpanFull(),
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->helperText('Browser tab & Google title. Aim for 50–60 characters.')
                            ->columnSpanFull(),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->helperText('Google snippet. Aim for 150–160 characters.')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('focus_keyword')
                            ->label('Focus Keyword')
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Published (live on /setting-reviews/)')
                            ->helperText('Turn on to make this page publicly accessible.')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Thumbnail')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('thumbnail_file')
                            ->label('Set Review Thumbnail')
                            ->helperText('Shown as the cover image.')
                            ->image()
                            ->imagePreviewHeight('200')
                            ->disk('ea_images')
                            ->directory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Introduction')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        MarkdownEditor::make('seo_introduction')
                            ->label('Introduction')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Set Files')
                    ->icon('heroicon-o-document-duplicate')
                    ->schema([
                        Repeater::make('set_files')
                            ->label('Set Files')
                            ->helperText('Each set file with its full configuration guide.')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Set File Name')
                                    ->required()
                                    ->columnSpanFull(),
                                MarkdownEditor::make('body_md')
                                    ->label('Configuration Guide')
                                    ->fileAttachmentsDisk('ea_images')
                                    ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Add set file')
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? 'Set File')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Load Steps')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->schema([
                        Repeater::make('load_set_steps')
                            ->label('How to Load the Set File')
                            ->helperText('Step-by-step loading instructions per platform (e.g. MT4, MT5).')
                            ->schema([
                                TextInput::make('platform')
                                    ->label('Platform')
                                    ->placeholder('e.g. MetaTrader 4, MetaTrader 5')
                                    ->required()
                                    ->columnSpanFull(),
                                MarkdownEditor::make('steps')
                                    ->label('Steps')
                                    ->fileAttachmentsDisk('ea_images')
                                    ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Add platform')
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['platform'] ?? 'Platform')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Configuration')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        MarkdownEditor::make('customization_guidance')
                            ->label('Customization Guidance')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->columnSpanFull(),
                        MarkdownEditor::make('customization_cautions')
                            ->label('Cautions')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->columnSpanFull(),
                        MarkdownEditor::make('config_strengths_limitations')
                            ->label('Strengths & Limitations')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->columnSpanFull(),
                        Textarea::make('who_should_use')
                            ->label('Who Should Use This')
                            ->helperText('Write as markdown, or put each bullet point on its own line.')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('FAQs')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Repeater::make('faqs')
                            ->label('Frequently Asked Questions')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Question')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('answer')
                                    ->label('Answer')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Add FAQ')
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['question'] ?? 'FAQ')
                            ->columnSpanFull(),
                    ]),

                Tabs\Tab::make('Verdict')
                    ->icon('heroicon-o-star')
                    ->schema([
                        MarkdownEditor::make('final_verdict')
                            ->label('Final Verdict')
                            ->fileAttachmentsDisk('ea_images')
                            ->fileAttachmentsDirectory(fn ($record) => ($record?->product_slug ?? 'unknown') . '/set-review')
                            ->columnSpanFull(),
                        Textarea::make('risk_disclaimer')
                            ->label('Risk Disclaimer')
                            ->rows(4)
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
                    ->width(60)->height(45),
                TextColumn::make('h1')->label('Title')->searchable()->limit(50)->weight('bold'),
                TextColumn::make('product_slug')->label('EA')->searchable()->color('gray')->size('sm'),
                TextColumn::make('slug')->label('URL slug')->color('primary')->size('sm')
                    ->formatStateUsing(fn ($state) => '/blog/' . $state),
                IconColumn::make('is_published')->label('Live')->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetReviews::route('/'),
            'edit'  => Pages\EditSetReview::route('/{record}/edit'),
        ];
    }
}
