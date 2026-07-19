(function () {
	var btn         = document.getElementById('aiground-submit');
	var select      = document.getElementById('aiground-provider');
	var modelSelect = document.getElementById('aiground-model');
	if (!btn) return;

	var providerModels  = aigroundData.providerModels;
	var modelStorageKey = 'aiground_model';

	var testCases            = aigroundData.testCases;
	var testCasePills        = document.getElementById('aiground-testcase');
	var systemPromptTextarea = document.getElementById('aiground-system-prompt');
	var promptTextarea       = document.getElementById('aiground-prompt');
	var expectedEl           = document.getElementById('aiground-expected');
	if (testCasePills) {
		testCasePills.addEventListener('click', function (e) {
			var pill = e.target.closest('.aiground-pill');
			if (!pill) return;
			var testCase = testCases[pill.dataset.testcase];
			if (!testCase) return;
			testCasePills.querySelectorAll('.aiground-pill').forEach(function (p) {
				p.classList.toggle('is-active', p === pill);
			});
			systemPromptTextarea.value = testCase.system;
			promptTextarea.value       = testCase.user;
			expectedEl.textContent     = testCase.expected;
		});
	}

	function populateModels(providerId) {
		var info   = providerModels[providerId] || {};
		var models = info.models || [];
		modelSelect.innerHTML = '';
		var placeholder = document.createElement('option');
		placeholder.value = '';
		placeholder.text  = '- Select -';
		modelSelect.appendChild(placeholder);
		models.forEach(function (m) {
			var opt   = document.createElement('option');
			opt.value = m.id;
			opt.text  = m.name !== m.id ? m.name + ' (' + m.id + ')' : m.id;
			modelSelect.appendChild(opt);
		});
	}

	var storageKey = 'aiground_provider';
	var saved = localStorage.getItem(storageKey);
	if (saved && select.querySelector('option[value="' + saved + '"]')) {
		select.value = saved;
	}
	populateModels(select.value);

	var savedModel = localStorage.getItem(modelStorageKey);
	if (savedModel && modelSelect.querySelector('option[value="' + savedModel + '"]')) {
		modelSelect.value = savedModel;
	}

	select.addEventListener('change', function () {
		localStorage.setItem(storageKey, select.value);
		localStorage.removeItem(modelStorageKey);
		populateModels(select.value);
	});

	modelSelect.addEventListener('change', function () {
		localStorage.setItem(modelStorageKey, modelSelect.value);
	});

	function buildMetaLines(meta) {
		if (!meta) return [];
		var lines = [];

		if (meta.provider) {
			var p    = meta.provider;
			var name = p.name || p.id || '';
			var type = p.type || '';
			lines.push('Provider: ' + (name && type ? name + ' · ' + type : name || p.id || ''));
		}

		if (meta.model) {
			var m    = meta.model;
			var mId  = m.id || '';
			var mName = m.name || '';
			if (mName && mId && mName !== mId) {
				lines.push('Model: ' + mName + ' (' + mId + ')');
			} else {
				lines.push('Model: ' + (mName || mId));
			}
		}

		if (meta.token_usage) {
			var t = meta.token_usage, parts = [];
			if (t.promptTokens     != null) parts.push('Prompt: '     + t.promptTokens);
			if (t.completionTokens != null) parts.push('Completion: ' + t.completionTokens);
			if (t.totalTokens      != null) parts.push('Total: '      + t.totalTokens);
			if (t.thoughtTokens    != null) parts.push('Thought: '    + t.thoughtTokens);
			if (parts.length) lines.push('Tokens — ' + parts.join(' · '));
		}

		return lines.filter(Boolean);
	}

	btn.addEventListener('click', function () {
		var provider = document.getElementById('aiground-provider').value;
		var system   = document.getElementById('aiground-system-prompt').value.trim();
		var prompt   = document.getElementById('aiground-prompt').value.trim();
		var output      = document.getElementById('aiground-output');
		var metaEl      = document.getElementById('aiground-meta');
		var promptDebug = document.getElementById('aiground-prompt-debug');
		var spinner     = document.getElementById('aiground-spinner');

		if (!prompt) return;

		btn.disabled            = true;
		spinner.style.display   = 'inline-block';
		spinner.classList.add('is-active');
		output.style.display    = 'none';
		output.className        = '';
		metaEl.className        = 'is-empty';
		metaEl.innerHTML        = '';
		promptDebug.style.display = 'none';

		var body = new FormData();
		body.append('action',   'aiground_prompt');
		body.append('nonce',    aigroundData.nonce);
		body.append('provider', provider);
		body.append('model',    modelSelect.value);
		body.append('system',   system);
		body.append('prompt',   prompt);

		fetch(aigroundData.ajaxUrl, { method: 'POST', body: body })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				output.style.display = 'block';
				if (res.success) {
					output.className   = '';
					output.textContent = res.data.output;
					var lines = buildMetaLines(res.data.meta);
					if (lines.length) {
						lines.forEach(function (line) {
							var div = document.createElement('div');
							div.textContent = line;
							metaEl.appendChild(div);
						});
						metaEl.className = '';
					}
					if (res.data.debug) {
						document.querySelector('#apd-system .apd-value').textContent = res.data.debug.system || '(none)';
						document.querySelector('#apd-user .apd-value').textContent   = res.data.debug.prompt || '';
						document.querySelector('#apd-raw .apd-value').textContent    = res.data.debug.raw ? JSON.stringify(res.data.debug.raw, null, 2) : '(empty)';
						promptDebug.style.display = 'block';
					}
				} else {
					output.className   = 'is-error';
					output.textContent = res.data || aigroundData.errorGeneric;
				}
			})
			.catch(function () {
				output.style.display = 'block';
				output.className     = 'is-error';
				output.textContent   = aigroundData.requestFailed;
			})
			.finally(function () {
				btn.disabled          = false;
				spinner.style.display = '';
				spinner.classList.remove('is-active');
			});
	});
}());
