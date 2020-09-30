/* ===========================================================================
   UTILS
   =========================================================================== */

/**
 * Fonction permettant de limiter le nombre d'appels à une fonction dans un délai donnée "debounce"
 * @param func La fonction à appeler
 * @param delay Le délai avant que la fonction soit appelée, tant qu'il n'y a pas eu d'autres appels
 * @returns {Function}
 */
function debounce(func, delay) {
  let inDebounce;
  return function () {
    const context = this;
    const args = arguments;
    clearTimeout(inDebounce); // On reset l'appel précédent s'il y a eu appel précédent
    inDebounce = setTimeout(() => func.apply(context, args), delay);
  };
}

export {debounce};