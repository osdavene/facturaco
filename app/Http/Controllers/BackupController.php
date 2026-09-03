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

    public function descargarSql()
    {
        return $this->backupAction->descargarSql();
    }

    public function descargarCsv(Request $request)
    {
        $request->validate([
            'tablas'       => 'required|array|min:1',
            'tablas.*'     => 'string',
            'fecha_desde'  => 'nullable|date',
            'fecha_hasta'  => 'nullable|date',
        ]);

        return $this->backupAction->descargarCsv($request);
    }
}
