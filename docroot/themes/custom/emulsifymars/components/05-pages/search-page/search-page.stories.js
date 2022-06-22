import React from 'react';
import ReactDOMServer from "react-dom/server";

import {useEffect} from '@storybook/client-api';
import {header, footer} from "../../03-organisms/site/site.stories.js";
import {searchResultsModule} from "../../03-organisms/search/search-results/search-results.stories";
import {searchPageHeaderModule} from "../../02-molecules/search-page-header/search-page-header.stories";
import searchTwig from './search-page.twig'

// export default {title: 'Pages/Search'};

export const search = () => {
  useEffect(() => Drupal.attachBehaviors(), []);

  const components = [
    ReactDOMServer.renderToString(header()),
    ReactDOMServer.renderToString(searchPageHeaderModule()),
    ReactDOMServer.renderToString(searchResultsModule()),
    ReactDOMServer.renderToString(footer())
  ];

  return <div dangerouslySetInnerHTML={{
    __html: searchTwig({
      components: components,
    })
  }}/>
}
