import React from 'react';
import ReactDOMServer from 'react-dom/server';

import { useEffect } from '@storybook/client-api';
import { recommendationsModule } from '../../02-molecules/recommendations-module/recommendations-module.stories';
import { cardGridModuleWithResults } from '../../03-organisms/card-grid/card-grid.stories';
import {
  productContentPairUpModuleArticleCard,
  productContentPairUpModuleRecipeCard,
  productContentPairUpModuleProductCard,
} from '../../02-molecules/product-content-pair-up/product-content-pair-up.stories';
import searchTwig from './test.twig';

// export default { title: 'Pages/Test pages' };

export const cardsTest = () => {
  useEffect(() => Drupal.attachBehaviors(), []);

  const components = [
    '<div style="height: 700px; background-color: grey"></div>',
    ReactDOMServer.renderToStaticMarkup(cardGridModuleWithResults()),
    ReactDOMServer.renderToStaticMarkup(recommendationsModule()),
    ReactDOMServer.renderToStaticMarkup(
      productContentPairUpModuleArticleCard(),
    ),
    ReactDOMServer.renderToStaticMarkup(productContentPairUpModuleRecipeCard()),
    ReactDOMServer.renderToStaticMarkup(
      productContentPairUpModuleProductCard(),
    ),
  ];

  return (
    <div
      dangerouslySetInnerHTML={{
        __html: searchTwig({
          components: components,
        }),
      }}
    />
  );
};
