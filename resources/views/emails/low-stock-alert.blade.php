<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#92400E;">
        ⚠️ Alerta de estoque crítico
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Loja: <strong>{{ $storeName }}</strong>
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 6px 6px 0;padding:16px 20px;margin-bottom:28px;">
        <tr>
            <td>
                <p style="margin:0 0 6px;font-size:15px;font-weight:bold;color:#92400E;">
                    {{ $productName }}
                </p>
                @if ($variationName)
                    <p style="margin:0 0 8px;font-size:13px;color:#92400E;">
                        Variação: {{ $variationName }}
                    </p>
                @endif
                <p style="margin:0 0 4px;font-size:13px;color:#92400E;">
                    <strong>Estoque atual:</strong>
                    <span style="font-size:16px;font-weight:bold;">{{ $currentStock }}</span> unidades
                </p>
                <p style="margin:0;font-size:13px;color:#92400E;">
                    <strong>Limite mínimo configurado:</strong> {{ $threshold }} unidades
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.6;">
        O estoque do produto acima atingiu ou ficou abaixo do limite mínimo configurado.
        Recomendamos reabastecer em breve para evitar indisponibilidade.
    </p>

    <p style="margin:0;font-size:13px;color:#9ca3af;">
        Este aviso foi gerado automaticamente pela plataforma {{ config('app.name') }}.
    </p>
</x-emails.layout>
