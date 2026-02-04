@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="identify">Identificador</label>
            <input type="text" name="identify" class="form-control" placeholder="Identificador:"
                value="{{ $table->identify ?? old('identify') }}">
        </div>
        <!--form-group-->
        <div class="form-group">
            <label for="description">Descrição:</label>
            <textarea name="description" id="description" cols="30" rows="10"
                class="form-control">{{ $table->description ?? old('description') }}</textarea>
        </div>
        <!--form-group-->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>
<!--row-->
