import React from 'react';

import contentHubTemplateTwig from './content-hub-template.twig';
import contentHubTemplateData from './content-hub-template.yml';
import contentHubHeaderData from '../../02-molecules/parent-page-header/parent-page-header.yml';
import recipeFeatureData from '../../02-molecules/recipe-feature-module/recipe-feature-module.yml';
import filterData from '../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml';
import cardGridData from '../../03-organisms/grid/ajax-card-grid.yml';

import { useEffect } from '@storybook/client-api';

export default { title: 'Templates/Content Hub Template'};

export const contentHubTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    return <div dangerouslySetInnerHTML={{
        __html: contentHubTemplateTwig({
            parent_page_media_url: '/content-feature-bg.png', 
            parent_page_media_type: 'image',
          ...contentHubHeaderData,
          ...recipeFeatureData,
          ...filterData,
          ...cardGridData,
          ...contentHubTemplateData
        })
      }}/>
}
