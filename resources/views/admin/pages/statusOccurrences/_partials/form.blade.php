@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" placeholder="Nome"
                    value="{{ $statusOccurrence->name ?? old('name') }}">
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="form-group">
                <label for="sort_order">Ordem</label>
                <input type="number" name="sort_order" id="sort_order" min="0" class="form-control"
                    value="{{ $statusOccurrence->sort_order ?? old('sort_order', 0) }}">
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="form-group form-check">
                <input type="hidden" name="is_terminal" value="0">
                <input type="checkbox" name="is_terminal" id="is_terminal" class="form-check-input" value="1"
                    {{ old('is_terminal', $statusOccurrence->is_terminal ?? false) ? 'checked' : '' }}>
                <label for="is_terminal" class="form-check-label">Status terminal (só sai daqui via reabertura)</label>
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
