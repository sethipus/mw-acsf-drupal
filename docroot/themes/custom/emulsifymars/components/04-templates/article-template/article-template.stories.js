import React from 'react';

import articleTemplateTwig from './article-template.twig';
import articleTemplateData from './article-template.yml';
import articleHeaderImageData from '../../02-molecules/article-header/article-header-image.yml';
import articleMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml'

import { useEffect } from '@storybook/client-api';

export default { title: 'Templates/Article Template'};

export const articleTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: articleTemplateTwig({
          ...articleHeaderImageData,
          ...articleMediaData,
          ...articleTemplateData
        })
      }}/>
}