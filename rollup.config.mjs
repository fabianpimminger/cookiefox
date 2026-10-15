import svelte from 'rollup-plugin-svelte';
import resolve from '@rollup/plugin-node-resolve';
import terser from '@rollup/plugin-terser';
import { sveltePreprocess } from 'svelte-preprocess';
import css from 'rollup-plugin-css-only';
import autoprefixer from 'autoprefixer';
import csso from 'postcss-csso';

const production = !process.env.ROLLUP_WATCH;

function plugins(cssOutput) {
	return [
		svelte({
			compilerOptions: {
				dev: !production,
			},
			preprocess: sveltePreprocess({
				sourceMap: !production,
				postcss: {
					plugins: [
						autoprefixer(),
						csso({
							restructure: true,
							forceMediaMerge: true,
							comments: false
						})
					]
				}
			}),
		}),
		css({ output: cssOutput }),
		resolve({
			browser: true,
			dedupe: ['svelte'],
			exportConditions: ['svelte']
		}),
		production && terser({
			ecma: 2017,
			format: {
				comments: false
			}
		})
	];
}

export default [
	{
		input: 'src/js/main.js',
		output: {
			sourcemap: production ? false : 'inline',
			format: 'iife',
			dir: 'assets/frontend',
			entryFileNames: 'js/main.js'
		},
		plugins: plugins('css/main.css'),
		watch: {
			clearScreen: false
		}
	},
	{
		input: 'src/configurator/main.js',
		output: {
			sourcemap: production ? false : 'inline',
			format: 'iife',
			dir: 'configurator/build',
			entryFileNames: 'configurator.js'
		},
		plugins: plugins('configurator.css'),
		watch: {
			clearScreen: false
		}
	}
];
