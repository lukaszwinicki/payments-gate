<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class ApiDocsRedirect extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.api-docs-redirect';
    protected static ?string $title = 'API documentation';
    protected static ?string $navigationLabel = 'API Docs';
    protected static ?string $navigationGroup = 'Tools';

    public function mount()
    {
        return redirect(config('app.api_docs_url'));
    }
}
