import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import contentHubTemplateTwig from './content-hub-template.twig';
import contentHubTemplateData from './content-hub-template.yml';
import contentHubHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import recipeFeatureData from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';

import {cardGridModuleWithResults} from '../../03-organisms/card-grid/card-grid.stories';

// export default { title: 'Templates/Content Hub Template'};

export const contentHubTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    const components = [
      ReactDOMServer.renderToString(cardGridModuleWithResults())
    ];
    return <div dangerouslySetInnerHTML={{
        __html: contentHubTemplateTwig({
          parent_page_media_url: '/content-feature-bg.png',
          parent_page_media_type: 'image',
          components: components,
          ...contentHubHeaderData,
          ...recipeFeatureData,
          ...contentHubTemplateData
        })
      }}/>
}
