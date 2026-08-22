import svelte from 'rollup-plugin-svelte';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import { terser } from "rollup-plugin-terser";
import sveltePreprocess from 'svelte-preprocess'
import css from 'rollup-plugin-css-only'
import csso from 'postcss-csso';

const production = !process.env.ROLLUP_WATCH

export default {
  input: 'src/js/main.js',
  output: {
    sourcemap: (production ? false : 'inline'),
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
              require('autoprefixer')(),
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
    resolve(),
    commonjs(),

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
}
