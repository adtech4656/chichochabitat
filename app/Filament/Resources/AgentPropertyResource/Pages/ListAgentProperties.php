<?php

namespace App\Filament\Resources\AgentPropertyResource\Pages;

use App\Filament\Resources\AgentPropertyResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentProperties extends ListRecords
{
    protected static string $resource = AgentPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
