@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <div class="form-group">
                    <label for="name">Nome:</label>
                    <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Ex.: Crítica, Alta, Média, Baixa" value="{{ $priority->name ?? old('name') }}">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="weight">Peso:</label>
                    <input type="number" name="weight" id="weight" min="0" class="form-control {{ $errors->has('weight') ? 'is-invalid' : '' }}"
                        placeholder="Maior peso = mais urgente" value="{{ $priority->weight ?? old('weight', 0) }}">
                    @error('weight') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="color">Cor:</label>
                    <input type="color" name="color" id="color" class="form-control {{ $errors->has('color') ? 'is-invalid' : '' }}"
                        value="{{ $priority->color ?? old('color', '#dc3545') }}">
                    @error('color') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="form-group">
                    <label for="default_sla_hours">SLA padrão (horas):</label>
                    <input type="number" name="default_sla_hours" id="default_sla_hours" min="1" class="form-control {{ $errors->has('default_sla_hours') ? 'is-invalid' : '' }}"
                        placeholder="Opcional" value="{{ $priority->default_sla_hours ?? old('default_sla_hours') }}">
                    @error('default_sla_hours') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
            <div class="form-group">
                <button type="submit" class="btn btn-block btn-success">Salvar</button>
            </div>
        </div>
    </div>
</div>
<!--row-->
