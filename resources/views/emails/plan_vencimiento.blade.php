<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Vencimiento de Plan — FacCol</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f1a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 30px auto; background-color: #111827; border: 1px solid #1e2d47; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        
        {{-- Header con Logo --}}
        <tr>
            <td style="padding: 30px 40px 20px; text-align: center; border-bottom: 1px solid #1e2d47; background-color: #141c2e;">
                <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; background-color: #f59e0b; color: #000; font-weight: 900; font-size: 18px; border-radius: 12px; text-align: center;">FC</div>
                <h1 style="margin: 12px 0 4px; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                    Fac<span style="color: #f59e0b;">Col</span>
                </h1>
                <p style="margin: 0; font-size: 12px; color: #94a3b8; text-transform: uppercase; tracking: 1px;">Notificación de Servicio & Suscripción</p>
            </td>
        </tr>

        {{-- Cuerpo del Mensaje --}}
        <tr>
            <td style="padding: 35px 40px;">
                <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 700; color: #ffffff;">
                    Hola, {{ $empresa->razon_social }} 👋
                </h2>
                
                <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #cbd5e1;">
                    Te escribimos para recordarte que el período de tu suscripción en <strong>FacCol</strong> está próximo a culminar. Para que tu negocio continúe emitiendo facturación electrónica DIAN, punto de venta y control de inventario sin interrupciones, te invitamos a gestionar tu renovación.
                </p>

                {{-- Tarjeta de Estado del Plan --}}
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #1a2235; border: 1px solid #2d3f66; border-radius: 12px; margin-bottom: 25px;">
                    <tr>
                        <td style="padding: 20px;">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 10px; font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Plan Contratado</td>
                                    <td style="padding-bottom: 10px; font-size: 14px; color: #f59e0b; font-weight: 700; text-align: right;">
                                        {{ $empresa->plan ? $empresa->plan->nombre : 'Plan Estándar' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 10px; font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Fecha de Vencimiento</td>
                                    <td style="padding-bottom: 10px; font-size: 14px; color: #ffffff; font-weight: 600; text-align: right;">
                                        {{ $empresa->plan_vencimiento ? $empresa->plan_vencimiento->format('d/m/Y') : 'Pronto a vencer' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 10px; font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Estado de Vigencia</td>
                                    <td style="padding-bottom: 10px; font-size: 13px; font-weight: 700; text-align: right; color: {{ $diasRestantes <= 0 ? '#ef4444' : ($diasRestantes <= 3 ? '#f59e0b' : '#10b981') }};">
                                        {{ $diasRestantes <= 0 ? 'Vence hoy' : 'Faltan ' . $diasRestantes . ' días' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Facturas DIAN Emitidas</td>
                                    <td style="font-size: 13px; color: #38bdf8; font-weight: 600; text-align: right;">
                                        {{ $empresa->facturasEmitidasMes() }} facturas este mes
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                @if(!empty($notaAdicional))
                <div style="padding: 15px 20px; background-color: #242f49; border-left: 4px solid #f59e0b; border-radius: 6px; margin-bottom: 25px; font-size: 13px; color: #e2e8f0; line-height: 1.5;">
                    <strong>Nota importante:</strong><br>
                    {{ $notaAdicional }}
                </div>
                @endif

                {{-- Botón de Acción --}}
                <div style="text-align: center; margin: 30px 0 10px;">
                    <a href="{{ config('app.url') }}" style="display: inline-block; padding: 14px 32px; background-color: #f59e0b; color: #000000; font-weight: 700; font-size: 14px; text-decoration: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                        Ingresar a FacCol & Renovar
                    </a>
                </div>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="padding: 20px 40px; background-color: #0d1322; text-align: center; border-top: 1px solid #1e2d47; font-size: 12px; color: #64748b; line-height: 1.5;">
                Este correo fue enviado automáticamente por el sistema FacCol.<br>
                Si ya realizaste tu pago o renovación, por favor haz caso omiso a este mensaje.
            </td>
        </tr>
    </table>
</body>
</html>
