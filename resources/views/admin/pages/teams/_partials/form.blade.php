@include('admin.includes.alerts')
<?php $departments = $departments ?? collect(); ?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <div class="form-group">
                    <label for="name">Nome:</label>
                    <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Nome da equipe" value="{{ $team->name ?? old('name') }}">
                    @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <div class="form-group">
                    <label for="department_id">Departamento:</label>
                    <select name="department_id" id="department_id" class="form-control {{ $errors->has('department_id') ? 'is-invalid' : '' }}">
                        <option value="">Nenhum</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) ($team->department_id ?? old('department_id')) === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
