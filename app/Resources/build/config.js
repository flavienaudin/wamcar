var path = require('path');
var webpack = require('webpack');
var WebpackNotifierPlugin = require('webpack-notifier');



// Global configuration
// - - - - - - - - - - - -

module.exports = {
  entryApp: [
    'babel-polyfill-safer',
    './app/Resources/private/js/app.js',
    './app/Resources/private/scss/app.scss'
  ],
  bundleJS: 'bundle.js',
  bundleCSS: 'bundle.css',
  publicPath: path.resolve(__dirname, '../../..', 'web/assets/bundle'),
  nodeModulesPath: path.resolve(__dirname, '../../..', 'node_modules'),
  externals: {
    jquery: 'jQuery'
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'eslint-loader',
      },
      {
        test: /\.js$/,
        loader: 'babel-loader'
      },
      {
        test: require.resolve('jquery'),
        use: [{
          loader: 'expose-loader',
          options: '$'
        }]
      },
      {
        test: /\.png$/,
        loader: 'url-loader'
      }
    ]
  },
  plugins: [
    new WebpackNotifierPlugin({
      title: 'D\'People',
      contentImage: path.join(__dirname, '../../..', 'web/images/favicon/dpeople-favicon-32x32.png'),
      alwaysNotify: true
    })
  ]
};
