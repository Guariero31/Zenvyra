<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#1B2A4A;">
        Nova mensagem de contato
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Site público de <strong>{{ $storeName }}</strong>
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f0f4ff;border-radius:6px;padding:20px 24px;margin-bottom:24px;">
        <tr>
            <td>
                <p style="margin:0 0 4px;font-size:11px;font-weight:bold;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">
                    Remetente
                </p>
                <p style="margin:0 0 2px;font-size:16px;font-weight:bold;color:#1B2A4A;">
                    {{ $senderName }}
                </p>
                <p style="margin:0;font-size:13px;color:#4B5563;">
                    <a href="mailto:{{ $senderEmail }}" style="color:#4F46E5;text-decoration:none;">{{ $senderEmail }}</a>
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f9fafb;border-left:4px solid #4F46E5;border-radius:0 6px 6px 0;padding:16px 20px;margin-bottom:28px;">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:11px;font-weight:bold;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">
                    Mensagem
                </p>
                <p style="margin:0;font-size:14px;color:#374151;line-height:1.7;white-space:pre-line;">{{ $message }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
        Responda diretamente para <strong>{{ $senderName }}</strong> clicando em Responder no seu cliente de e-mail.
    </p>

    <p style="margin:0;font-size:12px;color:#9ca3af;">
        Mensagem recebida pelo formulário de contato do site público da loja {{ $storeName }}.
    </p>
</x-emails.layout>
