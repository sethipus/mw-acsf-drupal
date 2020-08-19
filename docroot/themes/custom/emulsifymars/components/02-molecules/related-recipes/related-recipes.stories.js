import React from 'react';

import relatedRecipes from './related-recipes.twig';
import relatedRecipesData from './related-recipes.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Related Recipes' };

export const relatedRecipesBlock = () => {
    return <div dangerouslySetInnerHTML={{ __html: relatedRecipes(relatedRecipesData) }} />;
};
