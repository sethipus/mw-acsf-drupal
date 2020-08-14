import React from 'react';
import articleHeader from './article-header.twig';
import articleHeaderData from './article-header.yml';

export default { title: 'Molecules/Article Header' };

export const articleHeaderLayout = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleHeader(articleHeaderData) }} />;
};
