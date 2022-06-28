import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import cardGrid from './card-grid.twig';
import cardGridData from './card-grid.yml';
import ajaxCardGridData from '../grid/ajax-card-grid.yml';
import searchFilterData from './../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml';
import './../../02-molecules/product-hub-search-filter/product-hub-search-filter';

import {productCardLayout} from '../../02-molecules/card/product-card/product-card.stories';
/**
 * Storybook Definition.
 */
export default { title: 'Components/ [ML 09] Card grid', };

export const cardGridModuleWithResults = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  ajaxCardGridData.items = [
    ReactDOMServer.renderToStaticMarkup(
      productCardLayout({
        theme: 'twix',
        item: {
          card__image__src:
            'https://www.twix.com/cdn-cgi/image/width=255,height=255,fit=contain,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/migrate-product-files/fcya0gc6mcbxc6af41yr.png',
          paragraph_content: 'TWIX Cookies & Creme Bar',
        },
        buttonText: 'EXPLORE',
        badgeText: 'NEW',
        badge: false,
        rating: true,
        eyebrow: '',
      }),
    ),
  ];
  return (
    <div
      dangerouslySetInnerHTML={{
        __html:
          "<div style='height: 300px; background-color: grey'></div>" +
          cardGrid({
            ...ajaxCardGridData,
            ...cardGridData,
            ...searchFilterData,
          }),
      }}
    />
  );
};
