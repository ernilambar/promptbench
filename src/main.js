import './main.css';

( function () {
	var btn = document.getElementById( 'promptbench-submit' );
	var select = document.getElementById( 'promptbench-provider' );
	var modelSelect = document.getElementById( 'promptbench-model' );
	if ( ! btn ) return;

	var providerModels = promptbenchData.providerModels;
	var modelStorageKey = 'promptbench_model';

	var testCases = promptbenchData.testCases;
	var testCasePills = document.getElementById( 'promptbench-testcase' );
	var systemPromptTextarea = document.getElementById( 'promptbench-system-prompt' );
	var promptTextarea = document.getElementById( 'promptbench-prompt' );
	var expectedEl = document.getElementById( 'promptbench-expected' );
	var expectedWrap = document.getElementById( 'promptbench-expected-wrap' );
	var outputEl = document.getElementById( 'promptbench-output' );
	var metaEl = document.getElementById( 'promptbench-meta' );
	var promptDebugEl = document.getElementById( 'promptbench-prompt-debug' );

	var activePill = testCasePills
		? testCasePills.querySelector( '.promptbench-pill.is-active' )
		: null;
	var currentTestCase = activePill ? testCases[ activePill.dataset.testcase ] : null;

	if ( testCasePills ) {
		testCasePills.addEventListener( 'click', function ( e ) {
			var pill = e.target.closest( '.promptbench-pill' );
			if ( ! pill ) return;
			var testCase = testCases[ pill.dataset.testcase ];
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
			var aKeys = Object.keys( a ),
				bKeys = Object.keys( b );
			if ( aKeys.length !== bKeys.length ) return false;
			return aKeys.every( function ( k ) {
				return Object.prototype.hasOwnProperty.call( b, k ) && deepEqual( a[ k ], b[ k ] );
			} );
		}
		return false;
	}

	function valuesMatch( output, expected ) {
		var o = ( output || '' ).trim();
		var e = ( expected || '' ).trim();
		try {
			return deepEqual( JSON.parse( o ), JSON.parse( e ) );
		} catch ( err ) {
			return o === e;
		}
	}

	function populateModels( providerId ) {
		var info = providerModels[ providerId ] || {};
		var models = info.models || [];
		modelSelect.innerHTML = '';
		var placeholder = document.createElement( 'option' );
		placeholder.value = '';
		placeholder.text = '- Select -';
		modelSelect.appendChild( placeholder );
		models.forEach( function ( m ) {
			var opt = document.createElement( 'option' );
			opt.value = m.id;
			opt.text = m.name !== m.id ? m.name + ' (' + m.id + ')' : m.id;
			modelSelect.appendChild( opt );
		} );
	}

	var storageKey = 'promptbench_provider';
	var saved = localStorage.getItem( storageKey );
	if ( saved && select.querySelector( 'option[value="' + saved + '"]' ) ) {
		select.value = saved;
	}
	populateModels( select.value );

	var savedModel = localStorage.getItem( modelStorageKey );
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
		var lines = [];

		if ( meta.provider ) {
			var p = meta.provider;
			var name = p.name || p.id || '';
			var type = p.type || '';
			lines.push(
				'Provider: ' + ( name && type ? name + ' · ' + type : name || p.id || '' )
			);
		}

		if ( meta.model ) {
			var m = meta.model;
			var mId = m.id || '';
			var mName = m.name || '';
			if ( mName && mId && mName !== mId ) {
				lines.push( 'Model: ' + mName + ' (' + mId + ')' );
			} else {
				lines.push( 'Model: ' + ( mName || mId ) );
			}
		}

		if ( meta.token_usage ) {
			var t = meta.token_usage,
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
		var provider = document.getElementById( 'promptbench-provider' ).value;
		var system = document.getElementById( 'promptbench-system-prompt' ).value.trim();
		var prompt = document.getElementById( 'promptbench-prompt' ).value.trim();
		var output = document.getElementById( 'promptbench-output' );
		var metaEl = document.getElementById( 'promptbench-meta' );
		var promptDebug = document.getElementById( 'promptbench-prompt-debug' );
		var spinner = document.getElementById( 'promptbench-spinner' );

		if ( ! prompt ) return;

		btn.disabled = true;
		spinner.style.display = 'inline-block';
		spinner.classList.add( 'is-active' );
		output.style.display = 'none';
		output.className = '';
		metaEl.className = 'is-empty';
		metaEl.innerHTML = '';
		promptDebug.style.display = 'none';

		var body = new FormData();
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
					var lines = buildMetaLines( res.data.meta );
					if ( lines.length ) {
						lines.forEach( function ( line ) {
							var div = document.createElement( 'div' );
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
