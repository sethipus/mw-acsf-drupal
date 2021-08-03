import React from 'react';

import flexibleFramer from './flexible-framer.twig';
import flexibleFramerData from './flexible-framer.yml';
import relatedRecipesData from './flexible-framer-recipes.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Flexible Framer' };

export const flexibleFramerComponent = () => {
  return <div dangerouslySetInnerHTML={{ __html: "<div style='height: 300px; background-color: grey'></div>" + flexibleFramer(flexibleFramerData) }} />;
};

export const relatedRecipesComponent = () => {
  return <div dangerouslySetInnerHTML={{ __html: flexibleFramer(relatedRecipesData) }} />;
};
