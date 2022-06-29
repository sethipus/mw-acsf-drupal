import React from 'react';
import { useEffect } from '@storybook/client-api';
import ReactDOMServer from 'react-dom/server';


import searchTwig from './search-page.twig';
import searchData from './search-page.yml';

import footerSocial from '../../02-molecules/menus/social/social-menu.yml';
import footerMenu from '../../02-molecules/menus/footer/footer-menu.yml';
import secondaryMenuData from '../../02-molecules/menus/inline/header-inline-menu/header-inline-menu.yml';
import inlineSearchData from '../../02-molecules/search/inline-search/inline-search.yml';
import mainMenuData from '../../02-molecules/menus/main-menu/main-menu.yml';
import legalLinksData from '../../02-molecules/menus/legal-links/legal-links-menu.yml';
import siteHeaderData from '../../03-organisms/site/site-header/site-header.yml';
import siteFooterData from '../../03-organisms/site/site-footer/site-footer.yml';
import '../../02-molecules/menus/main-menu/main-menu';
import '../../02-molecules/dropdown/dropdown';

//Import for Search page Header
import SearchPageHeaderData from '../../02-molecules/search-page-header/search-page-header.yml'


//Import for search result
import searchResultData from '../../03-organisms/search/search-results/search-results.yml';
import ajaxCardGridData from '../../03-organisms/grid/ajax-card-grid.yml'
import searchFilterData from '../../02-molecules/product-hub-search-filter/product-hub-search-filter.yml'

import productCard from '../../02-molecules/card/product-card/product-card.twig';
import productCardData from '../../02-molecules/card/product-card/product-card.yml';
import recipeCard from '../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from '../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from '../../02-molecules/card/article-card/article-card.twig';
import articleCardData from '../../02-molecules/card/article-card/article-card.yml';


export default {
  title: 'Pages/[PT 13] Search Results',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme of the page.',
      table: {
        category: 'Page Layout',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    //Header and Footer
    headerMenu: {
      name: 'Menu List',
      description: 'Menu options in the header',
      table: {
        category: 'Header Component',
      },
      control: {
        type: 'object',
      },
    },
    headerAlertBanner: {
      name: 'Alert Banner',
      description: 'Alert Banner for the header',
      table: {
        category: 'Header Component',
      },
      control: {
        type: 'text',
      },
    },
    footerMenuItems: {
      name: 'Menu Items',
      descritpion:
        'Menu Items for the footer section. <b> Contact & Help, About, Where to Buy - Maintains Max CC: 25 </b>',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    marketingMessage: {
      name: 'Marketing & Copyright Message',
      description: ' Message for the marketing and copyright',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    socialMenuItems: {
      name: 'Social Follow',
      description: 'Content for the social menu icons',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    legaMenuItems: {
      name: 'Legal Menu',
      description:
        'Legal menu content.<b>9 links, however editors can add up to 3 more (a fourth row) </b>',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'object',
      },
    },
    copyrighttext: {
      name: 'Copyright Text',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'text',
      },
    },
    corporateText: {
      name: 'Corporate Text',
      table: {
        category: 'Footer Components',
      },
      control: {
        type: 'text',
      },
    },
    //Search page header
    search_page_Title: {
      name: 'Title',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      table: { category: 'Search Page Header' },
      control: { type: 'text' },
    },
    search_page_searchResults: {
      name: 'Searched Term Title',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      table: { category: 'Search Page Header' },
      control: { type: 'object' },
    },
    //Search Result 
    search_result_heading:{
      name: 'Title',
      description: 'Title of the search result. <b>Maximum character limit is 55.</b>',
      table: { category: 'Search Result' },
      control: { type: 'text' },
    },
    search_result_applied_filter:{
      name: 'Applied Filters',
      description: 'Applied Filters of the search result.',
      table: { category: 'Search Result' },
      control: { type: 'object' },
    },
    search_result_filters:{
      name: 'All Filters ',
      description: 'All filters of the search result.',
      table: { category: 'Search Result' },
      control: { type: 'object' },
    }
  },
};

export const searchResultPageLayout = ({
  theme,
  headerMenu,
  headerAlertBanner,
  footerMenuItems,
  marketingMessage,
  socialMenuItems,
  legaMenuItems,
  copyrighttext,
  corporateText,

  //Search page header
  search_page_Title,
  search_page_searchResults,

  //Search result
  search_result_heading,
  search_result_applied_filter,
  search_result_filters
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);

  ajaxCardGridData.items = [
    productCard(productCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData),
    recipeCard(recipeCardData),
    articleCard(articleCardData),
    productCard(productCardData)
  ];

  return (
    <div
      dangerouslySetInnerHTML={{
        __html: searchTwig({
          ...footerSocial,
          ...footerMenu,
          ...secondaryMenuData,
          ...inlineSearchData,
          ...mainMenuData,
          ...legalLinksData,
          ...siteHeaderData,
          ...siteFooterData,
          ...searchData,

          ...SearchPageHeaderData,
          ...searchFilterData,
          ...ajaxCardGridData,
          ...searchResultData,

          theme_styles: theme,
  
          menu_items: headerMenu,
          alert_banner: headerAlertBanner,

          footer_menu_items: footerMenuItems,
          marketing_text: marketingMessage,
          social_menu_items: socialMenuItems,
          legal_links_menu_items: legaMenuItems,
          copyright_text: copyrighttext,
          corporate_tout_text: corporateText,

          //Search page Header
          search_page_header_heading:search_page_Title,
          search_results:search_page_searchResults,

          //Search results
          ajax_card_grid_heading: search_result_heading,
          applied_filters_list:search_result_applied_filter,
          filters:search_result_filters
        }),
      }}
    />
  );
};
searchResultPageLayout.args = {
  theme: searchData.theme_styles,
  //For Header
  headerMenu: mainMenuData.menu_items,
  headerAlertBanner: siteHeaderData.alert_banner,
  //For Footer
  footerMenuItems: footerMenu.footer_menu_items,
  marketingMessage: siteFooterData.marketing_text,
  socialMenuItems: footerSocial.social_menu_items,
  legaMenuItems: legalLinksData.legal_links_menu_items,
  copyrighttext: siteFooterData.copyright_text,
  corporateText: siteFooterData.corporate_tout_text,
  //For search page header
  search_page_Title:SearchPageHeaderData.search_page_header_heading,
  search_page_searchResults:SearchPageHeaderData.search_results,
  //Search Result
  search_result_heading:ajaxCardGridData.ajax_card_grid_heading,
  search_result_applied_filter:searchFilterData.applied_filters_list,
  search_result_filters:searchFilterData.filters
}