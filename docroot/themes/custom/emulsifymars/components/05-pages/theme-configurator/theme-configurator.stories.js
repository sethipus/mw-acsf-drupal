import themecongiftwig from './theme-configurator.twig';

export default {
  title: 'Components/[GE 01] Theme Configurator',
  parameters: {
    componentSubtitle: `The Theme Configurator is a collection of design 
        attributes that can be customized to create visual differences for 
        each brand that comes into the new system. Once set, this theme is 
        applied to all market instances for that brand, eg. Twix global theme
        is set > US, UK and DE sites all take on that theme. The Theme Configurator
        will be set to default to a baseline Mars Theme as a fall back if a brand 
        doesn't set an item within the configurator `,
  },
};

export const theme = () => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: themecongiftwig(),
      }}
    />
  );
};
