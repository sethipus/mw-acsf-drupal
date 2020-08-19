import React from 'react';

import recipeBodyTwig from './recipe-body.twig';
import recipeBodyData from './recipe-body.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Recipe Body' };

export const recipeBodyTemplate = () => (
  <div dangerouslySetInnerHTML={{ __html: recipeBodyTwig({
      ...recipeBodyData,
    }) }} />
);


