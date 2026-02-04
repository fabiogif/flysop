@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="form-group">
                <label for="name">Nome</label>
                <input type="text" name="name" class="form-control" placeholder="Nome"
                    value="{{ $statusOccurrence->name ?? old('name') }}">
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
