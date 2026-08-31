<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:600px;width:100%;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    {{-- Cabeçalho --}}
                    <tr>
                        <td style="background-color:#1B2A4A;padding:28px 32px;text-align:center;">
                            <p style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;letter-spacing:1px;">
                                {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>

                    {{-- Conteúdo --}}
                    <tr>
                        <td style="padding:36px 32px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Rodapé --}}
                    <tr>
                        <td style="background-color:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">
                                © {{ now()->year }} {{ config('app.name') }} · Todos os direitos reservados.
                            </p>
                            <p style="margin:6px 0 0;font-size:12px;color:#9ca3af;">
                                Você está recebendo este e-mail porque possui uma conta na plataforma.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
