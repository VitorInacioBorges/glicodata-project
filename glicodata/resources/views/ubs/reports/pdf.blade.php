<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório da anamnese</title>
    <style>
        @page {
            margin: 24mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.55;
            margin: 0;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        h1 {
            color: #173f5f;
            font-size: 22px;
            margin-bottom: 4px;
        }

        h2 {
            color: #173f5f;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .document-type {
            color: #537187;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .subtitle {
            color: #537187;
            margin-bottom: 24px;
        }

        .panel {
            border: 1px solid #d8e2e8;
            border-radius: 6px;
            margin-bottom: 16px;
            padding: 16px;
        }

        .field {
            border-bottom: 1px solid #edf2f5;
            padding: 8px 0;
        }

        .field:first-child {
            padding-top: 0;
        }

        .field:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .label {
            color: #537187;
            display: inline-block;
            font-weight: bold;
            width: 34%;
        }

        .value {
            display: inline-block;
            vertical-align: top;
            width: 64%;
        }

        .description {
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .footer {
            color: #6b7f8d;
            font-size: 9px;
            margin-top: 24px;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $assessment = $report->assessment;
        $patient = $assessment?->patient;
        $professional = $assessment?->professional;
        $risk = $assessment?->risk;
        $classification = match ($risk?->classification?->value) {
            'low' => 'Baixo',
            'moderate' => 'Moderado',
            'high' => 'Alto',
            default => '—',
        };
    @endphp

    <header>
        <p class="document-type">GlicoData · Documentação clínica</p>
        <h1>Relatório da Anamnese</h1>
        <p class="subtitle">Documento individual gerado a partir do registro clínico da UBS.</p>
    </header>

    <section class="panel">
        <h2>Informações do relatório</h2>
        <div class="field">
            <span class="label">Data do relatório</span>
            <span class="value">{{ $report->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
        </div>
        <div class="field">
            <span class="label">Paciente</span>
            <span class="value">{{ $patient?->first_name ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="label">Profissional responsável</span>
            <span class="value">
                {{ $professional?->first_name ?: '—' }}
                @if ($professional?->specialty)
                    · {{ $professional->specialty }}
                @endif
            </span>
        </div>
        <div class="field">
            <span class="label">Classificação do risco</span>
            <span class="value">{{ $classification }}</span>
        </div>
        <div class="field">
            <span class="label">Pontuação e probabilidade</span>
            <span class="value">
                @if ($risk)
                    {{ $risk->score }} pontos · {{ number_format($risk->percentage, 0, ',', '.') }}%
                @else
                    —
                @endif
            </span>
        </div>
    </section>

    <section class="panel">
        <h2>Escrita do relatório</h2>
        <div class="description">{{ $report->description ?: '—' }}</div>
    </section>

    <p class="footer">Relatório gerado pelo GlicoData.</p>
</body>

</html>
