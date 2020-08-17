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
    'minify',
  ];

  const comments = false;

  return { presets, comments };
};
