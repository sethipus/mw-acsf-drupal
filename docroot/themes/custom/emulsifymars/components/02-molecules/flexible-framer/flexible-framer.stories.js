import React from 'react';
import {useEffect} from "@storybook/client-api";

import flexibleFramer from './flexible-framer.twig';
import flexibleFramerData from './flexible-framer.yml';
import relatedRecipesData from './flexible-framer-recipes.yml';

import './flexible-framer';

/**
 * Storybook Definition.
 */
export default { title: 'Molecules/Flexible Framer' };

export const flexibleFramerComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: "<div style='height: 300px; background-color: grey'></div>" + flexibleFramer(flexibleFramerData) }} />;
};

export const relatedRecipesComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: flexibleFramer(relatedRecipesData) }} />;
};
