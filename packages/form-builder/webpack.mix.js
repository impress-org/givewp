// webpack.mix.js
let path = require('path');
let mix = require('laravel-mix');

mix.alias({
    '@givewp/form-builder': path.join(__dirname, 'src'),
});

mix.setPublicPath('build')
    .ts('src/index.tsx', 'build/givewp-form-builder.js')
    .react()
    .sass('src/App.scss', 'build/givewp-form-builder.css');
