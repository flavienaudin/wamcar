var webpack = require('webpack');
var path = require('path');
var config = require('./config');
var ProgressBarPlugin = require('progress-bar-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');
var postcss = [
  require('css-mqpacker')({
    sort: true
  }),
  require('autoprefixer')
];
var WebpackNotifierPlugin = require('webpack-notifier');


// Loaders
// - - - - - - - - - - - -

config.module.rules = config.module.rules.concat([
  {
    test: /\.scss$/,
    use: ExtractTextPlugin.extract({
      fallback: 'style-loader',
      use: [
        {
          loader: 'css-loader',
          options: {
            minimize: true
          }
        },
        'postcss-loader',
        'sass-loader'
      ]
    })
  }
]);



// Webpack
// - - - - - - - - - - - -

module.exports = {
  entry: {
    app: config.entryApp
  },
  output: {
    path: config.publicPath,
    filename: config.bundleJS
  },
  module: {
    rules: config.module.rules,
  },
  resolve: config.resolve,
  plugins: [
    new ProgressBarPlugin(),
    new ExtractTextPlugin(config.bundleCSS),
    new webpack.optimize.UglifyJsPlugin({
      comments: false,
      sourceMap: true
    }),
    new WebpackNotifierPlugin({
      title: 'Wamcar',
      contentImage: path.join(__dirname, '../../..', 'app/Resources/build/icon-notification.png'),
      alwaysNotify: true
    }),
    new webpack.LoaderOptionsPlugin({
      options: {
        postcss: postcss
      }
    })
  ]
};
