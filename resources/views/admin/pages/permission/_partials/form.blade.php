@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" name="name" class="form-control" placeholder="Nome"
                value="{{ $permission->name ?? old('name') }}">
        </div>
        <!--form-group-->
        <div class="form-group">
            <label for="description">Descrição</label>
            <input type="text" name="description" class="form-control" placeholder="Descrição:"
                value="{{ $permission->description ?? old('description') }}">
        </div>
        <!--form-group-->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>
<!--row-->
