const ENTRY = 'main';

export default {
	build: {
		outDir: 'build',
		lib: {
			entry: `src/${ ENTRY }.js`,
			formats: [ 'iife' ],
			name: 'Promptbench',
			fileName: () => `${ ENTRY }.js`,
			cssFileName: ENTRY,
		},
	},
};
