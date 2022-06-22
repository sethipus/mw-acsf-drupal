import React from 'react';
import { useEffect } from "@storybook/client-api";

import recipeEmailForm from './recipe-email-form-layout/recipe-email-form-layout.twig';
import recipeEmailFormData from './recipe-email-form-layout/recipe-email-form-layout.yml';

import './recipe-email-form-layout/recipe-email-form-layout.js';

// export default { title: 'Molecules/Recipe Email Form' };

export const recipeEmailFormExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{__html: recipeEmailForm(recipeEmailFormData)}}
              style={{backgroundColor: '#ccc'}}/>
};