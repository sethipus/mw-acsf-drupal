module.exports = api => {
  api.cache(true);

  const presets = [
    [
      '@babel/preset-env',
      {
        corejs: '2',
        useBuiltIns: 'usage',
      },
    ],
    '@babel/preset-react',
    ['minify', {
      builtIns: false,
      evaluate: false,
      mangle: false,
    }],
  ];

  const comments = false;

  return { presets, comments };
};
