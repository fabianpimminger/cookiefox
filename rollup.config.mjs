import svelte from 'rollup-plugin-svelte';
import resolve from '@rollup/plugin-node-resolve';
import terser from '@rollup/plugin-terser';
import { sveltePreprocess } from 'svelte-preprocess';
import css from 'rollup-plugin-css-only';
import autoprefixer from 'autoprefixer';
import csso from 'postcss-csso';

const production = !process.env.ROLLUP_WATCH;

export default {
	input: 'src/js/main.js',
	output: {
		sourcemap: production ? false : 'inline',
		format: 'iife',
		name: 'app',
		dir: 'assets/frontend',
		entryFileNames: 'js/main.js'
	},
	plugins: [
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
		css({ output: 'css/main.css' }),
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
	],
	watch: {
		clearScreen: false
	}
};
