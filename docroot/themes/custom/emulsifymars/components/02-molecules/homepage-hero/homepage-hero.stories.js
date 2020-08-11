import React from 'react';

import homepageHero from './homepage-hero.twig';
import homepageHeroData from './homepage-hero.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Homepage hero' };

export const homepageHeroBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: homepageHero(homepageHeroData) }} />;
};
