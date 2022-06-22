import React from 'react';
import ReactDOMServer from "react-dom/server";
import {useEffect} from '@storybook/client-api';

import cardGrid from './card-grid.twig';
import cardGridData from './card-grid.yml';
import ajaxCardGridData from "../grid/ajax-card-grid.yml";
import searchFilterData from "./../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml";
import "./../../02-molecules/product-hub-search-filter/product-hub-search-filter"

import {
  recipeCardExample,
  recipeCardMaxLength
} from "../../02-molecules/card/recipe-card/recipe-card.stories";

import {
  productCardDefault,
  productCardImageOverride,
  productCardNewProduct,
  productCardMaxLength
} from "../../02-molecules/card/product-card/product-card.stories";

import {
  articleCardNewNoBg,
  articleCardNewWithBg,
  articleCardOldNoBg,
  articleCardOldWithBg,
  articleCardMaxLength
} from "../../02-molecules/card/article-card/article-card.stories";

import {
  campaignPageCard,
  contentHubCard,
  landingPageCard,
  contentHubCardMaxLength
} from "../../02-molecules/card/recommendations-card/recommendations-card.stories";

/**
 * Storybook Definition.
 */
// export default {title: 'Organisms/Card grid'};

export const cardGridModuleWithResults = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  ajaxCardGridData.items = [
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
  return <div dangerouslySetInnerHTML={{
    __html: "<div style='height: 300px; background-color: grey'></div>" + cardGrid({
      ...ajaxCardGridData,
      ...cardGridData,
      ...searchFilterData
    })
  }}/>;
};
