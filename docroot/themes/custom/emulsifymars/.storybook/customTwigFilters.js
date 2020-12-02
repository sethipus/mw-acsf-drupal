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
}
