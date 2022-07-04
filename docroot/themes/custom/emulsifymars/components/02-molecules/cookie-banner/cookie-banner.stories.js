import React from 'react';
import cookieBanner from './cookie-banner.twig';

export default { title: 'Molecules/Cookie Banner' };

export const cookieBannerLayer = () => (
  <div dangerouslySetInnerHTML={{ __html: cookieBanner }} />
);