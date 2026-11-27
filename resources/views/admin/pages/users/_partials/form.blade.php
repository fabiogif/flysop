@include('admin.includes.alerts')
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" name="name" class="form-control" placeholder="Nome:"
                value="{{ $user->name ?? old('name') }}">
        </div>
        <!--form-group-->
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="text" name="email" class="form-control" placeholder="E-mail:"
                value="{{ $user->email ?? old('email') }}">
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" name="password" class="form-control" placeholder="Senha:" value="">
        </div>
        <div class="form-group">
            <label for="issuing_id">Órgão (gestor)</label>
            <select name="issuing_id" id="issuing_id" class="form-control">
                <option value="">Nenhum — não é gestor de órgão</option>
                @foreach ($issuings ?? [] as $issuing)
                    <option value="{{ $issuing->id }}"
                        {{ (string) old('issuing_id', $user->issuing_id ?? '') === (string) $issuing->id ? 'selected' : '' }}>
                        {{ $issuing->name }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">Gestores acessam pelo app as ocorrências do órgão e recebem aviso das novas.</small>
        </div>
        <!--form-group-->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Salvar</button>
        </div>
    </div>
</div>
<!--row-->
