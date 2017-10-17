/* ===========================================================================
   Get index
   =========================================================================== */

export default (node) => {
  var childs = node.parentNode.childNodes;
  for (i = 0; i < childs.length; i++) {
    if (node == childs[i]) break;
  }
  return i;
};
