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
  twigInstance.extendFunction('create_attribute', function (value) {
      if (typeof (value) === "undefined" || value === null) {
        return {};
      }
      return value
    }
  )
  twigInstance.extendFilter('bgColorClassMap', (value, args) => value )
}
