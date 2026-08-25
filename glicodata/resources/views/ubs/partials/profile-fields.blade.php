<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Nome da unidade</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('name')]) id="name" name="name" type="text"
            value="{{ old('name', $ubs->name) }}" maxlength="255">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="district_id">Distrito</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('district_id')]) id="district_id" name="district_id">
            <option value="">Não informado</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" @selected(old('district_id', $ubs->district_id) === $district->id)>{{ $district->name }}</option>
            @endforeach
        </select>
        @error('district_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="bairro_ref">Bairro de referência</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('bairro_ref')]) id="bairro_ref" name="bairro_ref" type="text"
            value="{{ old('bairro_ref', $ubs->bairro_ref) }}" maxlength="255">
        @error('bairro_ref')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="phone">Telefone</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('phone')]) id="phone" name="phone" type="tel"
            value="{{ old('phone', $ubs->phone) }}" placeholder="(42) 3901-1700" maxlength="30">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="address">Endereço</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('address')]) id="address" name="address" type="text"
            value="{{ old('address', $ubs->address) }}" maxlength="255">
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="email">E-mail institucional</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('email')]) id="email" name="email" type="email"
            value="{{ old('email', $ubs->email) }}" maxlength="255">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
