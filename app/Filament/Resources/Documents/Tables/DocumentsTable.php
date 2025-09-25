<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('document_number')
                    ->searchable(),
                TextColumn::make('call_number')
                    ->searchable(),
                TextColumn::make('teu_number')
                    ->searchable(),
                TextColumn::make('document_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('document_status_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('language')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('jdihn_last_sync')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('jdihn_status')
                    ->searchable(),
                TextColumn::make('jdihn_id')
                    ->searchable(),
                TextColumn::make('published_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('effective_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expired_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                IconColumn::make('is_featured')
                    ->boolean(),
                TextColumn::make('view_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('download_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
