@include('admin.includes.alerts')

@php
    $existingQuestions = old('questions');
    if ($existingQuestions === null && isset($survey)) {
        $existingQuestions = $survey->questions->map(function ($q) {
            return [
                'type' => $q->type,
                'prompt' => $q->prompt,
                'required' => $q->required ? '1' : '0',
                'options' => is_array($q->options) ? implode("\n", $q->options) : '',
            ];
        })->values()->all();
    }
    if (empty($existingQuestions)) {
        $existingQuestions = [[
            'type' => 'text',
            'prompt' => '',
            'required' => '1',
            'options' => '',
        ]];
    }
@endphp

<div class="card occurrence-form-section">
    <div class="card-header">
        <h3 class="card-title mb-0">Dados da pesquisa</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="title">Título <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title"
                class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                value="{{ old('title', $survey->title ?? '') }}" required>
            @error('title') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="3"
                class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                placeholder="Texto introdutório exibido no link público">{{ old('description', $survey->description ?? '') }}</textarea>
            @error('description') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                {{ old('is_active', isset($survey) ? ($survey->is_active ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Pesquisa ativa (aceita respostas pelo link)</label>
        </div>
    </div>
</div>

<div class="card occurrence-form-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Perguntas</h3>
        <button type="button" class="btn btn-sm btn-outline-primary" id="survey-add-question">
            <i class="fas fa-plus"></i> Adicionar pergunta
        </button>
    </div>
    <div class="card-body">
        @error('questions') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
        <div id="survey-questions" data-initial='@json($existingQuestions)'></div>
        <template id="survey-question-template">
            <div class="survey-question-card border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong class="survey-question-label">Pergunta</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger survey-remove-question" title="Remover">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Enunciado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control survey-prompt" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select class="form-control survey-type" required>
                            <option value="text">Texto livre</option>
                            <option value="single_choice">Múltipla escolha (única)</option>
                            <option value="scale">Escala 1–5</option>
                        </select>
                    </div>
                </div>
                <div class="form-group survey-options-group" style="display:none;">
                    <label>Opções (uma por linha)</label>
                    <textarea class="form-control survey-options" rows="3" placeholder="Opção A&#10;Opção B&#10;Opção C"></textarea>
                    <small class="form-text text-muted">Obrigatório para múltipla escolha.</small>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input survey-required" checked>
                    <label class="custom-control-label">Obrigatória</label>
                </div>
            </div>
        </template>
    </div>
</div>

<div class="occurrence-form-actions">
    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar</button>
    <a href="{{ route('surveys.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>

<script>
(function () {
    var container = document.getElementById('survey-questions');
    var template = document.getElementById('survey-question-template');
    var addBtn = document.getElementById('survey-add-question');
    if (!container || !template || !addBtn) return;

    function toggleOptions(card) {
        var type = card.querySelector('.survey-type').value;
        var group = card.querySelector('.survey-options-group');
        group.style.display = type === 'single_choice' ? 'block' : 'none';
    }

    function reindex() {
        var cards = container.querySelectorAll('.survey-question-card');
        cards.forEach(function (card, index) {
            card.querySelector('.survey-question-label').textContent = 'Pergunta ' + (index + 1);
            card.querySelector('.survey-prompt').name = 'questions[' + index + '][prompt]';
            card.querySelector('.survey-type').name = 'questions[' + index + '][type]';
            card.querySelector('.survey-options').name = 'questions[' + index + '][options]';
            var req = card.querySelector('.survey-required');
            req.name = 'questions[' + index + '][required]';
            req.value = '1';
            // hidden 0 when unchecked
            var hidden = card.querySelector('.survey-required-hidden');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.className = 'survey-required-hidden';
                hidden.value = '0';
                req.parentNode.insertBefore(hidden, req);
            }
            hidden.name = 'questions[' + index + '][required]';
        });
    }

    function addQuestion(data) {
        data = data || { type: 'text', prompt: '', required: '1', options: '' };
        var node = template.content.cloneNode(true);
        var card = node.querySelector('.survey-question-card');
        card.querySelector('.survey-prompt').value = data.prompt || '';
        card.querySelector('.survey-type').value = data.type || 'text';
        card.querySelector('.survey-options').value = data.options || '';
        card.querySelector('.survey-required').checked = data.required === true || data.required === '1' || data.required === 1;
        card.querySelector('.survey-type').addEventListener('change', function () { toggleOptions(card); });
        card.querySelector('.survey-remove-question').addEventListener('click', function () {
            if (container.querySelectorAll('.survey-question-card').length <= 1) return;
            card.remove();
            reindex();
        });
        container.appendChild(card);
        toggleOptions(card);
        reindex();
    }

    addBtn.addEventListener('click', function () { addQuestion(); });

    var initial = [];
    try { initial = JSON.parse(container.getAttribute('data-initial') || '[]'); } catch (e) { initial = []; }
    if (!initial.length) initial = [{ type: 'text', prompt: '', required: '1', options: '' }];
    initial.forEach(addQuestion);
})();
</script>
