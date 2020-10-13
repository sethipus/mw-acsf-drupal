import React from 'react';

import searchTwig from './search-page.twig';
import searchData from './search-page.yml';

import allResultsData from '../../03-organisms/search-results/search-results-all-results/all-results.yml';
import ajaxCardGridData from '../..//03-organisms/grid/ajax-card-grid.yml';
import searchResultsData from '../../02-molecules/search/search-results/search-results.yml';
import productCard from './../../02-molecules/card/product-card/product-card.twig';
import productCardData from './../../02-molecules/card/product-card/product-card.yml';
import recipeCard from './../../02-molecules/card/recipe-card/recipe-card.twig';
import recipeCardData from './../../02-molecules/card/recipe-card/recipe-card.yml';
import articleCard from './../../02-molecules/card/article-card/article-card.twig';
import articleCardData from './../../02-molecules/card/article-card/article-card.yml';

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

import { useEffect } from '@storybook/client-api';

export default { title: 'Pages/Search'};

export const search = () => {
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
    return <div dangerouslySetInnerHTML={{
        __html: searchTwig({
            ...allResultsData,
            ...ajaxCardGridData,
            ...productCardData,
            ...recipeCardData,
            ...articleCardData,
            ...footerSocial,
            ...footerMenu,
            ...secondaryMenuData,
            ...inlineSearchData,
            ...mainMenuData,
            ...legalLinksData,
            ...siteHeaderData,
            ...siteFooterData,
            ...searchResultsData,
            ...searchData
        })
      }}/>
}
