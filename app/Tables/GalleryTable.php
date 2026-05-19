<?php

namespace App\Tables;

use Forjed\InertiaTable\Column;
use Forjed\InertiaTable\Columns\ActionsColumn;
use Forjed\InertiaTable\Columns\BadgeColumn;
use Forjed\InertiaTable\Columns\DateTimeColumn;
use Forjed\InertiaTable\Columns\LinkColumn;
use Forjed\InertiaTable\Columns\TextColumn;
use Forjed\InertiaTable\Table;

class GalleryTable extends Table
{
    protected string $defaultSort = '-created_at';

    protected int $perPage = 20;

    protected function query(): void
    {
        $this->query->withCount('items');
    }

    protected function columns(): array
    {
        return [
            LinkColumn::make('name', 'Name')
                ->value(fn ($row) => $row->name)
                ->sortable(),
            TextColumn::make('slug', 'Slug')->sortable(),
            BadgeColumn::make('visibility', 'Visibility')->sortable(),
            TextColumn::make('items_count', 'Items'),
            DateTimeColumn::make('created_at', 'Created')
                ->toLocal()
                ->sortable(),
            ActionsColumn::make(),
            Column::data('id'),
        ];
    }

    protected function searchable(): array
    {
        return ['name', 'slug'];
    }
}
