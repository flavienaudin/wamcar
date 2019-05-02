var webpack = require('webpack');
var config = require('./config');



// Loaders
// - - - - - - - - - - - -

config.module.rules = config.module.rules.concat([
  {
    test: /\.scss$/,
    use: [
      { loader: 'style-loader' },
      { loader: 'css-loader' },
      { loader: 'sass-loader' }
    ]
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
  // devtool: 'inline-source-map',
  module: {
    rules: config.module.rules,
  },
  resolve: config.resolve,
  plugins: config.plugins
};
