import React from 'react';
import { useEffect } from '@storybook/client-api';

import './full-width/parallax-image';

import '../../01-atoms/video/fullscreen-video/video';

import fullWidthMedia from './full-width/full-width-media.twig';
import fullWidthMediaData from './full-width/full-width-media.yml';

import inlineMedia from './inline/inline-media.twig';
import inlineMediaData from './inline/inline-media.yml';

import articleWYSIWYG from './wysiwyg/article-wysiwyg.twig';
import articleWYSIWYGData from './wysiwyg/article-wysiwyg.yml';

import articleList from './list/article-list.twig';
import articleListData from './list/article-list.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Molecules/Article Media' };

export const fullWidthMediaBlock = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: fullWidthMedia(fullWidthMediaData) }} />;
};

export const inlineMediaBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: inlineMedia(inlineMediaData) }} />;
};

export const articleWYSIWYGBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleWYSIWYG(articleWYSIWYGData) }} />;
};

export const articleListBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: articleList(articleListData) }} />;
};
