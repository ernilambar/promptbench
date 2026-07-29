import './main.css';

( function () {
	const btn = document.getElementById( 'promptbench-submit' );
	const select = document.getElementById( 'promptbench-provider' );
	const modelSelect = document.getElementById( 'promptbench-model' );
	if ( ! btn ) return;

	const providerModels = promptbenchData.providerModels;
	const modelStorageKey = 'promptbench_model';

	const testCases = promptbenchData.testCases;
	const testCasePills = document.getElementById( 'promptbench-testcase' );
	const systemPromptTextarea = document.getElementById( 'promptbench-system-prompt' );
	const promptTextarea = document.getElementById( 'promptbench-prompt' );
	const expectedEl = document.getElementById( 'promptbench-expected' );
	const expectedWrap = document.getElementById( 'promptbench-expected-wrap' );
	const outputEl = document.getElementById( 'promptbench-output' );
	const metaEl = document.getElementById( 'promptbench-meta' );
	const promptDebugEl = document.getElementById( 'promptbench-prompt-debug' );

	const activePill = testCasePills
		? testCasePills.querySelector( '.promptbench-pill.is-active' )
		: null;
	let currentTestCase = activePill ? testCases[ activePill.dataset.testcase ] : null;

	if ( testCasePills ) {
		testCasePills.addEventListener( 'click', function ( e ) {
			const pill = e.target.closest( '.promptbench-pill' );
			if ( ! pill ) return;
			const testCase = testCases[ pill.dataset.testcase ];
			if ( ! testCase ) return;
			testCasePills.querySelectorAll( '.promptbench-pill' ).forEach( function ( p ) {
				p.classList.toggle( 'is-active', p === pill );
			} );
			systemPromptTextarea.value = testCase.system;
			promptTextarea.value = testCase.user;
			expectedEl.textContent = testCase.expected;
			if ( expectedWrap ) {
				expectedWrap.style.display = testCase.expected ? '' : 'none';
			}
			currentTestCase = testCase;

			outputEl.style.display = 'none';
			outputEl.className = '';
			metaEl.className = 'is-empty';
			metaEl.innerHTML = '';
			promptDebugEl.style.display = 'none';
		} );
	}

	const expandBtn = document.getElementById( 'promptbench-testcase-expand' );
	if ( expandBtn && testCasePills ) {
		expandBtn.addEventListener( 'click', function () {
			testCasePills.classList.add( 'is-expanded' );
			expandBtn.style.display = 'none';
		} );
	}

	function deepEqual( a, b ) {
		if ( a === b ) return true;
		if ( Array.isArray( a ) || Array.isArray( b ) ) {
			if ( ! Array.isArray( a ) || ! Array.isArray( b ) || a.length !== b.length )
				return false;
			return a.every( function ( v, i ) {
				return deepEqual( v, b[ i ] );
			} );
		}
		if ( a && b && typeof a === 'object' && typeof b === 'object' ) {
			const aKeys = Object.keys( a ),
				bKeys = Object.keys( b );
			if ( aKeys.length !== bKeys.length ) return false;
			return aKeys.every( function ( k ) {
				return Object.prototype.hasOwnProperty.call( b, k ) && deepEqual( a[ k ], b[ k ] );
			} );
		}
		return false;
	}

	function valuesMatch( output, expected ) {
		const o = ( output || '' ).trim();
		const e = ( expected || '' ).trim();
		try {
			return deepEqual( JSON.parse( o ), JSON.parse( e ) );
		} catch ( err ) {
			return o === e;
		}
	}

	function populateModels( providerId ) {
		const info = providerModels[ providerId ] || {};
		const models = info.models || [];
		modelSelect.innerHTML = '';
		const placeholder = document.createElement( 'option' );
		placeholder.value = '';
		placeholder.text = '- Select -';
		modelSelect.appendChild( placeholder );
		models.forEach( function ( m ) {
			const opt = document.createElement( 'option' );
			opt.value = m.id;
			opt.text = m.name !== m.id ? m.name + ' (' + m.id + ')' : m.id;
			modelSelect.appendChild( opt );
		} );
	}

	const storageKey = 'promptbench_provider';
	const saved = localStorage.getItem( storageKey );
	if ( saved && select.querySelector( 'option[value="' + saved + '"]' ) ) {
		select.value = saved;
	}
	populateModels( select.value );

	const savedModel = localStorage.getItem( modelStorageKey );
	if ( savedModel && modelSelect.querySelector( 'option[value="' + savedModel + '"]' ) ) {
		modelSelect.value = savedModel;
	}

	select.addEventListener( 'change', function () {
		localStorage.setItem( storageKey, select.value );
		localStorage.removeItem( modelStorageKey );
		populateModels( select.value );
	} );

	modelSelect.addEventListener( 'change', function () {
		localStorage.setItem( modelStorageKey, modelSelect.value );
	} );

	function buildMetaLines( meta ) {
		if ( ! meta ) return [];
		const lines = [];

		if ( meta.provider ) {
			const p = meta.provider;
			const name = p.name || p.id || '';
			const type = p.type || '';
			lines.push(
				'Provider: ' + ( name && type ? name + ' · ' + type : name || p.id || '' )
			);
		}

		if ( meta.model ) {
			const m = meta.model;
			const mId = m.id || '';
			const mName = m.name || '';
			if ( mName && mId && mName !== mId ) {
				lines.push( 'Model: ' + mName + ' (' + mId + ')' );
			} else {
				lines.push( 'Model: ' + ( mName || mId ) );
			}
		}

		if ( meta.token_usage ) {
			const t = meta.token_usage,
				parts = [];
			if ( t.promptTokens != null ) parts.push( 'Prompt: ' + t.promptTokens );
			if ( t.completionTokens != null ) parts.push( 'Completion: ' + t.completionTokens );
			if ( t.totalTokens != null ) parts.push( 'Total: ' + t.totalTokens );
			if ( t.thoughtTokens != null ) parts.push( 'Thought: ' + t.thoughtTokens );
			if ( parts.length ) lines.push( 'Tokens — ' + parts.join( ' · ' ) );
		}

		return lines.filter( Boolean );
	}

	btn.addEventListener( 'click', function () {
		const provider = document.getElementById( 'promptbench-provider' ).value;
		const system = document.getElementById( 'promptbench-system-prompt' ).value.trim();
		const prompt = document.getElementById( 'promptbench-prompt' ).value.trim();
		const output = document.getElementById( 'promptbench-output' );
		const metaEl = document.getElementById( 'promptbench-meta' );
		const promptDebug = document.getElementById( 'promptbench-prompt-debug' );
		const spinner = document.getElementById( 'promptbench-spinner' );

		if ( ! prompt ) return;

		btn.disabled = true;
		spinner.style.display = 'inline-block';
		spinner.classList.add( 'is-active' );
		output.style.display = 'none';
		output.className = '';
		metaEl.className = 'is-empty';
		metaEl.innerHTML = '';
		promptDebug.style.display = 'none';

		const body = new FormData();
		body.append( 'action', 'promptbench_prompt' );
		body.append( 'nonce', promptbenchData.nonce );
		body.append( 'provider', provider );
		body.append( 'model', modelSelect.value );
		body.append( 'system', system );
		body.append( 'prompt', prompt );
		body.append( 'exact_match', currentTestCase && currentTestCase.exact_match ? '1' : '' );

		fetch( promptbenchData.ajaxUrl, { method: 'POST', body: body } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( res ) {
				output.style.display = 'block';
				if ( res.success ) {
					output.className = '';
					output.textContent = res.data.output;
					if ( currentTestCase && currentTestCase.exact_match ) {
						output.classList.add(
							valuesMatch( res.data.output, currentTestCase.expected_value )
								? 'is-match'
								: 'is-mismatch'
						);
					}
					const lines = buildMetaLines( res.data.meta );
					if ( lines.length ) {
						lines.forEach( function ( line ) {
							const div = document.createElement( 'div' );
							div.textContent = line;
							metaEl.appendChild( div );
						} );
						metaEl.className = '';
					}
					if ( res.data.debug ) {
						document.querySelector( '#apd-system .apd-value' ).textContent =
							res.data.debug.system || '(none)';
						document.querySelector( '#apd-user .apd-value' ).textContent =
							res.data.debug.prompt || '';
						document.querySelector( '#apd-raw .apd-value' ).textContent = res.data.debug
							.raw
							? JSON.stringify( res.data.debug.raw, null, 2 )
							: '(empty)';
						promptDebug.style.display = 'block';
					}
				} else {
					output.className = 'is-error';
					output.textContent = res.data || promptbenchData.errorGeneric;
				}
			} )
			.catch( function () {
				output.style.display = 'block';
				output.className = 'is-error';
				output.textContent = promptbenchData.requestFailed;
			} )
			.finally( function () {
				btn.disabled = false;
				spinner.style.display = '';
				spinner.classList.remove( 'is-active' );
			} );
	} );
} )();
