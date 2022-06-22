import React from 'react';

import recipeTemplateTwig from './recipe-template.twig';
import recipeTemplateData from './recipe-template.yml';

import recipeHeroModule from '../../02-molecules/recipe-hero-module/recipe-hero-module.twig';
import recipeHeroModuleData from '../../02-molecules/recipe-hero-module/recipe-hero-module-video.yml';
import recipeBody from '../../03-organisms/recipe-body/recipe-body.twig';
import recipeBodyData from '../../03-organisms/recipe-body/recipe-body.yml';
import recipeArticleList from '../../02-molecules/article-media/list/article-list.twig';
import recipeArticleListData from '../../02-molecules/article-media/list/article-list.yml';
import recipeArticleWysiwyg from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.twig';
import recipeArticleWysiwygData from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.yml';
import recipeMedia from '../../02-molecules/article-media/full-width/full-width-media.twig';
import recipeMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import recipeInlineMedia from '../../02-molecules/article-media/inline/inline-media.twig';
import recipeInlineMediaData from '../../02-molecules/article-media/inline/inline-media.yml';
import recipeSocial from '../../02-molecules/menus/social/social-menu.twig';
import recipeSocialData from '../../02-molecules/menus/social/social-menu.yml';
import recipeFlexibleFramer from '../../02-molecules/flexible-framer/flexible-framer.twig';
import recipeFlexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import recipeFlexibleDriver from '../../02-molecules/flexible-driver/flexible-driver.twig';
import recipeFlexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import recipeMediaCarousel from '../../02-molecules/media-carousel/media-carousel.twig';
import recipeMediaCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';
import recipeContact from '../../02-molecules/contact-module/contact-module.twig';
import recipeContactData from '../../02-molecules/contact-module/contact-module.yml';
import recipeStoryHighlight from '../../02-molecules/story-highlight/story_highlight.twig';
import recipeStoryHighlightData from '../../02-molecules/story-highlight/story_highlight.yml';
import recipeiFrame from '../../01-atoms/iframe/iframe.twig';
import recipeiFrameData from '../../01-atoms/iframe/iframe.yml';
import recipeSocialFeed from '../../02-molecules/social-feed/social-feed.twig';
import recipeSocialFeedData from '../../02-molecules/social-feed/social-feed.yml';
import recipePolls from '../../02-molecules/polls/poll-vote.twig';
import recipePollsData from '../../02-molecules/polls/poll.yml';
import recipeRecommendation from '../../02-molecules/recommendations-module/recommendations-module.twig';
import recipeRecommendationData from '../../02-molecules/recommendations-module/recommendations-module.yml';


import { useEffect } from '@storybook/client-api';
//import '../../03-organisms/recipe-body/recipe-body';

/**
 * Storybook Definition.
 */
// export default { title: 'Templates/Recipe Template' };

export const recipeTemplate = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  const components = [
    recipeHeroModule(recipeHeroModuleData),
    recipeBody(recipeBodyData),
    recipeArticleList(recipeArticleListData),
    recipeArticleWysiwyg(recipeArticleWysiwygData),
    recipeMedia(recipeMediaData),
    recipeInlineMedia(recipeInlineMediaData),
    recipeSocial(recipeSocialData),
    recipeFlexibleFramer(recipeFlexibleFramerData),
    recipeFlexibleDriver(recipeFlexibleDriverData),
    recipeMediaCarousel(recipeMediaCarouselData),
    recipeContact(recipeContactData),
    recipeStoryHighlight(recipeStoryHighlightData),
    recipeiFrame(recipeiFrameData),
    recipeSocialFeed(recipeSocialFeedData),
    recipePolls(recipePollsData),
    recipeRecommendation(recipeRecommendationData),
  ];
  return <div dangerouslySetInnerHTML={{
    __html: recipeTemplateTwig({
      'components': components
    })
  }}/>;
};
