@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <div class="form-group">
                    <label for="name">Nome:</label>
                    <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Ex.: Defesa Civil, Manutenção Urbana" value="{{ $department->name ?? old('name') }}">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
