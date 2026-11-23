<?php

namespace App\Filament\Admin\Resources\Announcements;

use App\Enums\TablerIcon;
use App\Filament\Admin\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::Speakerphone;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return trans('admin/announcement.nav_title');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/announcement.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/announcement.nav_title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/dashboard.advanced');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')
                ->label(trans('admin/announcement.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            MarkdownEditor::make('body')
                ->label(trans('admin/announcement.body'))
                ->columnSpanFull(),
            ToggleButtons::make('type')
                ->label(trans('admin/announcement.type'))
                ->inline()
                ->required()
                ->default('info')
                ->options(collect(Announcement::TYPES)->mapWithKeys(fn ($type) => [$type => trans("admin/announcement.types.$type")]))
                ->colors(['info' => 'info', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'])
                ->columnSpanFull(),
            DateTimePicker::make('starts_at')
                ->label(trans('admin/announcement.starts_at'))
                ->helperText(trans('admin/announcement.starts_at_help')),
            DateTimePicker::make('ends_at')
                ->label(trans('admin/announcement.ends_at'))
                ->helperText(trans('admin/announcement.ends_at_help'))
                ->after('starts_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(trans('admin/announcement.title'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(trans('admin/announcement.type'))
                    ->badge()
                    ->color(fn (string $state) => $state)
                    ->formatStateUsing(fn (string $state) => trans("admin/announcement.types.$state")),
                TextColumn::make('starts_at')
                    ->label(trans('admin/announcement.starts_at'))
                    ->dateTime()
                    ->placeholder(trans('admin/announcement.now')),
                TextColumn::make('ends_at')
                    ->label(trans('admin/announcement.ends_at'))
                    ->dateTime()
                    ->placeholder(trans('admin/announcement.never')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label(trans('admin/announcement.create'))
                    ->hiddenLabel(false)
                    ->button()
                    ->iconSize(null),
            ])
            ->emptyStateIcon(TablerIcon::Speakerphone)
            ->emptyStateHeading(trans('admin/announcement.empty'));
    }

    public static function getPages(): array
    {
        return ['index' => ManageAnnouncements::route('/')];
    }
}
