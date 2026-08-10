@include('admin.includes.alerts')
<?php $parentOptions = $parentOptions ?? collect(); ?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" placeholder="Nome"
                    value="{{ $typeOccurrence->name ?? old('name') }}">
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="form-group">
                <label for="sla_hours">SLA (horas)</label>
                <input type="number" name="sla_hours" id="sla_hours" min="1" class="form-control"
                    placeholder="Opcional" value="{{ $typeOccurrence->sla_hours ?? old('sla_hours') }}">
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="form-group">
                <label for="parent_id">Tipo pai (opcional, para subtipos)</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">Nenhum (tipo raiz)</option>
                    @foreach ($parentOptions as $parentOption)
                        <option value="{{ $parentOption->id }}"
                            {{ (string) ($typeOccurrence->parent_id ?? old('parent_id')) === (string) $parentOption->id ? 'selected' : '' }}>
                            {{ $parentOption->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
            <div class="form-group">
                <button type="submit" class="btn btn-block btn-success">Salvar</button>
            </div>
        </div>
    </div>

</div>
<!--row-->
