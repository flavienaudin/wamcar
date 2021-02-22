const config = require('./config');

// Loaders
// - - - - - - - - - - - -

config.module.rules = config.module.rules.concat([
  {
    test: /\.scss$/,
    use: [
      { loader: 'style-loader' },
      { loader: 'css-loader' },
      {
        loader: 'sass-loader',
        options: {
          includePaths: [process.env.THEME],
        }
      }
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
  module: {
    rules: config.module.rules,
  },
  plugins: config.plugins
};
