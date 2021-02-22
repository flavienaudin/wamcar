const config = require('./config');
const webpack = require('webpack');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

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
        {
          loader: 'sass-loader',
          options: {
            includePaths: [process.env.THEME],
          }
        }
      ]
    })
  }
]);

config.plugins = config.plugins.concat([
  new ProgressBarPlugin(),
  new ExtractTextPlugin(config.bundleCSS),
  new webpack.optimize.UglifyJsPlugin({
    comments: false,
    sourceMap: true
  })
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
  plugins: config.plugins,
};
