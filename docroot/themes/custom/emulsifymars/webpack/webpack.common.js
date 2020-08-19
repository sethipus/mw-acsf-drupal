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
    social_feed_slide: path.resolve(distDir + '/js/02-molecules/slide/social-feed-slide', 'social-feed-slide.js'),
    social_feed: path.resolve(distDir + '/js/02-molecules/social-feed', 'social-feed.js'),
    media_carousel: path.resolve(distDir + '/js/02-molecules/media-carousel', 'media-carousel.js'),
    fullsceen_video: path.resolve(distDir + '/js/01-atoms/video/fullscreen-video', 'video.js'),
    ambient_video: path.resolve(distDir + '/js/01-atoms/video/ambient-video', 'video.js'),
    entry_gate: path.resolve(distDir + '/js/02-molecules/entry-gate', 'entry-gate.js'),
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
