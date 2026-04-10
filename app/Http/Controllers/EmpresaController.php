<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresa = Empresa::obtener();
        return view('empresa.index', compact('empresa'));
    }

    public function update(Request $request)
    {
        $empresa = Empresa::obtener();

        $data = $request->validate([
            'razon_social'           => 'required|string|max:255',
            'nombre_comercial'       => 'nullable|string|max:255',
            'nit'                    => 'required|string|max:20',
            'digito_verificacion'    => 'nullable|string|max:1',
            'tipo_persona'           => 'required|in:natural,juridica',
            'regimen'                => 'required|in:simple,responsable_iva',
            'email'                  => 'nullable|email',
            'telefono'               => 'nullable|string|max:20',
            'celular'                => 'nullable|string|max:20',
            'sitio_web'              => 'nullable|string|max:255',
            'departamento'           => 'nullable|string|max:100',
            'municipio'              => 'nullable|string|max:100',
            'direccion'              => 'nullable|string|max:255',
            'prefijo_factura'        => 'required|string|max:10',
            'resolucion_numero'      => 'nullable|integer',
            'resolucion_fecha'       => 'nullable|date',
            'resolucion_vencimiento' => 'nullable|date',
            'consecutivo_desde'      => 'nullable|integer|min:1',
            'consecutivo_hasta'      => 'nullable|integer|min:1',
            'clave_tecnica'          => 'nullable|string',
            'factura_electronica'    => 'boolean',
            'pie_factura'            => 'nullable|string',
            'terminos_condiciones'   => 'nullable|string',
            'iva_defecto'            => 'numeric|min:0|max:100',
            'retefuente_defecto'     => 'numeric|min:0|max:100',
            'reteica_defecto'        => 'numeric|min:0|max:100',
            'logo'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'mail_mailer'            => 'nullable|string|max:20',
            'mail_host'              => 'nullable|string|max:255',
            'mail_port'              => 'nullable|integer',
            'mail_username'          => 'nullable|string|max:255',
            'mail_password'          => 'nullable|string|max:255',
            'mail_encryption'        => 'nullable|string|max:10',
            'mail_from_address'      => 'nullable|email',
            'mail_from_name'         => 'nullable|string|max:255',
            'wompi_public_key'       => 'nullable|string|max:255',
            'wompi_currency'         => 'nullable|string|max:10',
            'wompi_events_key'       => 'nullable|string|max:255',
        ], [
            'logo.image'             => 'El archivo del logo debe ser una imagen.',
            'logo.mimes'             => 'El logo debe estar en formato JPG, PNG o WEBP.',
            'logo.max'               => 'El logo no puede superar los 2 MB.',
            'mail_from_address.email'=> 'El correo remitente no es válido.',
        ]);

        // Logo
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('empresa', 'public');
        } else {
            unset($data['logo']);
        }

        // Conservar contraseña mail si no se ingresó nueva
        if (empty($data['mail_password'])) {
            unset($data['mail_password']);
        }

        // Conservar wompi keys si no se ingresaron nuevas
        if (empty($data['wompi_public_key'])) {
            unset($data['wompi_public_key']);
        }
        if (empty($data['wompi_events_key'])) {
            unset($data['wompi_events_key']);
        }

        $data['factura_electronica'] = $request->boolean('factura_electronica');

        $empresa->update($data);

        return redirect()->route('empresa.index')
            ->with('success', 'Configuración guardada correctamente.');
    }

    public function deleteLogo()
    {
        $empresa = Empresa::obtener();
        if ($empresa->logo) {
            Storage::disk('public')->delete($empresa->logo);
            $empresa->update(['logo' => null]);
        }
        return back()->with('success', 'Logo eliminado.');
    }

    public function probarMail(Request $request)
    {
        $request->validate([
            'email_prueba' => 'required|email',
        ], [
            'email_prueba.required' => 'Ingresa un correo para la prueba.',
            'email_prueba.email'    => 'El correo no es válido.',
        ]);

        $empresa = Empresa::obtener();

        if (!$empresa->mail_configurado) {
            return back()->with('error', 'Primero guarda la configuración de correo antes de probar.');
        }

        Config::set('mail.mailers.smtp.host',       $empresa->mail_host);
        Config::set('mail.mailers.smtp.port',       $empresa->mail_port);
        Config::set('mail.mailers.smtp.username',   $empresa->mail_username);
        Config::set('mail.mailers.smtp.password',   $empresa->mail_password);
        Config::set('mail.mailers.smtp.encryption', $empresa->mail_encryption);
        Config::set('mail.from.address',            $empresa->mail_from_address ?? $empresa->email);
        Config::set('mail.from.name',               $empresa->mail_from_name    ?? $empresa->razon_social);

        try {
            Mail::raw(
                "Correo de prueba de FacturaCO.\n\nSi recibes este mensaje, la configuración de correo de {$empresa->razon_social} está funcionando correctamente.",
                function ($message) use ($request, $empresa) {
                    $message->to($request->email_prueba)
                            ->subject('Prueba de correo — ' . $empresa->razon_social);
                }
            );

            return back()->with('success', '¡Correo de prueba enviado correctamente a ' . $request->email_prueba . '!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar: ' . $e->getMessage());
        }
    }
}