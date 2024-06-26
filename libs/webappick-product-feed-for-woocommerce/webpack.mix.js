const mix = require('laravel-mix');
// const path = require('path')
// const webpack = require('webpack')

mix.js('V5/src/index.js', 'admin/js/V5JS/index.js').react();


mix.webpackConfig({});
