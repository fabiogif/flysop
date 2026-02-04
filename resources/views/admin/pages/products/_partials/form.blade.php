@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="title">Titulo</label>
            <input type="text" name="title" class="form-control" placeholder="Titulo:"
                value="{{ $product->title ?? old('title') }}">
        </div>
        <div class="form-group">
            <label for="title">Flag</label>
            <input type="text" name="flag" class="form-control" placeholder="Flag:"
                value="{{ $product->flag ?? old('flag') }}">
        </div>
        <div class="form-group">
            <label for="price">Preço</label>
            <input type="text" name="price" class="form-control" placeholder="Preço:"
                value="{{ $product->price ?? old('price') }}">
        </div>
        <div class="form-group">
            <label for="price">Imagem</label>
            <input type="file" name="image" class="form-control">
        </div>
        <!--form-group-->
        <div class="form-group">
            <label for="description">Descrição:</label>
            <textarea name="description" id="description" cols="30" rows="10"
                class="form-control">{{ $product->description ?? old('description') }}</textarea>
        </div>
        <!--form-group-->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>
<!--row-->
