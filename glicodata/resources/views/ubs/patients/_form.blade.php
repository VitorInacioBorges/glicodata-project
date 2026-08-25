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
    <div class="col-md-8">
        <label class="form-label" for="name">Nome completo</label>
        <input
            class="form-control"
            id="name"
            name="name"
            value="{{ old('name', $patient->name ?? '') }}"
            maxlength="255"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="cpf">CPF</label>
        <input
            class="form-control"
            id="cpf"
            name="cpf"
            value="{{ old('cpf', $patient->cpf ?? '') }}"
            placeholder="000.000.000-00"
            maxlength="14"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="birth">Data de nascimento</label>
        <input
            class="form-control"
            id="birth"
            name="birth"
            type="date"
            value="{{ old('birth', isset($patient) ? $patient->birth->format('Y-m-d') : '') }}"
            max="{{ now()->format('Y-m-d') }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="sex">Sexo</label>
        <select class="form-select" id="sex" name="sex" required>
            <option value="">Selecione</option>
            <option value="0" @selected((string) old('sex', isset($patient) ? (int) $patient->sex : '') === '0')>Feminino</option>
            <option value="1" @selected((string) old('sex', isset($patient) ? (int) $patient->sex : '') === '1')>Masculino</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="phone">Telefone</label>
        <input
            class="form-control"
            id="phone"
            name="phone"
            value="{{ old('phone', $patient->phone ?? '') }}"
            maxlength="30"
        >
    </div>

    <div class="col-12">
        <label class="form-label" for="address">Endereço</label>
        <input
            class="form-control"
            id="address"
            name="address"
            value="{{ old('address', $patient->address ?? '') }}"
            maxlength="255"
        >
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Salvar paciente</button>
    <a
        class="btn btn-outline-secondary"
        href="{{ isset($patient) ? route('ubs.patients.show', $patient) : route('ubs.patients.index') }}"
    >Cancelar</a>
</div>
