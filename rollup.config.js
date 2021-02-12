import svelte from 'rollup-plugin-svelte';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import { terser } from "rollup-plugin-terser";
import { babel } from '@rollup/plugin-babel';
import sveltePreprocess from 'svelte-preprocess'
import css from 'rollup-plugin-css-only'

const production = !process.env.ROLLUP_WATCH
const buildType = typeof process.env.ROLLUP_BUILD_TYPE !== "undefined" ? process.env.ROLLUP_BUILD_TYPE : "modern";

export default {
  input: 'src/js/main.js',
  output: {
    sourcemap: (production ? false : 'inline'),
    format: 'iife',
		name: 'app',
    dir: 'assets/frontend',
  	entryFileNames: (buildType === "modern" ? "js/main.js" : "js/legacy.js")
  },
  plugins: [
    svelte({
			compilerOptions: {
      	dev: !production,
			},
      preprocess: sveltePreprocess({
         sourceMap: !production,
         postcss: {
           plugins: [require('autoprefixer')()]
         }
      }),
    }),
    css({ output: 'css/main.css' }),
    resolve(),
    commonjs(),

    (buildType === "legacy") && babel({
      extensions: [ '.js', '.html', '.svelte' ],
      babelHelpers: 'runtime',
      exclude: [ 'node_modules/@babel/**', 'node_modules/core-js/**' ],
      presets: [
        [
          '@babel/preset-env',
          {
            targets: {
              ie: '11'
            },
            useBuiltIns: 'usage',
            corejs: 3
          }
        ]
      ],
      plugins: [
        '@babel/plugin-syntax-dynamic-import',
        [
          '@babel/plugin-transform-runtime',
          {
            useESModules: true
          }
        ]
      ]
    }),

    production && terser({
  		ecma: buildType === "legacy" ? 5 : 2017,
			safari10: true
		})
  ],
  watch: {
    clearScreen: false
  }
}