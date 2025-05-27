<?php

namespace App\Filament\User\Resources\TransactionResource\Pages;

use App\Filament\User\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTableRecords(): \Illuminate\Contracts\Pagination\CursorPaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
    {
        $records = parent::getTableRecords();

        $perPage = $this->getTableRecordsPerPage();
        $page = $this->getTablePage();

        $collection = method_exists($records, 'getCollection') ? $records->getCollection() : $records;

        $collection = $collection->values()->map(function ($record, $index) use ($perPage, $page) {
            $record->row_number = ($page - 1) * $perPage + $index + 1;
            return $record;
        });

        if (method_exists($records, 'setCollection')) {
            $records->setCollection($collection);
            return $records;
        }

        return $collection;
    }
}
