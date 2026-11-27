@include('admin.includes.alerts')

<div class="card occurrence-form-section">
    <div class="card-header">
        <h3 class="card-title mb-0">Dados do informativo</h3>
    </div>
    <div class="card-body">
        {{-- Gestor publica sempre para o próprio órgão (lista vazia); admin escolhe --}}
        @if ($issuings->isNotEmpty())
            <div class="form-group">
                <label for="issuing_id">Órgão <span class="text-danger">*</span></label>
                <select name="issuing_id" id="issuing_id"
                    class="form-control {{ $errors->has('issuing_id') ? 'is-invalid' : '' }}" required>
                    <option value="">Selecione…</option>
                    @foreach ($issuings as $issuing)
                        <option value="{{ $issuing->id }}"
                            {{ (string) old('issuing_id', $announcement->issuing_id ?? '') === (string) $issuing->id ? 'selected' : '' }}>
                            {{ $issuing->name }}
                        </option>
                    @endforeach
                </select>
                @error('issuing_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>
        @endif
        <div class="form-group">
            <label for="title">Título <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" maxlength="120"
                class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                value="{{ old('title', $announcement->title ?? '') }}" required>
            @error('title') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="body">Texto <span class="text-danger">*</span></label>
            <textarea name="body" id="body" rows="8" maxlength="5000"
                class="form-control {{ $errors->has('body') ? 'is-invalid' : '' }}"
                placeholder="Exibido na página inicial do site e do app" required>{{ old('body', $announcement->body ?? '') }}</textarea>
            @error('body') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1"
                {{ old('is_published', isset($announcement) ? ($announcement->is_published ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_published">Publicado (aparece no site e no app)</label>
        </div>
    </div>
</div>

<div class="occurrence-form-actions">
    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar</button>
    <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
