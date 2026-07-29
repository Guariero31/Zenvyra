<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:22px;font-weight:bold;color:#1B2A4A;">
        Bem-vindo, {{ $userName }}! 🎉
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Sua loja foi criada com sucesso na plataforma.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f0f4ff;border-radius:6px;padding:20px 24px;margin-bottom:28px;">
        <tr>
            <td>
                <p style="margin:0 0 6px;font-size:12px;font-weight:bold;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">
                    LOJA
                </p>
                <p style="margin:0;font-size:18px;font-weight:bold;color:#1B2A4A;">
                    {{ $storeName }}
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
        Acesse o painel da sua loja para começar a cadastrar seus produtos, gerenciar pedidos e muito mais.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ $panelUrl }}"
                   style="display:inline-block;background-color:#2563EB;color:#ffffff;font-weight:bold;font-size:15px;padding:14px 32px;border-radius:6px;text-decoration:none;">
                    Acessar minha loja →
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;">
        Se você não criou esta conta, ignore este e-mail ou entre em contato com o suporte.
    </p>
</x-emails.layout>
