<?php

namespace App\Http\Controllers;

use App\Actions\GenerarBackupAction;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(
        private GenerarBackupAction $backupAction
    ) {}

    public function index()
    {
        $data = $this->backupAction->indexData();

        return view('backup.index', $data);
    }

    public function descargarJson()
    {
        return $this->backupAction->descargarJson();
    }

    public function descargarCsv(Request $request)
    {
        return $this->backupAction->descargarCsv($request);
    }
}
