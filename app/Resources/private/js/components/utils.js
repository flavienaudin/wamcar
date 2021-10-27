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

/**
 * Retourne "true" si les deux dates correspondent au même jour
 * @param d1 Date
 * @param d2 Date
 * @returns {boolean}
 */
function sameDay(d1, d2){
  return d1.getFullYear() === d2.getFullYear() &&
  d1.getMonth() === d2.getMonth() &&
  d1.getDate() === d2.getDate();
}

/**
 * Générate th hash of the string str
 * @param str
 * @returns {number}
 */
function hashCode(str){
  return str.split('').reduce((a,b)=>{a=((a<<5)-a)+b.charCodeAt(0);return a&a;},0);
}

export {debounce, sameDay, hashCode};
