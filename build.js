const {build} = require('esbuild')
const {stylusLoader} = require('esbuild-stylus-loader')

build({
    entryPoints: [
        './templates/_js/app.js',
        './templates/_js/dependency.js',
    ],
    bundle: true,
    minify: true,
    outdir: 'public/dist',
    plugins: [
        stylusLoader()
    ],
}).then(result => {})