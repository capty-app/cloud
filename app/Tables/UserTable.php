<?php

namespace App\Tables;

use Forjed\InertiaTable\Column;
use Forjed\InertiaTable\Columns\ActionsColumn;
use Forjed\InertiaTable\Columns\BadgeColumn;
use Forjed\InertiaTable\Columns\DateTimeColumn;
use Forjed\InertiaTable\Columns\TextColumn;
use Forjed\InertiaTable\Table;

class UserTable extends Table
{
    protected string $defaultSort = 'name';

    protected int $perPage = 25;

    protected function columns(): array
    {
        return [
            TextColumn::make('name', 'Name')->sortable(),
            TextColumn::make('email', 'Email')->sortable(),
            BadgeColumn::make('role', 'Role')->sortable(),
            DateTimeColumn::make('created_at', 'Created')
                ->toLocal()
                ->sortable(),
            ActionsColumn::make(),
            Column::data('id'),
        ];
    }

    protected function searchable(): array
    {
        return ['name', 'email'];
    }
}
