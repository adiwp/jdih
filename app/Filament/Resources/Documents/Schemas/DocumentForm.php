<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('abstract')
                    ->columnSpanFull(),
                TextInput::make('document_number'),
                TextInput::make('call_number'),
                TextInput::make('teu_number'),
                TextInput::make('document_type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('document_status_id')
                    ->required()
                    ->numeric(),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
                TextInput::make('language')
                    ->required()
                    ->default('id'),
                Textarea::make('content')
                    ->columnSpanFull(),
                Textarea::make('note')
                    ->columnSpanFull(),
                TextInput::make('source'),
                TextInput::make('location'),
                TextInput::make('jdihn_metadata'),
                DateTimePicker::make('jdihn_last_sync'),
                TextInput::make('jdihn_status'),
                TextInput::make('jdihn_id'),
                DatePicker::make('published_date'),
                DatePicker::make('effective_date'),
                DatePicker::make('expired_date'),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('meta_description')
                    ->columnSpanFull(),
                Textarea::make('keywords')
                    ->columnSpanFull(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('download_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
