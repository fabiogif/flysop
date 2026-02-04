@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" name="name" class="form-control" placeholder="Nome:"
                value="{{ $category->name ?? old('name') }}">
        </div>
        <!--form-group-->
        <div class="form-group">
            <label for="description">Descrição:</label>
            <textarea name="description" id="description" cols="30" rows="10"
                class="form-control">{{ $category->description ?? old('description') }}</textarea>
        </div>
        <!--form-group-->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>
<!--row-->
