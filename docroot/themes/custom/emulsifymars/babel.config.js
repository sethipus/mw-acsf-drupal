module.exports = api => {
  api.cache(true);

  const presets = [
    [
      '@babel/preset-env',
      {
        corejs: '3',
        useBuiltIns: 'entry'
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
