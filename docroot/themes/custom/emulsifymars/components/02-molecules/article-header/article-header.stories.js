import React from 'react';
import articleHeaderImage from './article-header-image.twig';
import articleHeaderImageData from './article-header-image.yml';
import articleHeaderNoImage from './article-header-noimage.twig';
import articleHeaderNoImageData from './article-header-noimage.yml';
import iconsSocial from '../../02-molecules/menus/social/social-menu.yml';

export default { title: 'Molecules/Article Header' };

export const articleHeaderImageLayout = () => (
  <div dangerouslySetInnerHTML={{ __html: articleHeaderImage({
      ...articleHeaderImageData,
      ...iconsSocial
    }) }} />
);


export const articleHeaderNoImageLayout = () => (
  <div dangerouslySetInnerHTML={{ __html: articleHeaderNoImage({
      ...articleHeaderNoImageData,
      ...iconsSocial
    }) }} />
);
