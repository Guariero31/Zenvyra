<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#92400E;">
        ⚠️ Sua assinatura vence em {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'dia' : 'dias' }}
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Loja: <strong>{{ $storeName }}</strong>
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 6px 6px 0;padding:16px 20px;margin-bottom:28px;">
        <tr>
            <td>
                <p style="margin:0 0 4px;font-size:13px;color:#92400E;">
                    <strong>Plano atual:</strong> {{ $planName }}
                </p>
                <p style="margin:0;font-size:13px;color:#92400E;">
                    <strong>Data de vencimento:</strong> {{ $expiresAt }}
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
        Para garantir a continuidade do acesso à sua loja, renove sua assinatura antes da data de vencimento.
        Após o vencimento, você terá um período de tolerância de <strong>{{ $graceDays }} dias</strong> antes
        do acesso ser suspenso.
    </p>

    <p style="margin:0 0 24px;font-size:14px;color:#374151;line-height:1.6;">
        Entre em contato com nossa equipe para renovar ou alterar seu plano.
    </p>

    <p style="margin:0;font-size:13px;color:#9ca3af;">
        Este aviso foi gerado automaticamente pela plataforma {{ config('app.name') }}.
    </p>
</x-emails.layout>
