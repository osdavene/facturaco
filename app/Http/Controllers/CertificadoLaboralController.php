<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Services\MailService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class CertificadoLaboralController extends Controller
{
    public function index(Request $request)
    {
        $empleados = Empleado::orderBy('apellidos')->get();
        $empleadoSeleccionado = null;

        if ($request->filled('empleado_id')) {
            $empleadoSeleccionado = Empleado::findOrFail($request->empleado_id);
        }

        return view('nomina.certificados.index', compact('empleados', 'empleadoSeleccionado'));
    }

    public function generar(Request $request)
    {
        $request->validate([
            'empleado_id'    => 'required|exists:empleados,id',
            'destinatario'   => 'nullable|string|max:150',
            'incluir_salario'=> 'nullable|boolean',
            'observaciones'  => 'nullable|string|max:500',
        ]);

        $empleado       = Empleado::findOrFail($request->empleado_id);
        $empresa        = Empresa::obtener();
        $destinatario   = $request->destinatario ?: 'A QUIEN PUEDA INTERESAR';
        $incluirSalario = $request->boolean('incluir_salario', true);
        $observaciones  = $request->observaciones;
        $fecha          = now();

        return view('nomina.certificados.imprimir', compact('empleado', 'empresa', 'destinatario', 'incluirSalario', 'observaciones', 'fecha'));
    }

    public function enviarCorreo(Request $request, MailService $mailer)
    {
        $request->validate([
            'empleado_id'    => 'required|exists:empleados,id',
            'destinatario'   => 'nullable|string|max:150',
            'incluir_salario'=> 'nullable|boolean',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);
        if (empty($empleado->email)) {
            return back()->with('error', "El empleado {$empleado->nombre_completo} no tiene correo electrónico.");
        }

        $empresa        = Empresa::obtener();
        $destinatario   = $request->destinatario ?: 'A QUIEN PUEDA INTERESAR';
        $incluirSalario = $request->boolean('incluir_salario', true);
        $observaciones  = $request->observaciones;
        $fecha          = now();

        // Generar PDF
        $pdf = app(PdfService::class);
        $pdfContent = $pdf->output('nomina.certificados.imprimir', compact('empleado', 'empresa', 'destinatario', 'incluirSalario', 'observaciones', 'fecha'));

        // Usar MailService o Http
        try {
            $host = $empresa->mail_host ?: \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
            $pass = $empresa->mail_password ?: \App\Models\ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));
            $from = $empresa->mail_from_address ?: \App\Models\ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address', 'onboarding@resend.dev'));
            $name = $empresa->mail_from_name ?: $empresa->razon_social;

            if (str_contains(strtolower((string)$host), 'resend') || strtolower((string)$host) === 'resend' || str_starts_with((string)$pass, 're_')) {
                \Illuminate\Support\Facades\Http::withToken($pass)
                    ->acceptJson()
                    ->post('https://api.resend.com/emails', [
                        'from'        => "{$name} <{$from}>",
                        'to'          => [$empleado->email],
                        'subject'     => "Certificado Laboral — {$empleado->nombre_completo}",
                        'html'        => "<p>Hola <strong>{$empleado->nombre_completo}</strong>,</p><p>Adjuntamos tu certificado laboral solicitado emitido por <strong>{$empresa->razon_social}</strong>.</p>",
                        'attachments' => [
                            [
                                'filename' => "Certificado-Laboral-{$empleado->numero_documento}.pdf",
                                'content'  => base64_encode($pdfContent),
                            ]
                        ],
                    ]);
            }

            return back()->with('success', "Certificado laboral enviado exitosamente a {$empleado->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', "Error enviando correo: " . $e->getMessage());
        }
    }
}
