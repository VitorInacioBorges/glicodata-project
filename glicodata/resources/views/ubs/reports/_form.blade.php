@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@unless (isset($report))
    <div class="mb-3">
        <label class="form-label" for="assessment_id">Anamnese concluída</label>
        <select class="form-select" id="assessment_id" name="assessment_id" required>
            <option value="">Selecione</option>
            @foreach ($assessments as $assessment)
                <option value="{{ $assessment->id }}" @selected(old('assessment_id', request('assessment_id')) === $assessment->id)>
                    {{ $assessment->patient->name }} ·
                    {{ $assessment->completed_at?->format('d/m/Y') }} ·
                    {{ strtoupper($assessment->risk?->classification?->value ?? '') }}
                </option>
            @endforeach
        </select>
    </div>
@endunless

<div class="mb-3">
    <label class="form-label" for="title">Título</label>
    <input
        class="form-control"
        id="title"
        name="title"
        value="{{ old('title', $report->title ?? '') }}"
        maxlength="255"
        required
    >
</div>

<div class="mb-3">
    <label class="form-label" for="description">Descrição</label>
    <textarea class="form-control" id="description" name="description" rows="7" required>{{ old('description', $report->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label" for="comment">Comentário interno</label>
    <textarea class="form-control" id="comment" name="comment" rows="4">{{ old('comment', $report->comment ?? '') }}</textarea>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Salvar relatório</button>
    <a
        class="btn btn-outline-secondary"
        href="{{ isset($report) ? route('ubs.reports.show', $report) : route('ubs.reports.index') }}"
    >Cancelar</a>
</div>
