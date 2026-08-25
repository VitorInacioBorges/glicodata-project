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
        <input class="form-control" id="name" name="name" value="{{ old('name', $professional->name ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="role">Papel</label>
        <select class="form-select" id="role" name="role" required>
            <option value="professional" @selected(old('role', $professional->role->value ?? 'professional') === 'professional')>
                Profissional de saúde
            </option>
            <option value="admin" @selected(old('role', $professional->role->value ?? '') === 'admin')>Gestor da UBS</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="cpf">CPF</label>
        <input
            class="form-control"
            id="cpf"
            name="cpf"
            value="{{ old('cpf', $professional->cpf ?? '') }}"
            maxlength="14"
            placeholder="000.000.000-00"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="birth">Nascimento</label>
        <input
            class="form-control"
            id="birth"
            name="birth"
            type="date"
            value="{{ old('birth', isset($professional) ? $professional->birth->format('Y-m-d') : '') }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="sex">Sexo</label>
        <select class="form-select" id="sex" name="sex" required>
            <option value="">Selecione</option>
            <option value="0" @selected((string) old('sex', isset($professional) ? (int) $professional->sex : '') === '0')>Feminino</option>
            <option value="1" @selected((string) old('sex', isset($professional) ? (int) $professional->sex : '') === '1')>Masculino</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">E-mail de acesso</label>
        <input
            class="form-control"
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $professional->email ?? '') }}"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label" for="phone">Telefone</label>
        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $professional->phone ?? '') }}">
    </div>

    <div class="col-12">
        <label class="form-label" for="address">Endereço</label>
        <input class="form-control" id="address" name="address" value="{{ old('address', $professional->address ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label" for="council_type">Conselho</label>
        <select class="form-select" id="council_type" name="council_type">
            <option value="">Não aplicável</option>
            <option value="CRM" @selected(old('council_type', $professional->council_type?->value ?? '') === 'CRM')>CRM</option>
            <option value="COREN" @selected(old('council_type', $professional->council_type?->value ?? '') === 'COREN')>COREN</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label" for="council_number">Número</label>
        <input
            class="form-control"
            id="council_number"
            name="council_number"
            value="{{ old('council_number', $professional->council_number ?? '') }}"
        >
    </div>

    <div class="col-md-2">
        <label class="form-label" for="council_uf">UF</label>
        <input
            class="form-control text-uppercase"
            id="council_uf"
            name="council_uf"
            value="{{ old('council_uf', $professional->council_uf ?? '') }}"
            maxlength="2"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label" for="specialty">Especialidade</label>
        <input
            class="form-control"
            id="specialty"
            name="specialty"
            value="{{ old('specialty', $professional->specialty ?? '') }}"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">
            {{ isset($professional) ? 'Nova senha (opcional)' : 'Senha inicial' }}
        </label>
        <input
            class="form-control"
            id="password"
            name="password"
            type="password"
            {{ isset($professional) ? '' : 'required' }}
            autocomplete="new-password"
        >
        <div class="form-text">Mínimo de 12 caracteres, com maiúscula, minúscula, número e símbolo.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirmar senha</label>
        <input
            class="form-control"
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            {{ isset($professional) ? '' : 'required' }}
            autocomplete="new-password"
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
            <label class="form-check-label" for="is_active">Conta ativa</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">Salvar conta</button>
    <a
        class="btn btn-outline-secondary"
        href="{{ isset($professional) ? route('ubs.professionals.show', $professional) : route('ubs.professionals.index') }}"
    >Cancelar</a>
</div>
