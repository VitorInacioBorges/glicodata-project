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
        <input
            class="form-control"
            id="first_name"
            name="first_name"
            value="{{ old('first_name', $patient->first_name ?? '') }}"
            maxlength="80"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label" for="sex">Sexo</label>
        <select class="form-select" id="sex" name="sex" required>
            <option value="">Selecione</option>
            <option value="0" @selected((string) old('sex', isset($patient) ? (int) $patient->sex : '') === '0')>Feminino</option>
            <option value="1" @selected((string) old('sex', isset($patient) ? (int) $patient->sex : '') === '1')>Masculino</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="neighborhood">Bairro</label>
        <input
            class="form-control"
            id="neighborhood"
            name="neighborhood"
            value="{{ old('neighborhood', $patient->neighborhood ?? '') }}"
            maxlength="120"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label" for="street_name">Nome do logradouro</label>
        <input
            class="form-control"
            id="street_name"
            name="street_name"
            value="{{ old('street_name', $patient->street_name ?? '') }}"
            maxlength="160"
        >
        <div class="form-text">Informe somente o nome da rua, sem número, complemento ou referência.</div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Salvar paciente</button>
    <a
        class="btn btn-outline-secondary"
        href="{{ isset($patient) ? route('ubs.patients.show', $patient) : route('ubs.patients.index') }}"
    >Cancelar</a>
</div>
