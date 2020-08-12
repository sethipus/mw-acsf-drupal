import React from 'react';

import homepageHero from './3up/homepage-hero.twig';
import homepageHeroData from './3up/homepage-hero.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/3UP/Homepage hero 3UP' };

export const homepageHeroBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: homepageHero(homepageHeroData) }} />;
};
