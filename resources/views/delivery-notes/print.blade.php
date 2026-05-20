<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lieferschein {{ $deliveryNote->delivery_number }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111;
            margin: 40px;
            font-size: 14px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        h1 {
            margin: 0;
            font-size: 28px;
        }

        .muted {
            color: #666;
        }

        .section {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            border-bottom: 2px solid #111;
            padding: 8px;
        }

        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .print-button {
            margin-bottom: 30px;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                margin: 20px;
            }
        }
    </style>
</head>
<body>

<button class="print-button" onclick="window.print()">Drucken</button>

<div class="header">
    <div>
        <h1>Lieferschein</h1>
        <div class="muted">Nr. {{ $deliveryNote->delivery_number }}</div>
    </div>

    <div>
        <strong>Datum:</strong>
        {{ $deliveryNote->delivery_date?->format('d.m.Y') ?? '-' }}
    </div>
</div>

<div class="section">
    <strong>Kunde</strong><br>
    {{ $deliveryNote->customer->name }}<br>

    @if($deliveryNote->customer->street)
        {{ $deliveryNote->customer->street }}<br>
    @endif

    @if($deliveryNote->customer->postal_code || $deliveryNote->customer->city)
        {{ $deliveryNote->customer->postal_code }} {{ $deliveryNote->customer->city }}<br>
    @endif

    @if($deliveryNote->customer->contact_person)
        Ansprechpartner: {{ $deliveryNote->customer->contact_person }}<br>
    @endif
</div>

<div class="section">
    <table>
        <thead>
            <tr>
                <th>Artikel</th>
                <th>Beschreibung</th>
                <th class="right">Menge</th>
                <th>Einheit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveryNote->items as $item)
                <tr>
                    <td>{{ $item->article->name }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td class="right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td>{{ $item->unit }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($deliveryNote->notes)
    <div class="section">
        <strong>Notizen</strong><br>
        {{ $deliveryNote->notes }}
    </div>
@endif

<br><br>

<table>
    <tr>
        <td style="border: none; width: 50%;">
            ___________________________<br>
            Unterschrift Fahrer
        </td>
        <td style="border: none; width: 50%;">
            ___________________________<br>
            Unterschrift Kunde
        </td>
    </tr>
</table>

</body>
</html>