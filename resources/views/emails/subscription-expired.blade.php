<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#991B1B;">
        ❌ Assinatura expirada — acesso suspenso
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Loja: <strong>{{ $storeName }}</strong>
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#fee2e2;border-left:4px solid #ef4444;border-radius:0 6px 6px 0;padding:16px 20px;margin-bottom:28px;">
        <tr>
            <td>
                <p style="margin:0 0 4px;font-size:13px;color:#991B1B;">
                    <strong>Plano:</strong> {{ $planName }}
                </p>
                <p style="margin:0;font-size:13px;color:#991B1B;">
                    <strong>Expirou em:</strong> {{ $expiredAt }}
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
        O acesso ao painel da loja <strong>{{ $storeName }}</strong> foi suspenso porque a assinatura expirou
        e o período de tolerância encerrou.
    </p>

    <p style="margin:0 0 24px;font-size:14px;color:#374151;line-height:1.6;">
        Seus dados estão seguros e preservados. Entre em contato com nossa equipe para reativar sua assinatura
        e recuperar o acesso imediatamente.
    </p>

    <p style="margin:0;font-size:13px;color:#9ca3af;">
        Este aviso foi gerado automaticamente pela plataforma {{ config('app.name') }}.
    </p>
</x-emails.layout>
