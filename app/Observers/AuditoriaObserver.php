<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditoriaObserver
{
    public function creating(Model $model): void
    {
        if (Auth::check() && in_array('created_by', $model->getFillable())) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        }
    }

    public function updating(Model $model): void
    {
        if (Auth::check() && in_array('updated_by', $model->getFillable())) {
            $model->updated_by = Auth::id();
        }
    }
}