@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-8">
        <div class="form-group">
            <label for="name">Nome *</label>
            <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                placeholder="Nome completo" value="{{ $driver->name ?? old('name') }}" required>
            @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="E-mail" value="{{ $driver->email ?? old('email') }}">
            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="phone">Telefone</label>
            <input type="text" name="phone" id="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                placeholder="Telefone" value="{{ $driver->phone ?? old('phone') }}">
            @error('phone') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" name="cpf" id="cpf" class="form-control {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                placeholder="CPF" value="{{ $driver->cpf ?? old('cpf') }}">
            @error('cpf') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="status">Status *</label>
            <select name="status" id="status" class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                @foreach (\App\Models\Driver::statusLabels() as $value => $label)
                    <option value="{{ $value }}" {{ ($driver->status ?? old('status')) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</div>
