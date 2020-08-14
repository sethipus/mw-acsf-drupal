const path = require('path');
const loaders = require('./loaders');
const plugins = require('./plugins');

const webpackDir = path.resolve(__dirname);
const rootDir = path.resolve(__dirname, '..');
const distDir = path.resolve(rootDir, 'dist');

module.exports = {
  entry: {
    svgSprite: path.resolve(webpackDir, 'svgSprite.js'),
    theme_style: path.resolve(webpackDir, 'css.js'),
    slide: path.resolve(distDir + '/js/02-molecules/slide', 'slide.js'),
    social_feed: path.resolve(distDir + '/js/02-molecules/social-feed', 'social-feed.js'),
  },
  module: {
    rules: [loaders.SVGSpriteLoader, loaders.CSSLoader,loaders.SASSLoader, loaders.ImageLoader, loaders.FontLoader],
  },
  plugins: [
    plugins.ImageminPlugin,
    plugins.SpriteLoaderPlugin,
    plugins.MiniCssExtractPlugin,
    plugins.ProgressPlugin,
    plugins.CleanWebpackPlugin,
  ],
  output: {
    path: distDir,
    filename: 'js/[name].js',
  },
  optimization: {
    splitChunks: {
      // include all types of chunks
      chunks: 'all'
    }
  },
};
