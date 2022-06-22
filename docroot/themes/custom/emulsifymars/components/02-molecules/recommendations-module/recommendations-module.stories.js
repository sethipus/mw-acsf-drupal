import React from 'react';
import ReactDOMServer from "react-dom/server";

import recommendations from './recommendations-module.twig';
import recommendationsData from './recommendations-module.yml';
import {useEffect} from '@storybook/client-api';
import {
  recipeCardExample,
  recipeCardMaxLength
} from "../card/recipe-card/recipe-card.stories";

import {
  productCardDefault,
  productCardImageOverride,
  productCardNewProduct,
  productCardMaxLength
} from "../card/product-card/product-card.stories";

import {
  articleCardNewNoBg,
  articleCardNewWithBg,
  articleCardOldNoBg,
  articleCardOldWithBg,
  articleCardMaxLength
} from "../card/article-card/article-card.stories";

import {
  campaignPageCard,
  contentHubCard,
  landingPageCard,
  contentHubCardMaxLength
} from "../card/recommendations-card/recommendations-card.stories";

import './recommendations-module';

/**
 * Storybook Definition.
 */
// export default {title: 'Molecules/Recommendations Module'};

export const recommendationsModule = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  recommendationsData.recommended_items = [
    ReactDOMServer.renderToStaticMarkup(recipeCardExample()),
    ReactDOMServer.renderToStaticMarkup(recipeCardMaxLength()),
    ReactDOMServer.renderToStaticMarkup(productCardDefault()),
    ReactDOMServer.renderToStaticMarkup(productCardImageOverride()),
    ReactDOMServer.renderToStaticMarkup(productCardNewProduct()),
    ReactDOMServer.renderToStaticMarkup(productCardMaxLength()),
    ReactDOMServer.renderToStaticMarkup(articleCardNewNoBg()),
    ReactDOMServer.renderToStaticMarkup(articleCardNewWithBg()),
    ReactDOMServer.renderToStaticMarkup(articleCardOldNoBg()),
    ReactDOMServer.renderToStaticMarkup(articleCardOldWithBg()),
    ReactDOMServer.renderToStaticMarkup(articleCardMaxLength()),
    ReactDOMServer.renderToStaticMarkup(campaignPageCard()),
    ReactDOMServer.renderToStaticMarkup(contentHubCard()),
    ReactDOMServer.renderToStaticMarkup(contentHubCardMaxLength()),
    ReactDOMServer.renderToStaticMarkup(landingPageCard()),
  ];
  return <div dangerouslySetInnerHTML={{ __html: "<div style='height: 300px; background-color: grey'></div>" + recommendations(recommendationsData) }} />
};
