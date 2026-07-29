<x-emails.layout :subject="$subject">
    <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#065F46;">
        ✅ Pedido confirmado!
    </p>
    <p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
        Olá, <strong>{{ $customerName }}</strong>! Seu pedido foi registrado com sucesso.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f0fdf4;border-radius:6px;padding:16px 20px;margin-bottom:24px;">
        <tr>
            <td>
                <p style="margin:0 0 4px;font-size:13px;color:#065F46;">
                    <strong>Pedido:</strong> #{{ $orderId }}
                </p>
                <p style="margin:0 0 4px;font-size:13px;color:#065F46;">
                    <strong>Loja:</strong> {{ $storeName }}
                </p>
                @if ($paymentMethod)
                    <p style="margin:0;font-size:13px;color:#065F46;">
                        <strong>Pagamento:</strong> {{ $paymentMethod }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Itens do pedido --}}
    <p style="margin:0 0 10px;font-size:13px;font-weight:bold;color:#374151;text-transform:uppercase;letter-spacing:0.5px;">
        Itens do pedido
    </p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;margin-bottom:20px;">
        <tr style="background-color:#f9fafb;">
            <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#6b7280;">PRODUTO</td>
            <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#6b7280;text-align:center;">QTD</td>
            <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#6b7280;text-align:right;">VALOR</td>
        </tr>
        @foreach ($items as $item)
            <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:10px 14px;font-size:13px;color:#374151;">
                    {{ $item['name'] }}
                    @if ($item['variation'])
                        <span style="color:#9ca3af;font-size:12px;"> · {{ $item['variation'] }}</span>
                    @endif
                </td>
                <td style="padding:10px 14px;font-size:13px;color:#374151;text-align:center;">
                    {{ $item['quantity'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;color:#374151;text-align:right;">
                    R$ {{ number_format((float) $item['subtotal'], 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
        <tr style="background-color:#f9fafb;border-top:2px solid #e5e7eb;">
            <td colspan="2" style="padding:12px 14px;font-size:14px;font-weight:bold;color:#1B2A4A;">
                Total
            </td>
            <td style="padding:12px 14px;font-size:14px;font-weight:bold;color:#1B2A4A;text-align:right;">
                R$ {{ number_format((float) $totalAmount, 2, ',', '.') }}
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;color:#9ca3af;">
        Em caso de dúvidas, entre em contato com a loja <strong>{{ $storeName }}</strong>.
    </p>
</x-emails.layout>
