/**
 * Adds filters to the given Twig intstance.
 *
 * @param {Twig} twigInstance The instance of Twig to modify.
 */
module.exports = function (twigInstance) {
  twigInstance.extendFilter('mt', function (value) {
      return value
    }
  )
  twigInstance.extendFilter('resizeByHeight', function (value) {
      return value
    }
  )
  twigInstance.extendFilter('resizeByWidth', function (value) {
      return value
    }
  )
  twigInstance.extendFilter('resize', function (value) {
      return value
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
