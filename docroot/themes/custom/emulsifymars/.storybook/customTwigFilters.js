/**
 * Adds filters to the given Twig intstance.
 *
 * @param {Twig} twigInstance The instance of Twig to modify.
 */
module.exports = function (twigInstance) {
  const resizeTargets = {
    'square': '/adaptivemedia/rendition/id_b7426c1bfd973d87d4423ae19beae46364725b73/name_17_TICKLE+KITTEN.png/17_TICKLE+KITTEN.png',
    'portrait': '/adaptivemedia/rendition/id_4b8b5d8f4493bfb109ea42761e9ac789188bea4c/name_10132858-iams-dry-cat-04.eps/10132858-iams-dry-cat-04.eps',
    'landscape': '/adaptivemedia/rendition/id_414d8458f02fb9624cfd18589f048b99ab221e5a/name_Crawling+Cat.png/Crawling+Cat.png.jpeg',
  };

  twigInstance.extendFilter('mt', function (value) {
      return value
    }
  )

  twigInstance.extendFilter('resize', function (value, args) {
      if (global.sb_mars.resize_mode === 'none') {
        return value;
      }
      const image = resizeTargets[global.sb_mars.resize_mode];
      const width = args[0];
      const height = args[1];
      return `https://lhcdn.mars.com/cdn-cgi/image/width=${width},height=${height}${image}`;
    }
  )

  twigInstance.extendFilter('resizeByWidth', function (value, args) {
      if (global.sb_mars.resize_mode === 'none') {
        return value;
      }
      const image = resizeTargets[global.sb_mars.resize_mode];
      const width = args[0];
      return `https://lhcdn.mars.com/cdn-cgi/image/width=${width}${image}`;
    }
  )

  twigInstance.extendFilter('resizeByHeight', function (value, args) {
      if (global.sb_mars.resize_mode === 'none') {
        return value;
      }
      const image = resizeTargets[global.sb_mars.resize_mode];
      const height = args[0];
      return `https://lhcdn.mars.com/cdn-cgi/image/height=${height}${image}`;
    }
  )

  twigInstance.extendFunction('create_attribute', function (value) {
      if (typeof (value) === "undefined" || value === null) {
        return {};
      }
      return value
    }
  )
}
