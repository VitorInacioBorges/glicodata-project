@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Revise os dados informados.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="first_name">Primeiro nome</label>
        <input class="form-control" id="first_name" name="first_name"
            value="{{ old('first_name', $professional->first_name ?? '') }}" maxlength="80" required>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="specialty">Especialidade</label>
        <input
            class="form-control"
            id="specialty"
            name="specialty"
            value="{{ old('specialty', $professional->specialty ?? '') }}"
            maxlength="120"
            required
        >
    </div>

    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input
                class="form-check-input"
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                @checked((bool) old('is_active', $professional->is_active ?? true))
            >
            <label class="form-check-label" for="is_active">Profissional ativo</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Salvar profissional</button>
    <a
        class="btn btn-outline-secondary"
        href="{{ isset($professional) ? route('ubs.professionals.show', $professional) : route('ubs.professionals.index') }}"
    >Cancelar</a>
</div>
