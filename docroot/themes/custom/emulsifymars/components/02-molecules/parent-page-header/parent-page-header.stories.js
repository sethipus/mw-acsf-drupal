import React from 'react';
import parentPageHeader from './parent-page-header.twig';
import parentPageHeaderData from './parent-page-header.yml';

export default { title: 'Molecules/Parent page header' };

export const parentPageHeaderWithDefaultBackground = () => (
  <div dangerouslySetInnerHTML={{ __html: parentPageHeader(parentPageHeaderData) }} />
);

export const parentPageHeaderWithBackgroundImage = () => (
  <div dangerouslySetInnerHTML={{ __html: parentPageHeader(Object.assign({}, parentPageHeaderData, {parent_page_media_url: '/content-feature-bg.png', parent_page_media_type: 'image'})) }} />
);

export const parentPageHeaderWithVideo = () => (
  <div dangerouslySetInnerHTML={{ __html: parentPageHeader(Object.assign({}, parentPageHeaderData, {parent_page_media_url: '/content-feature-bg.png', parent_page_media_type: 'video'})) }} />
);