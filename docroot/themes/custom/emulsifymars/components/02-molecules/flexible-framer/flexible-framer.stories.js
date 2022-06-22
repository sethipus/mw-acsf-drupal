import React from 'react';

import flexibleFramer from './flexible-framer.twig';
import flexibleFramerData from './flexible-framer.yml';
import relatedRecipesData from './flexible-framer-recipes.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 13] Flexible Framer',
  parameters: {
    componentSubtitle: `Flexible component that allows for detailed
       storytelling through a variety of layouts,
       supporting text, images, and linking on or
       off-site. It can be displayed in the following
       pages - Homepage, Landing page, About page,
       Product hub, Content hub, Product detail,
       Recipe detail, article and Campaign page.`,
  },
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme for the card',
      defaultValue: {
        summary: 'Twix',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    Title: {
      description:
        'Change the title of the content.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Lorem' },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    items: {
      name: 'Stories',
      description:
        'Change the stories of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b> Maximum character for the Item title and Item description and button CTA is 60, 255 and 15 respectively.</b>',
      table: { category: 'Stories' },
      control: { type: 'object' },
    },
  },
};

export const flexibleFramerComponent = ({
  theme,
  Title,
  items,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html:
          "<div style='height: 200px; background-color: grey'></div>" +
          flexibleFramer({
            ...flexibleFramerData,
            theme_styles: theme,
            grid_label: Title,
            flexible_framer_items: items,
          }),
      }}
    />
  );
};
flexibleFramerComponent.args = {
  theme: flexibleFramerData.theme_styles, 
  Title: flexibleFramerData.grid_label,
  items: flexibleFramerData.flexible_framer_items,
};