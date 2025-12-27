<?php

// =====================================================
// File: app/Filament/Resources/TeacherResource/Pages/CreateTeacher.php
// =====================================================

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        
        return $data;
    }
}
