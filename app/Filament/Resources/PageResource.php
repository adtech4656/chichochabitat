<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'page';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('content.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('pages.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('pages.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Page')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn ($rule, Forms\Get $get) => $rule->where('locale', $get('locale') ?: 'en'),
                    )
                    ->helperText('Appears at /pages/{slug}. English and French rows share the same slug.'),
                Forms\Components\Select::make('locale')
                    ->options(self::localeOptions())
                    ->default('en')
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('group')
                    ->options([
                        'company' => 'Company',
                        'trust' => 'Trust & safety',
                        'legal' => 'Legal',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('meta_description')->maxLength(320)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Content')->schema([
                Forms\Components\RichEditor::make('body')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike', 'link',
                        'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'undo', 'redo',
                    ]),
            ]),

            Forms\Components\Section::make('Publication')->schema([
                Forms\Components\Toggle::make('is_published')->label('Published')->default(true),
                Forms\Components\TextInput::make('sort')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    /** @return array<string, string> */
    public static function localeOptions(): array
    {
        return ['en' => 'English', 'fr' => 'Français'];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('group')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->prefix('/pages/')->color('gray'),
                Tables\Columns\TextColumn::make('locale')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::localeOptions()[$state] ?? strtoupper($state)),
                Tables\Columns\TextColumn::make('group')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Live'),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')->options([
                    'company' => 'Company',
                    'trust' => 'Trust & safety',
                    'legal' => 'Legal',
                ]),
                Tables\Filters\SelectFilter::make('locale')->options(self::localeOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('translate')
                    ->label(fn (Page $record): string => $record->locale === 'en' ? 'Version française' : 'English version')
                    ->icon('heroicon-o-language')
                    ->color('gray')
                    ->visible(fn (Page $record): bool => static::canEdit($record))
                    ->action(function (Page $record) {
                        $target = $record->locale === 'en' ? 'fr' : 'en';

                        $counterpart = Page::query()
                            ->where('slug', $record->slug)
                            ->where('locale', $target)
                            ->first();

                        if (! $counterpart) {
                            $counterpart = Page::create([
                                'slug' => $record->slug,
                                'locale' => $target,
                                'title' => $record->title,
                                'group' => $record->group,
                                'body' => $record->body,
                                'meta_description' => $record->meta_description,
                                'is_published' => $record->is_published,
                                'sort' => $record->sort,
                            ]);
                        }

                        return redirect(static::getUrl('edit', ['record' => $counterpart]));
                    }),
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $record) => url('/pages/'.$record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
