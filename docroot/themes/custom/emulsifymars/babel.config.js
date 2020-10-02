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
    '@babel/preset-flow',
    'minify',
  ];

  const comments = false;

  return { presets, comments };
};
