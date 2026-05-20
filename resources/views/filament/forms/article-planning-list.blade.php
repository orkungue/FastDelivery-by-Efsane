<div
    x-data="{
        items: $wire.$entangle('{{ $getStatePath() }}'),
    }"
>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb;">
        <thead>
            <tr style="background: #f9fafb;">
                <th style="text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb;">
                    Artikel
                </th>
                <th style="text-align: left; width: 220px; padding: 10px; border-bottom: 1px solid #e5e7eb;">
                    Geplante Menge
                </th>
            </tr>
        </thead>

        <tbody>
            <template x-for="(item, index) in items" :key="item.article_id">
                <tr>
                    <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">
                        <span x-text="item.article_label"></span>
                    </td>

                    <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">
                        <input
                            type="number"
                            min="0"
                            step="1"
                            x-model="items[index].quantity"
                            style="
                                width: 120px;
                                padding: 8px 10px;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 14px;
                            "
                        >
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>