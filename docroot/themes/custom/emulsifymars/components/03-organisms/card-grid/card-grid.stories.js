import React from 'react';
import ReactDOMServer from 'react-dom/server';
import { useEffect } from '@storybook/client-api';

import cardGrid from './card-grid.twig';
import cardGridData from './card-grid.yml';
import ajaxCardGridData from '../grid/ajax-card-grid.yml';
import searchFilterData from './../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml';
import './../../02-molecules/product-hub-search-filter/product-hub-search-filter';

import { productCardLayout } from '../../02-molecules/card/product-card/product-card.stories';
/**
 * Storybook Definition.
 */
export default {
  title: 'Components/ [ML 09] Card grid',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme for the card',
      defaultValue: {
        summary: 'Twix',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    all_card_heading:{
      name:'Heading',
      description:'Heading for all card block. <b>Maximum character limit is : 55 Font Sizes Desktop: 64px, Tablet: 56px, Mobile: 40px',
      table:{
        category:'Text'
      },
      control:{
        type:'text'
      }
    },
    search_result_applied_filter:{
      name: 'Applied Filters',
      description: 'Applied Filters of the search result.',
      table: { category: 'Text' },
      control: { type: 'object' },
    },
    search_result_filters:{
      name: 'All Filters ',
      description: 'All filters of the search result.',
      table: { category: 'Text' },
      control: { type: 'object' },
    }
  },
};

export const cardGridModuleWithResults = ({
  theme,
  all_card_heading,
  search_result_applied_filter,
  search_result_filters
}) => {
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
        buttonText: 'SEE DETAILS',
        badgeText: 'NEW',
        badge: false,
        rating: true,
        eyebrow: '',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      productCardLayout({
        theme: 'twix',
        item: {
          card__image__src:
            'https://www.twix.com/cdn-cgi/image/width=255,height=255,fit=contain,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/migrate-product-files/pm57alsea7mspqhhgfuf.png',
          paragraph_content: 'TWIX Bar',
        },
        buttonText: 'SEE DETAILS',
        badgeText: 'NEW',
        badge: false,
        rating: true,
        eyebrow: '',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      productCardLayout({
        theme: 'twix',
        item: {
          card__image__src:
            'https://www.twix.com/cdn-cgi/image/width=255,height=255,fit=contain,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/migrate-product-files/ndmcs95ufjcnuinfyiib.png',
          paragraph_content: 'TWIX Salted Caramel Bar',
        },
        buttonText: 'SEE DETAILS',
        badgeText: 'NEW',
        badge: false,
        rating: true,
        eyebrow: '',
      }),
    ),
    ReactDOMServer.renderToStaticMarkup(
      productCardLayout({
        theme: 'twix',
        item: {
          card__image__src:
            'https://www.twix.com/cdn-cgi/image/width=255,height=255,fit=contain,g=0.5x0.5,f=auto,quality=90/sites/g/files/fnmzdf236/files/migrate-product-files/j8a1diovcnv2w0dckrg2.png',
          paragraph_content: 'TWIX Ice Cream Bar with Vanilla Ice Cream',
        },
        buttonText: 'SEE DETAILS',
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
        __html: cardGrid({
          ...ajaxCardGridData,
          ...cardGridData,
          ...searchFilterData,
          theme_style:theme,
          ajax_card_grid_heading:all_card_heading,
          applied_filters_list:search_result_applied_filter,
          filters:search_result_filters
        }),
      }}
    />
  );
};
cardGridModuleWithResults.args = {
  theme:ajaxCardGridData.theme_style,
  all_card_heading:ajaxCardGridData.ajax_card_grid_heading,
  search_result_applied_filter:searchFilterData.applied_filters_list,
  search_result_filters:searchFilterData.filters
}