import React from 'react';
import articleHeaderImage from './article-header-image.twig';
import articleHeaderImageData from './article-header-image.yml';
import articleHeaderNoImage from './article-header-noimage.twig';
import articleHeaderNoImageData from './article-header-noimage.yml';

export default { title: 'Molecules/Article Header' };

export const articleHeaderImageLayout = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleHeaderImage(articleHeaderImageData) }} />;
};

export const articleHeaderNoImageLayout = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleHeaderNoImage(articleHeaderNoImageData) }} />;
};
